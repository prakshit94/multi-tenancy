@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500"
         x-data="{ 
            search: '{{ request('search') }}',
            currentStatus: '{{ request('status', 'all') }}',
            currentCourier: '{{ request('courier', 'all') }}',
            isSearching: false,
            
            clearSearch() {
                this.search = '';
                this.currentCourier = 'all';
                this.performSearch();
            },

            async performSearch() {
                this.isSearching = true;
                const url = new URL(window.location.origin + window.location.pathname);
                
                if (this.currentStatus) url.searchParams.set('status', this.currentStatus);
                if (this.currentCourier && this.currentCourier !== 'all') url.searchParams.set('courier', this.currentCourier);
                if (this.search) url.searchParams.set('search', this.search);
                url.searchParams.set('ajax', '1');

                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    
                    const container = document.getElementById('returns-list-container');
                    if (container) container.innerHTML = data.html;
                    
                    if (data.stats) {
                        document.getElementById('stat-total').textContent = data.stats.total;
                        document.getElementById('stat-approved').textContent = data.stats.approved;
                        document.getElementById('stat-received').textContent = data.stats.received;
                    }

                    const historyUrl = new URL(window.location.origin + window.location.pathname);
                    if (this.currentStatus) historyUrl.searchParams.set('status', this.currentStatus);
                    if (this.currentCourier && this.currentCourier !== 'all') historyUrl.searchParams.set('courier', this.currentCourier);
                    if (this.search) historyUrl.searchParams.set('search', this.search);
                    window.history.pushState({}, '', historyUrl);
                } catch (err) {
                    console.error('Search failed:', err);
                } finally {
                    this.isSearching = false;
                }
            },

            async loadUrl(url) {
                this.isSearching = true;
                const ajaxUrl = new URL(url);
                ajaxUrl.searchParams.set('ajax', '1');
                
                try {
                    const res = await fetch(ajaxUrl);
                    const data = await res.json();
                    
                    const container = document.getElementById('returns-list-container');
                    if (container) container.innerHTML = data.html;
                    
                    if (data.stats) {
                        document.getElementById('stat-total').textContent = data.stats.total;
                        document.getElementById('stat-approved').textContent = data.stats.approved;
                        document.getElementById('stat-received').textContent = data.stats.received;
                    }

                    const newParams = new URL(url).searchParams;
                    this.search = newParams.get('search') || '';
                    this.currentStatus = newParams.get('status') || 'all';
                    this.currentCourier = newParams.get('courier') || 'all';

                    window.history.pushState({}, '', url);
                } catch (err) {
                    console.error('Fetch failed:', err);
                } finally {
                    this.isSearching = false;
                }
            }
         }"
         x-init="
            $el.addEventListener('click', (e) => {
                const link = e.target.closest('.ajax-pagination a');
                if (link) {
                    e.preventDefault();
                    loadUrl(link.href);
                }
            })
         ">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    Return Processing
                </h1>
                <p class="text-muted-foreground text-sm font-medium">
                    Manage and process customer returns efficiently.
                </p>
            </div>
            <div>
                <a href="{{ route('central.processing.orders.index') }}"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm hover:shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-500 group-hover:text-gray-900 transition-colors">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Back to Orders
                </a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Requests</p>
                <p class="text-2xl font-black text-gray-900" id="stat-total">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Waiting Receipt</p>
                <p class="text-2xl font-black text-blue-600" id="stat-approved">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider mb-1">Received</p>
                <p class="text-2xl font-black text-emerald-600" id="stat-received">{{ $stats['received'] }}</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-2 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center p-1 bg-gray-50 rounded-xl w-full sm:w-auto overflow-x-auto no-scrollbar">
                <a href="{{ route('central.processing.returns.index', ['status' => 'all']) }}" 
                   @click.prevent="loadUrl($event.target.href)"
                   :class="currentStatus === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all whitespace-nowrap">
                    All
                </a>
                <a href="{{ route('central.processing.returns.index', ['status' => 'approved']) }}" 
                   @click.prevent="loadUrl($event.target.href)"
                   :class="currentStatus === 'approved' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all whitespace-nowrap">
                    Approved
                </a>
                <a href="{{ route('central.processing.returns.index', ['status' => 'received']) }}" 
                   @click.prevent="loadUrl($event.target.href)"
                   :class="currentStatus === 'received' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all whitespace-nowrap">
                    Received
                </a>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-48">
                    <select x-model="currentCourier" @change="performSearch()"
                            class="w-full pl-3 pr-8 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-gray-900 transition-all outline-none appearance-none">
                        <option value="all">All Couriers</option>
                        @foreach($couriers as $courier)
                            <option value="{{ $courier }}">{{ $courier }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="search" @input.debounce.500ms="performSearch()"
                           placeholder="Search RMA, Order, Tracking..."
                           class="w-full pl-10 pr-10 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-gray-900 transition-all outline-none">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg x-show="!isSearching" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <svg x-show="isSearching" class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <button x-show="search.length > 0" @click="clearSearch()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="returns-list-container">
            @include('central.processing.returns.partials.returns-content')
        </div>
    </div>

    <!-- Premium Receive Modal -->
    <div x-data="{ 
            open: false, 
            returnId: null, 
            rma: '', 
            items: [],
            processing: false,
            error: null,

            init() {
                window.addEventListener('open-receive-modal', (e) => {
                    this.returnId = e.detail.id;
                    this.rma = e.detail.rma;
                    // Deep copy and initialize
                    this.items = e.detail.items.map(i => ({
                        ...i, 
                        new_condition: 'sellable', 
                        verified: true
                    })); 
                    this.error = null;
                    this.open = true;
                });
            },

            submit() {
                // CSRF Check
                const meta = document.querySelector('meta[name=csrf-token]');
                if (!meta || !meta.content) {
                    this.error = 'Security Token (CSRF) missing. Please refresh the page.';
                    return;
                }

                this.processing = true;
                this.error = null;

                try {
                    // Create form dynamically
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/processing/returns/${this.returnId}/receive`;
                    form.style.display = 'none'; // Hide it

                    // Add CSRF
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = meta.content;
                    form.appendChild(csrf);

                    // Add Items
                    this.items.forEach((item, index) => {
                        // ID
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = `items[${index}][id]`;
                        idInput.value = item.id;
                        form.appendChild(idInput);

                        // Condition
                        const conditionInput = document.createElement('input');
                        conditionInput.type = 'hidden';
                        conditionInput.name = `items[${index}][condition]`;
                        conditionInput.value = item.new_condition;
                        form.appendChild(conditionInput);
                    });

                    document.body.appendChild(form);
                    form.submit();
                } catch (e) {
                    console.error(e);
                    this.error = 'An unexpected error occurred. Please try again.';
                    this.processing = false;
                }
            }
        }" x-show="open" style="display: none;" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md transition-opacity" x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="if(!processing) open = false">
        </div>

        <!-- Modal Panel -->
        <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-black/5 transform transition-all"
            x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95">

            <!-- Modal Header -->
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                                <line x1="12" y1="22.08" x2="12" y2="12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Receive Items</h3>
                            <p class="text-sm font-medium text-gray-500">RMA: <span x-text="rma"
                                    class="font-mono text-gray-700"></span></p>
                        </div>
                    </div>
                </div>
                <button @click="if(!processing) open = false"
                    class="text-gray-400 hover:text-gray-900 bg-white hover:bg-gray-100 p-2 rounded-full transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Error Message -->
            <div x-show="error" class="px-8 pt-6 pb-0" style="display: none;">
                <div
                    class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <span x-text="error"></span>
                </div>
            </div>

            <!-- Items List -->
            <div class="p-8 space-y-4 overflow-y-auto overflow-x-hidden custom-scrollbar flex-1 bg-white">
                <template x-for="(item, index) in items" :key="item.id">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 p-5 rounded-2xl border border-gray-100 bg-white hover:border-gray-200 hover:shadow-sm transition-all group">
                        <!-- Product Info -->
                        <div class="flex items-center gap-4 flex-1">
                            <div
                                class="w-14 h-14 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0 text-gray-300">
                                <template x-if="item.image">
                                    <img :src="item.image" class="w-full h-full object-cover rounded-xl" alt="Product">
                                </template>
                                <template x-if="!item.image">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                </template>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-sm" x-text="item.name"></h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider"
                                        x-text="item.sku || 'N/A'"></span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span class="text-xs text-gray-500 font-medium">Qty: <span x-text="item.quantity"
                                            class="text-gray-900"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Condition Selector -->
                        <div class="flex flex-col gap-1.5 sm:text-right min-w-[180px]">
                            <label class="text-[10px] font-bold uppercase text-gray-400 tracking-wider">Condition
                                Received</label>
                            <div class="relative">
                                <select x-model="item.new_condition"
                                    class="w-full appearance-none pl-4 pr-10 py-2.5 rounded-xl text-sm font-bold border-gray-200 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 transition-all cursor-pointer"
                                    :class="item.new_condition === 'sellable' ? 'text-emerald-700 bg-emerald-50/50 border-emerald-100' : 'text-red-700 bg-red-50/50 border-red-100'">
                                    <option value="sellable">✅ Sellable (Restock)</option>
                                    <option value="damaged">❌ Damaged (Scrap)</option>
                                </select>
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="items.length === 0" style="display: none;" class="text-center py-10">
                    <p class="text-gray-500">No items found for this return.</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 shrink-0">
                <button @click="if(!processing) open = false"
                    class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 hover:bg-white border border-transparent hover:border-gray-200 rounded-xl transition-all disabled:opacity-50"
                    :disabled="processing">
                    Cancel
                </button>
                <button @click="submit()" :disabled="processing"
                    class="px-8 py-3 text-sm font-bold bg-gray-900 text-white rounded-xl hover:bg-black hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 flex items-center gap-2 shadow-lg shadow-gray-900/20">
                    <svg x-show="!processing" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 7-7 7 7" />
                        <path d="M12 19V5" />
                    </svg>
                    <svg x-show="processing" class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                    </svg>
                    <span x-text="processing ? 'Processing...' : 'Confirm Receipt'"></span>
                </button>
            </div>
        </div>
    </div>
@endsection