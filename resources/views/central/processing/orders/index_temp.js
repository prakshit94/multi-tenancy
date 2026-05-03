isStatusValid(targetStatus) {
    if (this.selected.length === 0) return false;

    // Get statuses of selected orders from DOM
    const selectedStatuses = this.selected.map(id => {
        const checkbox = document.querySelector(`input[type='checkbox'][value='${id}']`);
        return checkbox ? checkbox.getAttribute('data-status') : null;
    }).filter(s => s !== null);

    if (selectedStatuses.length === 0) return false;

    // Cancel logic
    if (targetStatus === 'cancelled') {
        return selectedStatuses.every(current =>
            current !== 'delivered' && current !== 'cancelled'
        );
    }

    // ✅ Bulk Delivery Rule (ONLY shipped → delivered)
    if (targetStatus === 'delivered') {
        return selectedStatuses.every(current => current === 'shipped');
    }

    const targetIndex = this.statusFlow.indexOf(targetStatus);
    if (targetIndex === -1) return false;

    // Forward transition validation (ALL must be valid)
    return selectedStatuses.every(current => {
        let normalizedCurrent = current === 'completed' ? 'delivered' : current;

        const currentIndex = this.statusFlow.indexOf(normalizedCurrent);

        if (currentIndex === -1) return false;

        return targetIndex > currentIndex;
    });
}