isStatusValid(targetStatus) {
    if (this.selected.length === 0) return false;

    // Get statuses of selected orders from DOM (scoped for safety)
    const selectedStatuses = this.selected.map(id => {
        const checkbox = document.querySelector(
            `#orders-content input[type='checkbox'][value='${id}']`
        );
        return checkbox ? checkbox.getAttribute('data-status') : null;
    }).filter(s => s !== null);

    if (selectedStatuses.length === 0) return false;

    // ✅ Normalize statuses (handle legacy "completed")
    const normalizedStatuses = selectedStatuses.map(s =>
        s === 'completed' ? 'delivered' : s
    );

    // ✅ Prevent mixed status selection (CRITICAL FIX)
    const uniqueStatuses = [...new Set(normalizedStatuses)];
    if (uniqueStatuses.length > 1) return false;

    // Cancel logic
    if (targetStatus === 'cancelled') {
        return normalizedStatuses.every(current =>
            current !== 'delivered' && current !== 'cancelled'
        );
    }

    // ✅ Bulk Delivery Rule (ONLY shipped → delivered)
    if (targetStatus === 'delivered') {
        return normalizedStatuses.every(current => current === 'shipped');
    }

    const targetIndex = this.statusFlow.indexOf(targetStatus);
    if (targetIndex === -1) return false;

    // ✅ STRICT forward transition (no skipping)
    return normalizedStatuses.every(current => {
        const currentIndex = this.statusFlow.indexOf(current);

        if (currentIndex === -1) return false;

        return targetIndex === currentIndex + 1;
    });
}