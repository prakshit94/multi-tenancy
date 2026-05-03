{{-- ============================================================
     Navigation & Filter Hub — Premium UI
     All logic preserved, only styling upgraded
     ============================================================ --}}

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand: #6c47ff;
    --brand-light: #ede9ff;
    --brand-mid: #8b6bff;
    --surface: #ffffff;
    --surface-2: #f8f7ff;
    --surface-3: #f2f0ff;
    --border: rgba(108,71,255,0.12);
    --border-strong: rgba(108,71,255,0.25);
    --text-1: #0f0d1a;
    --text-2: #4a4662;
    --text-3: #8b88a8;
    --radius-sm: 10px;
    --radius-md: 14px;
    --radius-lg: 20px;
    --radius-xl: 28px;
    --radius-pill: 999px;
    --shadow-sm: 0 1px 3px rgba(15,13,26,0.06), 0 1px 2px rgba(15,13,26,0.04);
    --shadow-md: 0 4px 16px rgba(108,71,255,0.08), 0 1px 4px rgba(15,13,26,0.06);
    --shadow-lg: 0 12px 40px rgba(108,71,255,0.14), 0 4px 12px rgba(15,13,26,0.06);
    --font: 'DM Sans', system-ui, sans-serif;
    --mono: 'DM Mono', monospace;
}

.prem-wrap { font-family: var(--font); color: var(--text-1); }

/* ── Status Tabs ─────────────────────────────────────────── */
.tab-rail {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}

.tab-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px 8px 12px;
    border-radius: var(--radius-md);
    border: 1.5px solid transparent;
    background: var(--surface);
    border-color: var(--border);
    color: var(--text-2);
    font-family: var(--font);
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.18s ease;
    box-shadow: var(--shadow-sm);
    white-space: nowrap;
}

.tab-btn:hover {
    border-color: var(--brand-mid);
    color: var(--brand);
    background: var(--brand-light);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.tab-btn.tab-active {
    background: var(--brand);
    border-color: var(--brand);
    color: #fff;
    box-shadow: 0 4px 16px rgba(108,71,255,0.35), 0 1px 4px rgba(108,71,255,0.2);
    transform: translateY(-1px);
}

.tab-icon {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    opacity: 0.85;
}

.tab-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
    margin-left: 2px;
}

.tab-count {
    padding: 1px 6px;
    border-radius: 6px;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0;
    line-height: 1.4;
    background: rgba(255,255,255,0.2);
    color: inherit;
    transition: background 0.18s;
}

.tab-btn:not(.tab-active) .tab-count {
    background: var(--surface-3);
    color: var(--text-2);
}

.tab-amount {
    font-size: 8.5px;
    font-weight: 600;
    opacity: 0.75;
    letter-spacing: 0;
    font-family: var(--mono);
}

/* ── Filter Controls ─────────────────────────────────────── */
.filter-hub {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 28px;
}

.filter-panel {
    display: flex;
    flex-col: column;
    gap: 12px;
    padding: 16px 20px;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-sm);
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.filter-row-inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

/* Dropdown filters */
.prem-dropdown { position: relative; min-width: 155px; }

.prem-dropdown-btn {
    width: 100%;
    height: 40px;
    padding: 0 14px;
    background: var(--surface-2);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    font-family: var(--font);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--text-2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}

.prem-dropdown-btn:hover {
    border-color: var(--brand-mid);
    color: var(--brand);
    background: var(--brand-light);
}

.prem-dropdown-panel {
    position: absolute;
    z-index: 110;
    left: 0;
    right: 0;
    top: calc(100% + 6px);
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    max-height: 280px;
    display: flex;
    flex-direction: column;
}

.prem-dropdown-search {
    width: 100%;
    padding: 9px 14px;
    font-family: var(--font);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: none;
    border-bottom: 1.5px solid var(--border);
    background: var(--surface-2);
    color: var(--text-1);
    outline: none;
}

.prem-dropdown-list {
    overflow-y: auto;
    flex: 1;
    padding: 4px;
}

.prem-dropdown-item {
    width: 100%;
    padding: 8px 12px;
    font-family: var(--font);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-2);
    background: none;
    border: none;
    border-radius: var(--radius-sm);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.12s;
    text-align: left;
}

.prem-dropdown-item:hover { background: var(--brand-light); color: var(--brand); }
.prem-dropdown-item.selected { background: var(--brand-light); color: var(--brand); }

.prem-check {
    width: 14px;
    height: 14px;
    border-radius: 4px;
    border: 1.5px solid var(--border-strong);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--surface);
    flex-shrink: 0;
    transition: all 0.12s;
}

.prem-check.checked {
    background: var(--brand);
    border-color: var(--brand);
    color: white;
}

/* Tracking input */
.prem-tracking {
    position: relative;
    min-width: 200px;
}

.prem-tracking-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-3);
    width: 15px;
    height: 15px;
    pointer-events: none;
}

.prem-tracking-input {
    width: 100%;
    height: 40px;
    padding: 0 14px 0 38px;
    background: var(--surface-2);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    font-family: var(--mono);
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-1);
    outline: none;
    transition: all 0.15s;
}

.prem-tracking-input:focus {
    border-color: var(--brand);
    background: var(--brand-light);
    box-shadow: 0 0 0 3px rgba(108,71,255,0.12);
}

.prem-tracking-input::placeholder { color: var(--text-3); }

/* ── Stats Grid ───────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}

@media (min-width: 768px) { .stats-grid { grid-template-columns: repeat(5, 1fr); } }

.stat-card {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: all 0.22s ease;
    cursor: default;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--card-accent, var(--brand));
    border-radius: 3px 3px 0 0;
    opacity: 0;
    transition: opacity 0.22s;
}

.stat-card:hover {
    border-color: var(--border-strong);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.stat-card:hover::before { opacity: 1; }

.stat-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    background: var(--icon-bg, var(--brand-light));
    color: var(--icon-color, var(--brand));
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    transition: transform 0.22s;
}

.stat-card:hover .stat-icon-wrap { transform: scale(1.08); }

.stat-label {
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-3);
    margin-bottom: 6px;
    transition: color 0.22s;
}

.stat-card:hover .stat-label { color: var(--icon-color, var(--brand)); }

.stat-count {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-1);
    line-height: 1;
    letter-spacing: -0.03em;
    margin-bottom: 3px;
    font-family: var(--font);
}

.stat-amount {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-3);
    font-family: var(--mono);
}

.stat-bar-track {
    margin-top: 14px;
    height: 3px;
    background: var(--surface-3);
    border-radius: var(--radius-pill);
    overflow: hidden;
}

.stat-bar-fill {
    height: 100%;
    background: var(--card-accent, var(--brand));
    border-radius: var(--radius-pill);
    transition: width 1s ease;
    opacity: 0.55;
}

/* ── District Insights ───────────────────────────────────── */
.district-section { margin-top: 24px; }

.district-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2px;
    margin-bottom: 12px;
}

.district-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.district-icon-wrap {
    width: 30px;
    height: 30px;
    border-radius: var(--radius-sm);
    background: var(--brand-light);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
}

.district-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-3);
}

.district-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: var(--radius-pill);
    background: var(--brand-light);
    color: var(--brand);
    font-family: var(--font);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border: 1.5px solid var(--border);
    cursor: pointer;
    transition: all 0.18s;
}

.district-toggle-btn:hover {
    background: var(--brand);
    color: #fff;
    border-color: var(--brand);
}

.district-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    padding-bottom: 8px;
}

@media (min-width: 640px) { .district-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 768px) { .district-grid { grid-template-columns: repeat(6, 1fr); } }
@media (min-width: 1280px) { .district-grid { grid-template-columns: repeat(8, 1fr); } }

.district-card {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 14px;
    border-radius: var(--radius-lg);
    border: 1.5px solid var(--border);
    background: var(--surface);
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.district-card:hover {
    border-color: var(--brand-mid);
    background: var(--brand-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.district-card.active {
    background: var(--brand);
    border-color: var(--brand);
    box-shadow: 0 6px 20px rgba(108,71,255,0.3);
    transform: translateY(-2px);
}

.district-card-name {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-3);
    truncate: true;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: color 0.2s;
}

.district-card:hover .district-card-name { color: var(--brand); }
.district-card.active .district-card-name { color: rgba(255,255,255,0.65); }

.district-card-count {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: var(--text-1);
    line-height: 1;
    transition: color 0.2s;
}

.district-card:hover .district-card-count { color: var(--brand); }
.district-card.active .district-card-count { color: #fff; }

/* ── Toolbar ─────────────────────────────────────────────── */
.toolbar {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 0;
}

@media (min-width: 640px) {
    .toolbar { flex-direction: row; align-items: center; justify-content: space-between; }
}

.toolbar-left { display: flex; align-items: center; gap: 10px; }

.prem-checkbox {
    width: 18px;
    height: 18px;
    border-radius: 6px;
    border: 1.5px solid var(--border-strong);
    background: var(--surface);
    cursor: pointer;
    accent-color: var(--brand);
    transition: all 0.12s;
}

.selected-pill {
    padding: 5px 12px;
    border-radius: var(--radius-pill);
    background: var(--brand-light);
    border: 1.5px solid var(--border);
    color: var(--brand);
    font-size: 11px;
    font-weight: 700;
}

/* Bulk dropdown */
.bulk-dropdown { position: relative; }

.bulk-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: var(--radius-md);
    background: var(--surface);
    border: 1.5px solid var(--border);
    font-family: var(--font);
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-1);
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    transition: all 0.15s;
}

.bulk-btn:hover { border-color: var(--brand-mid); box-shadow: var(--shadow-md); }

.bulk-panel {
    position: absolute;
    left: 0;
    top: calc(100% + 8px);
    z-index: 50;
    min-width: 240px;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    padding: 6px;
    overflow: hidden;
}

.bulk-section-label {
    padding: 6px 10px 4px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-3);
}

.bulk-item {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: var(--radius-sm);
    font-family: var(--font);
    font-size: 12.5px;
    font-weight: 500;
    color: var(--text-2);
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: all 0.12s;
}

.bulk-item:hover { background: var(--brand-light); color: var(--brand); }

.bulk-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.bulk-divider {
    height: 1px;
    background: var(--border);
    margin: 4px 6px;
}

/* Toolbar right buttons */
.toolbar-right {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

/* Date filter */
.date-dropdown { position: relative; }

.date-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    border-radius: var(--radius-md);
    background: var(--surface);
    border: 1.5px solid var(--border);
    font-family: var(--font);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-2);
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    transition: all 0.15s;
}

.date-btn:hover { border-color: var(--brand-mid); color: var(--brand); }

.date-panel {
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    z-index: 50;
    width: 260px;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    padding: 12px;
}

.date-quick-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-bottom: 10px;
}

.date-quick-btn {
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    font-family: var(--font);
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-2);
    background: var(--surface-2);
    border: 1.5px solid var(--border);
    cursor: pointer;
    text-align: left;
    transition: all 0.12s;
}

.date-quick-btn:hover { background: var(--brand-light); color: var(--brand); border-color: var(--brand-mid); }

.date-range-label {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-3);
    padding: 4px 0;
    margin-bottom: 6px;
}

.date-input {
    width: 100%;
    height: 36px;
    padding: 0 10px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: var(--font);
    font-size: 12px;
    color: var(--text-1);
    background: var(--surface-2);
    margin-bottom: 6px;
    outline: none;
    transition: border-color 0.15s;
}

.date-input:focus { border-color: var(--brand); }

.date-apply-btn {
    width: 100%;
    padding: 9px;
    border-radius: var(--radius-sm);
    background: var(--brand);
    color: #fff;
    font-family: var(--font);
    font-size: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
}

.date-apply-btn:hover { background: var(--brand-mid); }

/* Action buttons */
.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 8px 16px;
    border-radius: var(--radius-md);
    font-family: var(--font);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all 0.15s;
}

.action-export {
    background: #fff7ed;
    color: #c05300;
    border-color: #fed7aa;
}
.action-export:hover { background: #ffedd5; box-shadow: 0 4px 12px rgba(192,83,0,0.15); }

.action-import {
    background: #f0fdf4;
    color: #166534;
    border-color: #bbf7d0;
}
.action-import:hover { background: #dcfce7; box-shadow: 0 4px 12px rgba(22,101,52,0.12); }

/* ── Orders Container ─────────────────────────────────────── */
.orders-container { margin-top: 16px; }
</style>

<div class="prem-wrap">

{{-- ════════════════════════════════════════════════════════
     Navigation & Filter Hub
     ════════════════════════════════════════════════════════ --}}
<div class="filter-hub">
    <div class="filter-panel">

        {{-- Tabs Row --}}
        <div class="filter-row">
            <div class="tab-rail">

                @php
                $tabs = [
                    ['label' => 'Confirmed', 'status' => 'confirmed', 'icon' => '<path d="m9 12 2 2 4-4"/>',
                     'accent' => '#2563eb'],
                    ['label' => 'Processing', 'status' => 'processing',
                     'icon' => '<path d="M12 2 2 7v10l10 5 10-5V7Z"/><path d="m2 7 10 5 10-5"/><path d="M12 22V12"/>',
                     'accent' => '#7c3aed'],
                    ['label' => 'Ready to Ship', 'status' => 'ready_to_ship',
                     'icon' => '<path d="M21 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v3m18 0v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8m18 0-9 6-9-6"/>',
                     'accent' => '#059669'],
                    ['label' => 'Shipped', 'status' => 'shipped',
                     'icon' => '<path d="M10 17h4V5H2v12h3m0 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0m10 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0M13 5h9l-1 7h-8z"/>',
                     'accent' => '#4f46e5'],
                    ['label' => 'Delivered', 'status' => 'delivered',
                     'icon' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-11.7 8.38 8.38 0 0 1 3.8.9L21 2z"/><path d="M9 11l3 3L22 4"/>',
                     'accent' => '#16a34a'],
                    ['label' => 'Cancelled', 'status' => 'cancelled',
                     'icon' => '<path d="M18 6 6 18M6 6l12 12"/>',
                     'accent' => '#ea580c'],
                ];
                @endphp

                @foreach($tabs as $tab)
                <button
                    @click="activeStatus = '{{ $tab['status'] }}'; performFilter()"
                    type="button"
                    class="tab-btn"
                    :class="activeStatus === '{{ $tab['status'] }}' ? 'tab-active' : ''">

                    <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                        {!! $tab['icon'] !!}
                    </svg>

                    <span>{{ $tab['label'] }}</span>

                    <div class="tab-badge">
                        <span class="tab-count"
                            :class="activeStatus === '{{ $tab['status'] }}' ? '' : ''">
                            {{ $counts[$tab['status']] ?? 0 }}
                        </span>
                        <span class="tab-amount">₹{{ number_format($amounts[$tab['status']] ?? 0, 0) }}</span>
                    </div>
                </button>
                @endforeach

            </div>

            {{-- Filter controls --}}
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <form id="filter-form"
                    @submit.prevent="performFilter()"
                    @per-page-change.window="$el.querySelector('input[name=per_page]').value = $event.detail.value; performFilter()"
                    style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">

                    <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">

                    {{-- Searchable State --}}
                    <div class="prem-dropdown" x-data="{
                        open: window.__open_filter === 'state', search: window.__open_filter === 'state' ? (window.__filter_search || '') : '', options: @js($states ?? []), selected: '{{ request('state') }}' ? '{{ request('state') }}'.split(',') : [],
                        get filteredOptions() { return !this.search ? this.options : this.options.filter(o => o.toLowerCase().includes(this.search.toLowerCase())); },
                        toggle(val) {
                            window.__open_filter = 'state'; window.__filter_search = this.search;
                            if(this.selected.includes(val)) { this.selected = this.selected.filter(i => i !== val); }
                            else { this.selected.push(val); }
                            $nextTick(() => { $refs.stateInput.value = this.selected.join(','); $refs.stateInput.dispatchEvent(new Event('change')); performFilter(); });
                        },
                        toggleAll() {
                            window.__open_filter = 'state'; window.__filter_search = this.search;
                            if (this.selected.length === this.options.length && this.options.length > 0) {
                                this.selected = [];
                            } else {
                                this.selected = [...this.options];
                            }
                            $nextTick(() => { $refs.stateInput.value = this.selected.join(','); $refs.stateInput.dispatchEvent(new Event('change')); performFilter(); });
                        }
                    }" @click.away="open = false; window.__open_filter = null; window.__filter_search = null;">
                        <input type="hidden" name="state" x-ref="stateInput" value="{{ request('state') }}">
                        <button type="button" @click="open = !open" class="prem-dropdown-btn">
                            <span x-text="selected.length > 0 ? selected.length + ' Selected' : 'State'" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                            <svg style="width:14px;height:14px;flex-shrink:0;color:var(--text-3)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" x-transition.origin.top class="prem-dropdown-panel">
                            <input type="text" x-model="search" placeholder="Search state…" class="prem-dropdown-search">
                            <div class="prem-dropdown-list">
                                <button type="button" @click="toggleAll()" class="prem-dropdown-item" style="color:var(--brand);font-weight:800;">
                                    All States
                                    <div class="prem-check" :class="selected.length === options.length && options.length > 0 ? 'checked' : ''">
                                        <svg x-show="selected.length === options.length && options.length > 0" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </button>
                                <template x-for="opt in filteredOptions" :key="opt">
                                    <button type="button" @click="toggle(opt)" class="prem-dropdown-item" :class="selected.includes(opt) ? 'selected' : ''">
                                        <span x-text="opt"></span>
                                        <div class="prem-check" :class="selected.includes(opt) ? 'checked' : ''">
                                            <svg x-show="selected.includes(opt)" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Searchable District --}}
                    <div class="prem-dropdown" x-data="{
                        open: window.__open_filter === 'district', search: window.__open_filter === 'district' ? (window.__filter_search || '') : '', options: @js($districts), selected: '{{ request('district') }}' ? '{{ request('district') }}'.split(',') : [],
                        get filteredOptions() { return !this.search ? this.options : this.options.filter(o => o.toLowerCase().includes(this.search.toLowerCase())); },
                        toggle(val) {
                            window.__open_filter = 'district'; window.__filter_search = this.search;
                            if(this.selected.includes(val)) { this.selected = this.selected.filter(i => i !== val); }
                            else { this.selected.push(val); }
                            $nextTick(() => { $refs.districtInput.value = this.selected.join(','); $refs.districtInput.dispatchEvent(new Event('change')); performFilter(); });
                        },
                        toggleAll() {
                            window.__open_filter = 'district'; window.__filter_search = this.search;
                            if (this.selected.length === this.options.length && this.options.length > 0) {
                                this.selected = [];
                            } else {
                                this.selected = [...this.options];
                            }
                            $nextTick(() => { $refs.districtInput.value = this.selected.join(','); $refs.districtInput.dispatchEvent(new Event('change')); performFilter(); });
                        }
                    }" @click.away="open = false; window.__open_filter = null; window.__filter_search = null;">
                        <input type="hidden" name="district" x-ref="districtInput" value="{{ request('district') }}">
                        <button type="button" @click="open = !open" class="prem-dropdown-btn">
                            <span x-text="selected.length > 0 ? selected.length + ' Selected' : 'District'" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                            <svg style="width:14px;height:14px;flex-shrink:0;color:var(--text-3)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" x-transition.origin.top class="prem-dropdown-panel">
                            <input type="text" x-model="search" placeholder="Search district…" class="prem-dropdown-search">
                            <div class="prem-dropdown-list">
                                <button type="button" @click="toggleAll()" class="prem-dropdown-item" style="color:var(--brand);font-weight:800;">
                                    All Districts
                                    <div class="prem-check" :class="selected.length === options.length && options.length > 0 ? 'checked' : ''">
                                        <svg x-show="selected.length === options.length && options.length > 0" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </button>
                                <template x-for="opt in filteredOptions" :key="opt">
                                    <button type="button" @click="toggle(opt)" class="prem-dropdown-item" :class="selected.includes(opt) ? 'selected' : ''">
                                        <span x-text="opt"></span>
                                        <div class="prem-check" :class="selected.includes(opt) ? 'checked' : ''">
                                            <svg x-show="selected.includes(opt)" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Searchable Taluka --}}
                    <div class="prem-dropdown" x-data="{
                        open: window.__open_filter === 'taluka', search: window.__open_filter === 'taluka' ? (window.__filter_search || '') : '', options: @js($talukas), selected: '{{ request('taluka') }}' ? '{{ request('taluka') }}'.split(',') : [],
                        get filteredOptions() { return !this.search ? this.options : this.options.filter(o => o.toLowerCase().includes(this.search.toLowerCase())); },
                        toggle(val) {
                            window.__open_filter = 'taluka'; window.__filter_search = this.search;
                            if(this.selected.includes(val)) { this.selected = this.selected.filter(i => i !== val); }
                            else { this.selected.push(val); }
                            $nextTick(() => { $refs.talukaInput.value = this.selected.join(','); $refs.talukaInput.dispatchEvent(new Event('change')); performFilter(); });
                        },
                        toggleAll() {
                            window.__open_filter = 'taluka'; window.__filter_search = this.search;
                            if (this.selected.length === this.options.length && this.options.length > 0) {
                                this.selected = [];
                            } else {
                                this.selected = [...this.options];
                            }
                            $nextTick(() => { $refs.talukaInput.value = this.selected.join(','); $refs.talukaInput.dispatchEvent(new Event('change')); performFilter(); });
                        }
                    }" @click.away="open = false; window.__open_filter = null; window.__filter_search = null;">
                        <input type="hidden" name="taluka" x-ref="talukaInput" value="{{ request('taluka') }}">
                        <button type="button" @click="open = !open" class="prem-dropdown-btn">
                            <span x-text="selected.length > 0 ? selected.length + ' Selected' : 'Taluka'" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                            <svg style="width:14px;height:14px;flex-shrink:0;color:var(--text-3)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" x-transition.origin.top class="prem-dropdown-panel">
                            <input type="text" x-model="search" placeholder="Search taluka…" class="prem-dropdown-search">
                            <div class="prem-dropdown-list">
                                <button type="button" @click="toggleAll()" class="prem-dropdown-item" style="color:var(--brand);font-weight:800;">
                                    All Talukas
                                    <div class="prem-check" :class="selected.length === options.length && options.length > 0 ? 'checked' : ''">
                                        <svg x-show="selected.length === options.length && options.length > 0" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </button>
                                <template x-for="opt in filteredOptions" :key="opt">
                                    <button type="button" @click="toggle(opt)" class="prem-dropdown-item" :class="selected.includes(opt) ? 'selected' : ''">
                                        <span x-text="opt"></span>
                                        <div class="prem-check" :class="selected.includes(opt) ? 'checked' : ''">
                                            <svg x-show="selected.includes(opt)" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Courier Filter --}}
                    <div class="prem-dropdown" x-data="{
                        open: window.__open_filter === 'courier', search: window.__open_filter === 'courier' ? (window.__filter_search || '') : '', options: @js($couriers), selected: '{{ request('courier') }}' ? '{{ request('courier') }}'.split(',') : [],
                        get filteredOptions() { return !this.search ? this.options : this.options.filter(o => o.toLowerCase().includes(this.search.toLowerCase())); },
                        toggle(val) {
                            window.__open_filter = 'courier'; window.__filter_search = this.search;
                            if(this.selected.includes(val)) { this.selected = this.selected.filter(i => i !== val); }
                            else { this.selected.push(val); }
                            $nextTick(() => { $refs.courierInput.value = this.selected.join(','); $refs.courierInput.dispatchEvent(new Event('change')); performFilter(); });
                        },
                        toggleAll() {
                            window.__open_filter = 'courier'; window.__filter_search = this.search;
                            if (this.selected.length === this.options.length && this.options.length > 0) {
                                this.selected = [];
                            } else {
                                this.selected = [...this.options];
                            }
                            $nextTick(() => { $refs.courierInput.value = this.selected.join(','); $refs.courierInput.dispatchEvent(new Event('change')); performFilter(); });
                        }
                    }" @click.away="open = false; window.__open_filter = null; window.__filter_search = null;">
                        <input type="hidden" name="courier" x-ref="courierInput" value="{{ request('courier') }}">
                        <button type="button" @click="open = !open" class="prem-dropdown-btn">
                            <span x-text="selected.length > 0 ? selected.length + ' Selected' : 'Courier'" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                            <svg style="width:14px;height:14px;flex-shrink:0;color:var(--text-3)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" x-transition.origin.top class="prem-dropdown-panel">
                            <input type="text" x-model="search" placeholder="Search courier…" class="prem-dropdown-search">
                            <div class="prem-dropdown-list">
                                <button type="button" @click="toggleAll()" class="prem-dropdown-item" style="color:var(--brand);font-weight:800;">
                                    All Couriers
                                    <div class="prem-check" :class="selected.length === options.length && options.length > 0 ? 'checked' : ''">
                                        <svg x-show="selected.length === options.length && options.length > 0" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </button>
                                <template x-for="opt in filteredOptions" :key="opt">
                                    <button type="button" @click="toggle(opt)" class="prem-dropdown-item" :class="selected.includes(opt) ? 'selected' : ''">
                                        <span x-text="opt"></span>
                                        <div class="prem-check" :class="selected.includes(opt) ? 'checked' : ''">
                                            <svg x-show="selected.includes(opt)" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Product Filter --}}
                    <div class="prem-dropdown" x-data="{
                        open: window.__open_filter === 'product', search: window.__open_filter === 'product' ? (window.__filter_search || '') : '', options: @js($products ?? []), selected: '{{ request('product') }}' ? '{{ request('product') }}'.split(',') : [],
                        get filteredOptions() { return !this.search ? this.options : this.options.filter(o => o.toLowerCase().includes(this.search.toLowerCase())); },
                        toggle(val) {
                            window.__open_filter = 'product'; window.__filter_search = this.search;
                            if(this.selected.includes(val)) { this.selected = this.selected.filter(i => i !== val); }
                            else { this.selected.push(val); }
                            $nextTick(() => { $refs.productInput.value = this.selected.join(','); $refs.productInput.dispatchEvent(new Event('change')); performFilter(); });
                        },
                        toggleAll() {
                            window.__open_filter = 'product'; window.__filter_search = this.search;
                            if (this.selected.length === this.options.length && this.options.length > 0) {
                                this.selected = [];
                            } else {
                                this.selected = [...this.options];
                            }
                            $nextTick(() => { $refs.productInput.value = this.selected.join(','); $refs.productInput.dispatchEvent(new Event('change')); performFilter(); });
                        }
                    }" @click.away="open = false; window.__open_filter = null; window.__filter_search = null;">
                        <input type="hidden" name="product" x-ref="productInput" value="{{ request('product') }}">
                        <button type="button" @click="open = !open" class="prem-dropdown-btn">
                            <span x-text="selected.length > 0 ? selected.length + ' Selected' : 'Product'" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                            <svg style="width:14px;height:14px;flex-shrink:0;color:var(--text-3)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" x-transition.origin.top class="prem-dropdown-panel">
                            <input type="text" x-model="search" placeholder="Search product…" class="prem-dropdown-search">
                            <div class="prem-dropdown-list">
                                <button type="button" @click="toggleAll()" class="prem-dropdown-item" style="color:var(--brand);font-weight:800;">
                                    All Products
                                    <div class="prem-check" :class="selected.length === options.length && options.length > 0 ? 'checked' : ''">
                                        <svg x-show="selected.length === options.length && options.length > 0" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </button>
                                <template x-for="opt in filteredOptions" :key="opt">
                                    <button type="button" @click="toggle(opt)" class="prem-dropdown-item" :class="selected.includes(opt) ? 'selected' : ''">
                                        <span x-text="opt"></span>
                                        <div class="prem-check" :class="selected.includes(opt) ? 'checked' : ''">
                                            <svg x-show="selected.includes(opt)" style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Tracking Number --}}
                    <div class="prem-tracking">
                        <svg class="prem-tracking-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <input type="text" name="tracking_number" value="{{ request('tracking_number') }}"
                            @input.debounce.500ms="performFilter()"
                            placeholder="Tracking ID"
                            class="prem-tracking-input">
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     Stats Grid
     ════════════════════════════════════════════════════════ --}}
@php
$stats = [
    ['label' => 'Confirmed',    'status' => 'confirmed',    'count' => $counts['confirmed'] ?? 0,
     'accent' => '#2563eb', 'icon-bg' => '#eff6ff', 'icon-color' => '#2563eb',
     'icon' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>'],
    ['label' => 'Processing',   'status' => 'processing',   'count' => $counts['processing'] ?? 0,
     'accent' => '#7c3aed', 'icon-bg' => '#f5f3ff', 'icon-color' => '#7c3aed',
     'icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'],
    ['label' => 'Ready to Ship','status' => 'ready_to_ship','count' => $counts['ready_to_ship'] ?? 0,
     'accent' => '#059669', 'icon-bg' => '#ecfdf5', 'icon-color' => '#059669',
     'icon' => '<path d="M2 9h20"/><path d="M4 9h2V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v4h2"/><path d="M22 9v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9Z"/><path d="M12 12v3"/><path d="M12 15h3"/>'],
    ['label' => 'Dispatched',   'status' => 'shipped',      'count' => $counts['shipped'] ?? 0,
     'accent' => '#4f46e5', 'icon-bg' => '#eef2ff', 'icon-color' => '#4f46e5',
     'icon' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5l-4-4h-4"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>'],
    ['label' => 'Delivered',    'status' => 'delivered',    'count' => $counts['delivered'] ?? 0,
     'accent' => '#16a34a', 'icon-bg' => '#f0fdf4', 'icon-color' => '#16a34a',
     'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
];
@endphp

<div class="stats-grid">
    @foreach($stats as $s)
    <div class="stat-card"
         style="--card-accent: {{ $s['accent'] }}; --icon-bg: {{ $s['icon-bg'] }}; --icon-color: {{ $s['icon-color'] }};">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
            <div class="stat-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round">
                    {!! $s['icon'] !!}
                </svg>
            </div>
            <span class="stat-label">{{ $s['label'] }}</span>
        </div>
        <div class="stat-count">{{ number_format($s['count']) }}</div>
        <div class="stat-amount">₹{{ number_format($amounts[$s['status']] ?? 0, 0) }}</div>
        <div class="stat-bar-track">
            <div class="stat-bar-fill" style="width: {{ $s['count'] > 0 ? '65%' : '0%' }};"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ════════════════════════════════════════════════════════
     District Insights
     ════════════════════════════════════════════════════════ --}}
@if($districtCounts->count() > 0)
<div class="district-section" x-data="{ showDistricts: false }">
    <div class="district-header">
        <div class="district-header-left">
            <div class="district-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
            <span class="district-label">Regional Performance</span>
        </div>

        <button @click="showDistricts = !showDistricts" class="district-toggle-btn">
            <span x-text="showDistricts ? 'Collapse' : 'Expand All Regions'"></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="3"
                 stroke-linecap="round" stroke-linejoin="round"
                 class="transition-transform duration-300" :class="showDistricts ? 'rotate-180' : ''">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </button>
    </div>

    <div x-show="showDistricts"
         style="display:none;"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 -translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-250"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-3"
         class="district-grid">

        @foreach($districtCounts as $stat)
        @php
        $isActive = request('district') == $stat->district;
        $districtUrl = request()->fullUrlWithQuery(['district' => $stat->district]);
        @endphp
        <a href="{{ $districtUrl }}"
           @click.prevent="loadData('{{ $districtUrl }}')"
           class="district-card {{ $isActive ? 'active' : '' }}">
            <span class="district-card-name">{{ $stat->district ?: 'Global' }}</span>
            <span class="district-card-count">{{ $stat->total }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════
     Toolbar
     ════════════════════════════════════════════════════════ --}}
<div class="toolbar">

    {{-- Left: Bulk Actions --}}
    <div class="toolbar-left">
        <div style="display:flex; align-items:center; padding:6px; border-radius:8px; cursor:pointer;"
             title="Select All on Page">
            <input type="checkbox"
                   x-on:change="$el.checked ? selected = [...new Set([...selected, ...allIds])] : selected = selected.filter(id => !allIds.includes(id))"
                   x-bind:checked="allIds.length > 0 && allIds.every(id => selected.includes(id))"
                   class="prem-checkbox">
        </div>

        <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms
             style="display:flex; align-items:center; gap:10px;">

            <div class="selected-pill">
                <span x-text="selected.length"></span> selected
            </div>

            <div class="bulk-dropdown" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="bulk-btn">
                    <span>Bulk Actions</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-3)">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div x-show="open" style="display:none;" class="bulk-panel">

                    <div class="bulk-section-label">Update Status</div>

                    <form action="{{ route('central.processing.orders.bulk-status') }}" method="POST">
    @csrf

    <template x-for="id in selected" :key="id">
        <input type="hidden" name="ids[]" :value="id">
    </template>

    <!-- Confirmed -->
    <button type="submit" name="status" value="confirmed"
            x-show="isStatusValid('confirmed')"
            @click="if(!confirm('Mark ' + selected.length + ' orders as Confirmed?')) $event.preventDefault()"
            class="bulk-item">
        <span class="bulk-dot" style="background:#2563eb;"></span>
        Mark as Confirmed
    </button>

    <!-- Processing -->
    <button type="submit" name="status" value="processing"
            x-show="isStatusValid('processing')"
            @click="if(!confirm('Mark ' + selected.length + ' orders as Processing?')) $event.preventDefault()"
            class="bulk-item">
        <span class="bulk-dot" style="background:#7c3aed;"></span>
        Mark as Processing
    </button>

    <!-- Dispatched -->
    <button type="submit" name="status" value="shipped"
            x-show="isStatusValid('shipped')"
            @click="if(!confirm('Dispatch ' + selected.length + ' orders?')) $event.preventDefault()"
            class="bulk-item">
        <span class="bulk-dot" style="background:#4f46e5;"></span>
        Mark as Dispatched
    </button>

    <!-- ✅ NEW: Delivered -->
    <button type="submit" name="status" value="delivered"
            x-show="isStatusValid('delivered')"
            @click="if(!confirm('Mark ' + selected.length + ' orders as Delivered?')) $event.preventDefault()"
            class="bulk-item">
        <span class="bulk-dot" style="background:#16a34a;"></span>
        Mark as Delivered
    </button>

</form>

                    <div class="bulk-divider"></div>
                    <div class="bulk-section-label">Printing</div>

                    <form action="{{ route('central.processing.orders.bulk-print') }}" method="POST">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>

                        <button type="submit" name="type" value="invoice"
                                @click="$dispatch('notify', { type: 'success', message: 'Invoices download started' })"
                                class="bulk-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 9V2h12v7"/>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                <path d="M6 14h12v8H6z"/>
                            </svg>
                            Print Invoices
                        </button>

                        <button type="submit" name="type" value="cod"
                                @click="$dispatch('notify', { type: 'success', message: 'COD Receipts download started' })"
                                class="bulk-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="5" rx="2"/>
                                <line x1="2" x2="22" y1="10" y2="10"/>
                            </svg>
                            Print COD Receipts
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Date Filter + Export + Import --}}
    <div class="toolbar-right">

        {{-- Date Filter --}}
        <div class="date-dropdown" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" class="date-btn">
                <svg style="width:15px;height:15px;color:var(--text-3)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-text="new URLSearchParams(window.location.search).get('date_filter')
                    ? new URLSearchParams(window.location.search).get('date_filter').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
                    : 'Date Filter'"></span>
                <svg style="width:12px;height:12px;color:var(--text-3)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" style="display:none;" class="date-panel">
                <form action="{{ url()->current() }}" method="GET" x-data="{
                    submitFilter(e) {
                        e.preventDefault();
                        const formData = new FormData(e.target);
                        if (e.submitter && e.submitter.name) { formData.append(e.submitter.name, e.submitter.value); }
                        const url = new URL(window.location.href);
                        for (const [key, value] of formData.entries()) {
                            if (value) url.searchParams.set(key, value);
                            else url.searchParams.delete(key);
                        }
                        url.searchParams.delete('page');
                        loadData(url.toString());
                        open = false;
                    }
                }" @submit="submitFilter">
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif

                    <div class="date-quick-grid">
                        <button type="submit" name="date_filter" value="today" class="date-quick-btn">Today</button>
                        <button type="submit" name="date_filter" value="yesterday" class="date-quick-btn">Yesterday</button>
                        <button type="submit" name="date_filter" value="this_week" class="date-quick-btn">This Week</button>
                        <button type="submit" name="date_filter" value="this_month" class="date-quick-btn">This Month</button>
                    </div>

                    <div class="bulk-divider"></div>
                    <div class="date-range-label">Custom Range</div>

                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="date-input">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="date-input">
                    <button type="submit" name="date_filter" value="custom" class="date-apply-btn">Apply Range</button>
                </form>
            </div>
        </div>

        {{-- Export --}}
        <form action="{{ route('central.orders.export') }}" method="POST">
            @csrf
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @if(request('date_filter'))
                <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @endif
            @if(request('district')) <input type="hidden" name="district" value="{{ request('district') }}"> @endif
            @if(request('taluka')) <input type="hidden" name="taluka" value="{{ request('taluka') }}"> @endif
            @if(request('village')) <input type="hidden" name="village" value="{{ request('village') }}"> @endif
            <input type="hidden" name="ids" :value="selected.join(',')">
            <button type="submit" class="action-btn action-export">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
            </button>
        </form>

        {{-- Import CSV --}}
        <button @click="$dispatch('open-import-modal')" class="action-btn action-import">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Import CSV
        </button>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     Orders Grid Container
     ════════════════════════════════════════════════════════ --}}
<div id="orders-list-container" class="orders-container">
    @include('central.processing.orders.partials.orders-list')
</div>

</div>
{{-- end .prem-wrap --}}