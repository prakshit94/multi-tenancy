<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-gray-50 text-gray-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                    <line x1="16" x2="16" y1="2" y2="6" />
                    <line x1="8" x2="8" y1="2" y2="6" />
                    <line x1="3" x2="21" y1="10" y2="10" />
                    <path d="M8 14h.01" />
                    <path d="M12 14h.01" />
                    <path d="M16 14h.01" />
                    <path d="M8 18h.01" />
                    <path d="M12 18h.01" />
                    <path d="M16 18h.01" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-500">Total Active</span>
        </div>
        <div class="text-2xl font-black text-gray-900">{{ $counts['active'] ?? 0 }}</div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-500">Confirmed</span>
        </div>
        <div class="text-2xl font-black text-gray-900">{{ $counts['confirmed'] ?? 0 }}</div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                    <polyline points="3.29 7 12 12 20.71 7" />
                    <line x1="12" x2="12" y1="22" y2="12" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-500">Processing</span>
        </div>
        <div class="text-2xl font-black text-gray-900">{{ $counts['processing'] ?? 0 }}</div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z" />
                    <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z" />
                    <path d="M7 21h10" />
                    <path d="M12 3v18" />
                    <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-500">Ready to Ship</span>
        </div>
        <div class="text-2xl font-black text-gray-900">{{ $counts['ready_to_ship'] ?? 0 }}</div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11" />
                    <path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2" />
                    <circle cx="7" cy="18" r="2" />
                    <circle cx="17" cy="18" r="2" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-500">Dispatched</span>
        </div>
        <div class="text-2xl font-black text-gray-900">{{ $counts['shipped'] ?? 0 }}</div>
    </div>
</div>

<!-- District Insights -->
@if($districtCounts->count() > 0)
    <div class="flex flex-col gap-2 mt-6" x-data="{ showDistricts: false }">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">District Insights</span>
            </div>
            <button @click="showDistricts = !showDistricts" 
                    class="text-[10px] font-bold uppercase tracking-widest text-primary hover:text-primary/80 transition-colors flex items-center gap-1">
                <span x-text="showDistricts ? 'Hide' : 'Show Full List'"></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                     class="transition-transform duration-300" :class="showDistricts ? 'rotate-180' : ''">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </button>
        </div>
        <div x-show="showDistricts" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="flex flex-wrap items-center gap-2 pb-2">
            @foreach($districtCounts as $stat)
                @php
                    $districtUrl = request()->fullUrlWithQuery(['district' => $stat->district]);
                @endphp
                <a href="{{ $districtUrl }}" 
                   @click.prevent="loadData('{{ $districtUrl }}')"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl bg-white/40 dark:bg-black/20 border border-white/20 dark:border-white/5 backdrop-blur-md shadow-sm transition-all duration-300 hover:shadow-md hover:scale-[1.02] group whitespace-nowrap {{ request('district') == $stat->district ? 'ring-2 ring-primary/20 border-primary/50 bg-primary/5' : '' }}">
                    <span class="text-xs font-semibold text-foreground/80 group-hover:text-primary transition-colors">{{ $stat->district ?: 'Unknown' }}</span>
                    <span class="px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-bold ring-1 ring-primary/20">{{ $stat->total }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif


<!-- Filters & Navigation Tabs -->
<div
    class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 sticky top-0 z-30 bg-gray-50/95 backdrop-blur-xl p-2 rounded-2xl border border-gray-200/60 shadow-sm mt-4">
    <div class="flex flex-wrap items-center gap-1 w-full xl:w-auto">
        @php
            $navItems = [
                ['label' => 'Active', 'route' => route('central.processing.orders.index'), 'active' => !request('status'), 'count' => $counts['active'] ?? 0],
                ['label' => 'Confirmed', 'route' => route('central.processing.orders.index', ['status' => 'confirmed']), 'active' => request('status') === 'confirmed', 'count' => $counts['confirmed'] ?? 0],
                ['label' => 'Processing', 'route' => route('central.processing.orders.index', ['status' => 'processing']), 'active' => request('status') === 'processing', 'count' => $counts['processing'] ?? 0],
                ['label' => 'Ready to Ship', 'route' => route('central.processing.orders.index', ['status' => 'ready_to_ship']), 'active' => request('status') === 'ready_to_ship', 'count' => $counts['ready_to_ship'] ?? 0],
                ['label' => 'Dispatched', 'route' => route('central.processing.orders.index', ['status' => 'shipped']), 'active' => request('status') === 'shipped', 'count' => $counts['shipped'] ?? 0],
                ['label' => 'Delivered', 'route' => route('central.processing.orders.index', ['status' => 'delivered']), 'active' => request('status') === 'delivered', 'count' => $counts['delivered'] ?? 0],
                ['label' => 'Cancelled', 'route' => route('central.processing.orders.index', ['status' => 'cancelled']), 'active' => request('status') === 'cancelled', 'count' => $counts['cancelled'] ?? 0],
                ['label' => 'All History', 'route' => route('central.processing.orders.index', ['status' => 'all']), 'active' => request('status') === 'all', 'count' => $counts['all'] ?? 0],
            ];
        @endphp

        @foreach($navItems as $item)
            <a href="{{ $item['route'] }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all whitespace-nowrap {{ $item['active'] ? 'bg-white text-gray-900 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                {{ $item['label'] }}
                <span
                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $item['active'] ? 'bg-primary/10 text-primary' : 'bg-gray-200 text-gray-600' }}">
                    {{ $item['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Regional Filters & Search -->
    <div class="flex items-center gap-3 w-full xl:w-auto py-1">
                <form action="{{ url()->current() }}" method="GET" id="filter-form" class="flex flex-wrap items-center gap-3" x-data="{
                        performFilter() {
                            const url = new URL(window.location.href);
                            url.searchParams.delete('page');
                            
                            // Collect all form values
                            const formData = new FormData($refs.filterForm);
                            for (let [key, value] of formData.entries()) {
                                if (value) {
                                    url.searchParams.set(key, value);
                                } else {
                                    url.searchParams.delete(key);
                                }
                            }
                            
                            this.loadData(url.toString());
                        }
                    }" x-ref="filterForm" @submit.prevent="performFilter" @pagination-click.window="loadData($event.detail.url)">

            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            @if(request('date_filter'))
                <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
            @endif

            <!-- District Filter -->
            <div class="relative min-w-[140px]">
                <select name="district" @change="performFilter"
                    class="w-full h-10 pl-3 pr-8 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm outline-none appearance-none">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d }}" {{ request('district') == $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </div>

            <!-- Taluka Filter -->
            <div class="relative min-w-[140px]">
                <select name="taluka" @change="performFilter"
                    class="w-full h-10 pl-3 pr-8 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm outline-none appearance-none">
                    <option value="">All Talukas</option>
                    @foreach($talukas as $t)
                        <option value="{{ $t }}" {{ request('taluka') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </div>

            <!-- Village Filter -->
            <div class="relative min-w-[140px]">
                <input type="text" name="village" value="{{ request('village') }}" placeholder="Village..."
                    @input.debounce.500ms="performFilter"
                    class="w-full h-10 pl-3 pr-4 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm outline-none">
            </div>

            <!-- Search -->
            <div class="relative w-full xl:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search orders..." @refresh-orders.window="performFilter()"
                    @input.debounce.500ms="performFilter"
                    class="w-full h-10 pl-10 pr-4 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm outline-none">
            </div>
        </form>
    </div>

</div>

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
                    </form>

                    <div class="my-1.5 h-px bg-border/50"></div>
                    <div class="px-2 py-1 text-xs font-semibold text-muted-foreground/60 uppercase tracking-wider">
                        Printing</div>

                    <form action="{{ route('central.processing.orders.bulk-print') }}" method="POST" target="_blank">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>

                        <button type="submit" name="type" value="invoice"
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
                                        if (value) {
                                            url.searchParams.set(key, value);
                                        } else {
                                            url.searchParams.delete(key);
                                        }
                                    }
                                    
                                    url.searchParams.delete('page'); 
                                    
                                    const urlStr = url.toString();
                                    
                                    fetch(urlStr, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                        .then(response => response.ok ? response.text() : Promise.reject(response))
                                        .then(html => {
                                            document.getElementById('orders-content').innerHTML = html;
                                            window.history.pushState({}, '', urlStr);
                                            open = false; 
                                        })
                                        .catch(err => console.error('Filter Failed:', err));
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

<!-- Orders Grid -->
<div id="orders-list-container"
    @click="if ($event.target.closest('nav a')) { $event.preventDefault(); $dispatch('pagination-click', { url: $event.target.closest('nav a').href }); }">
    @include('central.processing.orders.partials.orders-list')
</div>