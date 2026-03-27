@php
    $stats = [
        ['label' => 'Awaiting Confirmation', 'count' => $counts['confirmed'] ?? 0, 'color' => 'blue', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>'],
        ['label' => 'In Packing', 'count' => $counts['processing'] ?? 0, 'color' => 'purple', 'icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'],
        ['label' => 'Ready to Ship', 'count' => $counts['ready_to_ship'] ?? 0, 'color' => 'emerald', 'icon' => '<path d="M2 9h20"/><path d="M4 9h2V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v4h2"/><path d="M22 9v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9Z"/><path d="M12 12v3"/><path d="M12 15h3"/>'],
        ['label' => 'Dispatched', 'count' => $counts['shipped'] ?? 0, 'color' => 'indigo', 'icon' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5l-4-4h-4"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>'],
        ['label' => 'Delivered', 'count' => $counts['delivered'] ?? 0, 'color' => 'green', 'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
    ];
@endphp

<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
    @foreach($stats as $s)
        <div class="group bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-{{ $s['color'] }}-500/5 transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center gap-4 mb-3">
                <div class="p-3 bg-{{ $s['color'] }}-50 text-{{ $s['color'] }}-600 rounded-2xl group-hover:scale-110 transition-transform duration-500 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        {!! $s['icon'] !!}
                    </svg>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400 group-hover:text-{{ $s['color'] }}-600 transition-colors">{{ $s['label'] }}</span>
            </div>
            <div class="text-3xl font-black text-gray-900 tracking-tighter">{{ number_format($s['count']) }}</div>
            <div class="mt-2 h-1 w-full bg-gray-50 rounded-full overflow-hidden">
                <div class="h-full bg-{{ $s['color'] }}-500 transition-all duration-1000 ease-out" style="width: {{ $s['count'] > 0 ? '65%' : '0%' }}"></div>
            </div>
        </div>
    @endforeach
</div>

<!-- Navigation & Filter Hub -->
<div class="flex flex-col space-y-4 mt-8" x-data="{
    performFilter() {
        const url = new URL(window.location.href);
        url.searchParams.delete('page');
        const formData = new FormData($refs.filterForm);
        for (let [key, value] of formData.entries()) {
            if (value) url.searchParams.set(key, value);
            else url.searchParams.delete(key);
        }
        url.searchParams.set('search', $refs.searchInput.value);
        this.loadData(url.toString());
    }
}">
    
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 p-3 bg-gray-50/50 rounded-[40px] border border-gray-100/50 backdrop-blur-sm">
        <div class="flex flex-wrap items-center gap-2">
            @php
                $tabs = [
                    ['label' => 'Active', 'status' => null, 'count' => $counts['active'] ?? 0],
                    ['label' => 'Fresh', 'status' => 'confirmed', 'count' => $counts['confirmed'] ?? 0],
                    ['label' => 'Packing', 'status' => 'processing', 'count' => $counts['processing'] ?? 0],
                    ['label' => 'Ready', 'status' => 'ready_to_ship', 'count' => $counts['ready_to_ship'] ?? 0],
                    ['label' => 'Dispatched', 'status' => 'shipped', 'count' => $counts['shipped'] ?? 0],
                    ['label' => 'History', 'status' => 'delivered', 'count' => $counts['delivered'] ?? 0],
                    ['label' => 'Void', 'status' => 'cancelled', 'count' => $counts['cancelled'] ?? 0],
                ];
            @endphp

            @foreach($tabs as $tab)
                @php
                    $isActive = (is_null($tab['status']) && (!request()->has('status') || request('status') === 'active')) || (request('status') === $tab['status']);
                @endphp
                <a href="{{ route('tenant.processing.orders.index', array_merge(request()->query(), ['status' => $tab['status'], 'page' => null])) }}"
                    @click.prevent="loadData($el.href)"
                    class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-3xl text-[11px] font-black uppercase tracking-widest transition-all duration-500 {{ $isActive ? 'bg-gray-900 text-white shadow-xl shadow-gray-900/10 scale-105 z-10' : 'bg-white text-gray-500 hover:text-gray-900 border border-gray-100 hover:border-gray-300 shadow-sm' }}">
                    {{ $tab['label'] }}
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-[9px] font-black rounded-lg {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-400' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
            <form action="{{ url()->current() }}" method="GET" x-ref="filterForm" 
                @submit.prevent="performFilter" 
                @per-page-change.window="$refs.perPageInput.value = $event.detail.value; performFilter()"
                class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                        <input type="hidden" name="per_page" x-ref="perPageInput" value="{{ request('per_page', 15) }}">

                {{-- Date Trigger --}}
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="h-11 px-4 flex items-center gap-2 bg-white border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-gray-900 hover:border-gray-300 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span x-text="new URLSearchParams(window.location.search).get('date_filter')?.replace('_', ' ') || 'Timeline'"></span>
                    </button>
                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 top-full mt-2 w-64 p-4 bg-white rounded-3xl border border-gray-100 shadow-2xl z-50 animate-in zoom-in-95 duration-200">
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            @foreach(['today', 'yesterday', 'this_week', 'this_month'] as $df)
                                <button type="button" @click=\"$refs.dateFilterInput.value = '{{ $df }}'; performFilter(); open = false;\" class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-500 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-all text-left">
                                    {{ str_replace('_', ' ', $df) }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="date_filter" x-ref="dateFilterInput" value="{{ request('date_filter') }}">
                        <div class="pt-4 border-t border-gray-50">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-3 px-1">Precise Range</p>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full mb-2 bg-gray-50 border-none rounded-xl text-xs font-bold px-3 py-2 text-gray-900">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full mb-4 bg-gray-50 border-none rounded-xl text-xs font-bold px-3 py-2 text-gray-900">
                            <button type="button" @click=\"$refs.dateFilterInput.value = 'custom'; performFilter(); open = false;\" class="w-full py-2.5 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all">Enable Range</button>
                        </div>
                    </div>
                </div>

                {{-- Search --}}
                <div class="relative group w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-gray-900 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <input type="text" name="search" x-ref="searchInput" value="{{ request('search') }}"
                        @input.debounce.500ms="performFilter()"
                        placeholder="Scan for order reference..."
                        class="w-full h-11 pl-10 pr-4 bg-white border border-gray-100 rounded-2xl text-[11px] font-bold focus:ring-4 focus:ring-gray-900/5 transition-all shadow-sm outline-none placeholder:text-gray-400">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Operations & Toolbox -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-8">
    <div class="flex items-center gap-4">
        <div class="flex items-center px-3 py-2 rounded-2xl bg-white border border-gray-100 shadow-sm hover:border-gray-200 transition-all cursor-pointer group" title="Select All Visible">
            <input type="checkbox" x-on:change=\"$el.checked ? selected = allIds : selected = []\"
                x-bind:checked=\"selected.length === allIds.length && allIds.length > 0\"
                class="h-5 w-5 rounded-lg border-gray-300 text-gray-900 focus:ring-gray-900/10 cursor-pointer transition-all">
        </div>

        <div x-cloak x-show="selected.length > 0" x-transition class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-widest shadow-sm">
                <span x-text="selected.length"></span> Packets Linked
            </div>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gray-900 text-white shadow-xl shadow-gray-900/20 hover:bg-black transition-all text-[10px] font-black uppercase tracking-widest">
                    Mass Commands
                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m6 9 6 6 6-6"/></svg>
                </button>

                <div x-show="open" style="display: none;" class="absolute left-0 top-full mt-2 w-64 p-2 bg-white rounded-3xl border border-gray-100 shadow-2xl z-50 animate-in zoom-in-95 duration-200">
                    <form action="{{ route('tenant.processing.orders.bulk-status') }}" method="POST" class="p-1 space-y-1">
                        @csrf
                        <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                        <button type="submit" name="status" value="confirmed" x-show=\"isStatusValid('confirmed')\" class="w-full flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-gray-600 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition-all">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Confirm Orders
                        </button>
                        <button type="submit" name="status" value="processing" x-show=\"isStatusValid('processing')\" class="w-full flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-gray-600 hover:bg-purple-50 hover:text-purple-700 rounded-xl transition-all">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span> Start Packing
                        </button>
                        <button type="submit" name="status" value="ready_to_ship" x-show=\"isStatusValid('ready_to_ship')\" class="w-full flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition-all">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Finalize Ready
                        </button>
                    </form>
                    <div class="h-px bg-gray-50 my-2 mx-2"></div>
                    <form action="{{ route('tenant.processing.orders.bulk-print') }}" method="POST" target="_blank" class="p-1 space-y-1">
                        @csrf
                        <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                        <button type="submit" name="type" value="invoice" class="w-full flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-gray-600 hover:bg-gray-50 rounded-xl transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z" stroke-width="2"/></svg> Invoices
                        </button>
                        <button type="submit" name="type" value="cod" class="w-full flex items-center gap-3 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest text-gray-600 hover:bg-gray-50 rounded-xl transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2" stroke-width="2"/><path d="M2 10h20" stroke-width="2"/></svg> COD Receipts
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <form action="{{ route('tenant.orders.export') }}" method="POST">
            @csrf
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @if(request('date_filter'))
                <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @endif
            <input type="hidden" name="ids" :value="selected.join(',')">
            <button type="submit" class="h-11 px-5 flex items-center gap-2 bg-white border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-600 hover:text-gray-900 hover:border-gray-200 transition-all shadow-sm group">
                <svg class="w-4 h-4 text-orange-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Data Export
            </button>
        </form>

        <button @click=\"$dispatch('open-import-modal')\" class="h-11 px-5 flex items-center gap-2 bg-white border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-600 hover:text-gray-900 hover:border-gray-200 transition-all shadow-sm group">
            <svg class="w-4 h-4 text-emerald-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            Import CSV
        </button>
    </div>
</div>

<!-- Orders Grid Container -->
<div id="orders-list-container" class="mt-4">
    @include('tenant.processing.orders.partials.orders-list')
</div>