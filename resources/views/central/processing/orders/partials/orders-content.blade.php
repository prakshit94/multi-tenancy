<!-- Navigation & Filter Hub -->
<div class="flex flex-col space-y-4 mb-8">
    {{-- Tabs & Regional Header --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 p-4 bg-gray-50/50 rounded-[40px] border border-gray-100/50 backdrop-blur-sm shadow-sm ring-1 ring-black/5">
        <div class="flex flex-wrap items-center gap-2">
            @php
                $tabs = [
                    ['label' => 'CONFIRMED', 'status' => 'confirmed', 'icon' => '<path d="m9 12 2 2 4-4"/>', 'color' => 'blue'],
                    ['label' => 'PROCESSING', 'status' => 'processing', 'icon' => '<path d="M12 2 2 7v10l10 5 10-5V7Z"/><path d="m2 7 10 5 10-5"/><path d="M12 22V12"/>', 'color' => 'purple'],
                    ['label' => 'READY TO SHIP', 'status' => 'ready_to_ship', 'icon' => '<path d="M21 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v3m18 0v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8m18 0-9 6-9-6"/>', 'color' => 'emerald'],
                    ['label' => 'SHIPPED', 'status' => 'shipped', 'icon' => '<path d="M10 17h4V5H2v12h3m0 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0m10 0a2 2 0 1 0 4 0 2 2 0 1 0-4 0M13 5h9l-1 7h-8z"/>', 'color' => 'indigo'],
                    ['label' => 'DELIVERD', 'status' => 'delivered', 'icon' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-11.7 8.38 8.38 0 0 1 3.8.9L21 2z"/><path d="M9 11l3 3L22 4"/>', 'color' => 'green'],
                    ['label' => 'CANCELLED', 'status' => 'cancelled', 'icon' => '<path d="M18 6 6 18M6 6l12 12"/>', 'color' => 'orange'],
                ];
            @endphp

            @foreach($tabs as $tab)
                <button 
                    @click="activeStatus = '{{ $tab['status'] }}'; performFilter()"
                    type="button"
                    class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-3xl text-[11px] font-black uppercase tracking-widest transition-all duration-300 transform hover:scale-105"
                    :class="activeStatus === '{{ $tab['status'] }}' ? 'bg-primary text-white shadow-xl shadow-primary/20 z-10' : 'bg-white text-gray-500 hover:text-gray-900 border border-gray-100 hover:border-gray-200 shadow-sm'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        {!! $tab['icon'] !!}
                    </svg>
                    {{ $tab['label'] }}
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-[9px] font-black rounded-lg transition-colors"
                        :class="activeStatus === '{{ $tab['status'] }}' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-400'">
                        {{ $counts[$tab['status']] ?? 0 }}
                    </span>
                </button>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            <form id="filter-form" @submit.prevent="performFilter()" 
                @per-page-change.window="$el.querySelector('input[name=per_page]').value = $event.detail.value; performFilter()"
                class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">
                
                {{-- Searchable District --}}
                <div class="relative min-w-[160px]" x-data="{ 
                    open: false, search: '', options: @js($districts), selected: '{{ request('district') }}',
                    get filteredOptions() { return !this.search ? this.options : this.options.filter(o => o.toLowerCase().includes(this.search.toLowerCase())); },
                    select(val) { this.selected = val; this.open = false; this.search = ''; $nextTick(() => { $refs.districtInput.value = val; $refs.districtInput.dispatchEvent(new Event('change')); performFilter(); }); }
                }" @click.away="open = false">
                    <input type="hidden" name="district" x-ref="districtInput" value="{{ request('district') }}">
                    <button type="button" @click="open = !open" class="w-full h-11 px-4 bg-white border border-gray-200 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-between gap-2 shadow-sm hover:border-primary transition-all">
                        <span x-text="selected || 'District'" class="truncate"></span>
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="open" x-transition.origin.top class="absolute z-[110] left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-2xl overflow-hidden max-h-72 flex flex-col">
                        <input type="text" x-model="search" placeholder="Search..." class="w-full px-3 py-2 text-[10px] font-bold border-b border-gray-50 bg-gray-50/50 outline-none uppercase">
                        <div class="overflow-y-auto flex-1 custom-scrollbar py-1">
                            <button type="button" @click="select('')" class="w-full px-4 py-2 text-left text-[10px] font-black uppercase text-gray-400 hover:bg-gray-50">All Districts</button>
                            <template x-for="opt in filteredOptions" :key="opt">
                                <button type="button" @click="select(opt)" class="w-full px-4 py-2 text-left text-[10px] font-bold uppercase text-gray-600 hover:bg-primary/5 hover:text-primary transition-all flex items-center justify-between" :class="selected === opt ? 'bg-primary/10 text-primary' : ''">
                                    <span x-text="opt"></span>
                                    <svg x-show="selected === opt" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Searchable Taluka --}}
                <div class="relative min-w-[160px]" x-data="{ 
                    open: false, search: '', options: @js($talukas), selected: '{{ request('taluka') }}',
                    get filteredOptions() { return !this.search ? this.options : this.options.filter(o => o.toLowerCase().includes(this.search.toLowerCase())); },
                    select(val) { this.selected = val; this.open = false; this.search = ''; $nextTick(() => { $refs.talukaInput.value = val; $refs.talukaInput.dispatchEvent(new Event('change')); performFilter(); }); }
                }" @click.away="open = false">
                    <input type="hidden" name="taluka" x-ref="talukaInput" value="{{ request('taluka') }}">
                    <button type="button" @click="open = !open" class="w-full h-11 px-4 bg-white border border-gray-200 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-between gap-2 shadow-sm hover:border-primary transition-all">
                        <span x-text="selected || 'Taluka'" class="truncate"></span>
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="open" x-transition.origin.top class="absolute z-[110] left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-2xl overflow-hidden max-h-72 flex flex-col">
                        <input type="text" x-model="search" placeholder="Search..." class="w-full px-3 py-2 text-[10px] font-bold border-b border-gray-50 bg-gray-50/50 outline-none uppercase">
                        <div class="overflow-y-auto flex-1 custom-scrollbar py-1">
                            <button type="button" @click="select('')" class="w-full px-4 py-2 text-left text-[10px] font-black uppercase text-gray-400 hover:bg-gray-50">All Talukas</button>
                            <template x-for="opt in filteredOptions" :key="opt">
                                <button type="button" @click="select(opt)" class="w-full px-4 py-2 text-left text-[10px] font-bold uppercase text-gray-600 hover:bg-primary/5 hover:text-primary transition-all flex items-center justify-between" :class="selected === opt ? 'bg-primary/10 text-primary' : ''">
                                    <span x-text="opt"></span>
                                    <svg x-show="selected === opt" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
    @php
        $stats = [
            ['label' => 'Confirmed', 'count' => $counts['confirmed'] ?? 0, 'color' => 'blue', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>'],
            ['label' => 'Processing', 'count' => $counts['processing'] ?? 0, 'color' => 'purple', 'icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'],
            ['label' => 'Ready to Ship', 'count' => $counts['ready_to_ship'] ?? 0, 'color' => 'emerald', 'icon' => '<path d="M2 9h20"/><path d="M4 9h2V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v4h2"/><path d="M22 9v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9Z"/><path d="M12 12v3"/><path d="M12 15h3"/>'],
            ['label' => 'Dispatched', 'count' => $counts['shipped'] ?? 0, 'color' => 'indigo', 'icon' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5l-4-4h-4"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>'],
            ['label' => 'Deliverd', 'count' => $counts['delivered'] ?? 0, 'color' => 'green', 'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
        ];
    @endphp

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
                <div class="h-full bg-{{ $s['color'] }}-500 transition-all duration-1000 ease-out shadow-[0_0_8px_rgba(var(--{{ $s['color'] }}-500),0.4)]" style="width: {{ $s['count'] > 0 ? '65%' : '0%' }}"></div>
            </div>
        </div>
    @endforeach
</div>

<!-- District Insights -->
@if($districtCounts->count() > 0)
    <div class="flex flex-col gap-3 mt-8" x-data="{ showDistricts: false }">
        <div class="flex items-center justify-between px-2">
            <div class="flex items-center gap-3">
                <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-500">Regional Performance</span>
            </div>
            <button @click="showDistricts = !showDistricts" 
                    class="text-[11px] font-black uppercase tracking-widest text-primary hover:text-primary-foreground bg-primary/5 hover:bg-primary px-4 py-1.5 rounded-full transition-all duration-300 flex items-center gap-2 group/btn shadow-sm">
                <span x-text="showDistricts ? 'Collapse Insights' : 'Expand All Regions'"></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" 
                     class="transition-transform duration-500" :class="showDistricts ? 'rotate-180' : ''">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </button>
        </div>
        <div x-show="showDistricts" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3 pb-4">
            @foreach($districtCounts as $stat)
                @php
                    $isActive = request('district') == $stat->district;
                    $districtUrl = request()->fullUrlWithQuery(['district' => $stat->district]);
                @endphp
                <a href="{{ $districtUrl }}" 
                   @click.prevent="loadData('{{ $districtUrl }}')"
                   class="flex flex-col gap-2 p-4 rounded-3xl border transition-all duration-300 hover:scale-[1.05] group {{ $isActive ? 'bg-primary text-white border-primary shadow-xl shadow-primary/20 scale-105 z-10' : 'bg-white border-gray-100 hover:border-primary/20 text-gray-600 hover:shadow-lg' }}">
                    <span class="text-[10px] font-black uppercase tracking-widest truncate {{ $isActive ? 'text-white/70' : 'text-gray-400' }}">{{ $stat->district ?: 'Global' }}</span>
                    <span class="text-xl font-black tracking-tight">{{ $stat->total }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif




<!-- Toolbar -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2">
    <!-- Left: Bulk Actions -->
    <div class="flex items-center gap-3">
        <div class="flex items-center px-2 py-1.5 rounded-lg hover:bg-muted/50 transition-colors cursor-pointer"
            title="Select All on Page">
            <input type="checkbox" x-on:change="$el.checked ? selected = allIds : selected = []"
                x-bind:checked="selected.length === allIds.length && allIds.length > 0"
                class="h-5 w-5 rounded-md border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
        </div>

        <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms
            class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">

            <div
                class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-semibold shadow-sm">
                <span x-text="selected.length"></span> selected
            </div>

            <!-- Bulk Options Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false"
                    class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-input shadow-sm hover:shadow-md hover:border-primary/30 transition-all duration-200 text-sm font-medium">
                    <span>Bulk Actions</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-muted-foreground group-hover:text-primary transition-colors">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>

                <div x-show="open" style="display: none;"
                    class="absolute left-0 top-full z-50 mt-2 min-w-[240px] p-1.5 rounded-xl border border-border/60 bg-white/95 backdrop-blur-xl shadow-xl shadow-black/5 animate-in zoom-in-95 fade-in-0 slide-in-from-top-2">

                    <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider">
                        Update Status</div>

                    <form action="{{ route('central.processing.orders.bulk-status') }}" method="POST">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>

                        <button type="submit" name="status" value="confirmed" x-show="isStatusValid('confirmed')"
                            @click="if(!confirm('Are you sure you want to mark ' + selected.length + ' orders as Confirmed?')) $event.preventDefault()"
                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-blue-50 hover:text-blue-600 text-foreground/80">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Mark as Confirmed
                        </button>
                        <button type="submit" name="status" value="processing" x-show="isStatusValid('processing')"
                            @click="if(!confirm('Are you sure you want to mark ' + selected.length + ' orders as Processing?')) $event.preventDefault()"
                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-purple-50 hover:text-purple-600 text-foreground/80">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            Mark as Processing
                        </button>
                        <button type="submit" name="status" value="shipped" x-show="isStatusValid('shipped')"
                            @click="if(!confirm('Are you sure you want to dispatch ' + selected.length + ' orders?')) $event.preventDefault()"
                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-indigo-50 hover:text-indigo-600 text-foreground/80">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Mark as Dispatched
                        </button>
                    </form>

                    <div class="my-1.5 h-px bg-border/50"></div>
                    <div class="px-2 py-1 text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider">
                        Printing</div>

                    <form action="{{ route('central.processing.orders.bulk-print') }}" method="POST">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>

                        <button type="submit" name="type" value="invoice"
                            @click="$dispatch('notify', { type: 'success', message: 'Invoices download started' })"
                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground text-foreground/80">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M6 9V2h12v7" />
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                <path d="M6 14h12v8H6z" />
                            </svg>
                            Print Invoices
                        </button>
                        <button type="submit" name="type" value="cod"
                            @click="$dispatch('notify', { type: 'success', message: 'COD Receipts download started' })"
                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground text-foreground/80">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="5" rx="2" />
                                <line x1="2" x2="22" y1="10" y2="10" />
                            </svg>
                            Print COD Receipts
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Right: Additional Actions (Search & Export & Date Filter) -->
    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">

        <!-- Date Filter -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/50 border border-input shadow-sm hover:shadow-md hover:border-primary/30 transition-all text-sm font-medium">
                <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                <span
                    x-text="new URLSearchParams(window.location.search).get('date_filter') ? new URLSearchParams(window.location.search).get('date_filter').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Date Filter'"></span>
                <svg class="w-3 h-3 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" style="display: none;"
                class="absolute right-0 top-full z-50 mt-2 w-64 p-2 rounded-xl border border-border/60 bg-popover/95 backdrop-blur-xl shadow-xl animate-in zoom-in-95 fade-in-0 slide-in-from-top-2">

                <form action="{{ url()->current() }}" method="GET" class="space-y-2" x-data="{
                                submitFilter(e) {
                                    e.preventDefault();
                                    const formData = new FormData(e.target);
                                    
                                    if (e.submitter && e.submitter.name) {
                                        formData.append(e.submitter.name, e.submitter.value);
                                    }

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
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit" name="date_filter" value="today"
                            class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-muted text-left">Today</button>
                        <button type="submit" name="date_filter" value="yesterday"
                            class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-muted text-left">Yesterday</button>
                        <button type="submit" name="date_filter" value="this_week"
                            class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-muted text-left">This
                            Week</button>
                        <button type="submit" name="date_filter" value="this_month"
                            class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-muted text-left">This
                            Month</button>
                    </div>
                    <div class="h-px bg-border/50 my-1"></div>
                    <div class="px-2 pb-1">
                        <p class="text-[10px] font-bold uppercase text-muted-foreground mb-1">Custom Range</p>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full text-xs h-8 rounded-md border-input bg-background mb-1">
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full text-xs h-8 rounded-md border-input bg-background mb-2">
                        <button type="submit" name="date_filter" value="custom"
                            class="w-full px-3 py-1.5 text-xs font-bold text-primary-foreground bg-primary rounded-md hover:bg-primary/90">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Form moved to filters bar -->

        <form action="{{ route('central.orders.export') }}" method="POST">
            @csrf
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @if(request('date_filter'))
                <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                @endif
                @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @endif
            @if(request('district')) <input type="hidden" name="district" value="{{ request('district') }}"> @endif
            @if(request('taluka')) <input type="hidden" name="taluka" value="{{ request('taluka') }}"> @endif
            @if(request('village')) <input type="hidden" name="village" value="{{ request('village') }}"> @endif
            <input type="hidden" name="ids" :value="selected.join(',')">

            <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-100 px-4 py-2.5 text-sm font-semibold text-orange-700 shadow-sm hover:bg-orange-200 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export
            </button>
        </form>

        <button @click="$dispatch('open-import-modal')"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-100 px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-200 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                </path>
            </svg>
            Import CSV
        </button>
    </div>
</div>

<!-- Orders Grid Container -->
<div id="orders-list-container" class="mt-4">
    @include('central.processing.orders.partials.orders-list')
</div>