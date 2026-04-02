@extends('layouts.app')

@section('content')
    <div class="flex flex-1 flex-col space-y-8 p-4 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500" 
         x-data="{ 
            showModal: false, 
            currentReturn: null,
            items: [],
            actionUrl: '',
            
            inspect(ret) {
                this.currentReturn = ret;
                this.actionUrl = '{{ route('tenant.processing.returns.receive', ':id') }}'.replace(':id', ret.id);
                this.items = ret.items.map(i => ({
                    item_id: i.id,
                    name: i.product ? i.product.name : 'Unknown Product',
                    quantity: i.quantity,
                    requested_condition: i.condition,
                    condition: i.condition,
                    verified: false,
                    image_url: (i.product && i.product.image_url) ? i.product.image_url : null
                }));
                this.showModal = true;
                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            },
            
            closeModal() {
                this.showModal = false;
                document.body.style.overflow = 'auto';
            },
            
            get canSubmit() {
                return this.items.length > 0 && this.items.every(i => i.verified);
            },

            search: '{{ request('search') }}',
            currentStatus: '{{ request('status', 'all') }}',
            isSearching: false,
            
            clearSearch() {
                this.search = '';
                this.performSearch();
            },

            async performSearch() {
                this.isSearching = true;
                const url = new URL(window.location.origin + window.location.pathname);
                const currentParams = new URLSearchParams(window.location.search);
                
                if (this.currentStatus) url.searchParams.set('status', this.currentStatus);
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

                    // Sync search and status state with URL
                    const newParams = new URL(url).searchParams;
                    this.search = newParams.get('search') || '';
                    this.currentStatus = newParams.get('status') || 'all';

                    window.history.pushState({}, '', url);
                } catch (err) {
                    console.error('Fetch failed:', err);
                } finally {
                    this.isSearching = false;
                }
            }
         }"
         @keydown.escape.window="closeModal()"
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
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-400 bg-clip-text text-transparent">
                    Return Processing
                </h1>
                <p class="text-muted-foreground text-sm">
                    Inspect and receive returned items into inventory.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('tenant.processing.orders.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Order Processing
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Requests</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white" id="stat-total">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Waiting Receipt</p>
                <p class="text-2xl font-black text-blue-600" id="stat-approved">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider mb-1">Received</p>
                <p class="text-2xl font-black text-emerald-600" id="stat-received">{{ $stats['received'] }}</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white/50 dark:bg-zinc-900/50 p-2 rounded-2xl border border-gray-100 dark:border-zinc-800 backdrop-blur-sm">
            <div class="flex items-center p-1 bg-gray-100 dark:bg-zinc-800 rounded-xl w-full sm:w-auto">
                <a href="{{ route('tenant.processing.returns.index', ['status' => 'all']) }}" 
                   @click.prevent="loadUrl($event.target.href)"
                   :class="currentStatus === 'all' ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all">
                    All
                </a>
                <a href="{{ route('tenant.processing.returns.index', ['status' => 'approved']) }}" 
                   @click.prevent="loadUrl($event.target.href)"
                   :class="currentStatus === 'approved' ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all">
                    Approved
                </a>
                <a href="{{ route('tenant.processing.returns.index', ['status' => 'received']) }}" 
                   @click.prevent="loadUrl($event.target.href)"
                   :class="currentStatus === 'received' ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                   class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold rounded-lg transition-all">
                    Received
                </a>
            </div>


            <div class="relative w-full sm:w-64">
                <input type="text" x-model="search" @input.debounce.500ms="performSearch()"
                       placeholder="Search RMA or Order..."
                       class="w-full pl-10 pr-10 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl text-xs focus:ring-2 focus:ring-gray-900 transition-all outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg x-show="!isSearching" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <svg x-show="isSearching" class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                <button x-show="search.length > 0" @click="clearSearch()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <div id="returns-list-container">
            @include('tenant.processing.returns.partials.returns-content')
        </div>

        <!-- Inspection Modal -->
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-cloak>
            
            <div x-show="showModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="bg-white dark:bg-zinc-900 w-full max-w-2xl rounded-3xl shadow-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden"
                 @click.away="closeModal()">
                
                <form :action="actionUrl" method="POST">
                    @csrf
                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-gray-50 dark:border-zinc-800 flex items-center justify-between bg-gray-50/50 dark:bg-zinc-800/50">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M20 6 9 17l-5-5"/></svg>
                                Inspection Checklist
                            </h3>
                            <p class="text-sm text-gray-500 mt-1" x-text="'Processing ' + (currentReturn ? currentReturn.rma_number : '')"></p>
                        </div>
                        <button type="button" @click="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-xl transition-colors text-gray-400 hover:text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-8 py-6 max-h-[60vh] overflow-y-auto space-y-4 custom-scrollbar">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 rounded-2xl border transition-all duration-200" 
                                 :class="item.verified ? 'bg-blue-50/30 border-blue-200 dark:bg-blue-500/5 dark:border-blue-500/30' : 'bg-white dark:bg-zinc-900 border-gray-100 dark:border-zinc-800'">
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <div class="flex items-start gap-4 flex-1">
                                        <!-- Checkbox -->
                                        <div class="pt-1">
                                            <input type="checkbox" x-model="item.verified" :name="'items['+index+'][verified]'" value="1"
                                                   class="h-6 w-6 rounded-lg border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer shadow-sm transition-all">
                                        </div>

                                        <!-- Product Info -->
                                        <div class="flex items-start gap-3 flex-1">
                                            <div class="h-12 w-12 rounded-xl bg-gray-50 dark:bg-zinc-800 overflow-hidden flex-shrink-0 border border-gray-100 dark:border-zinc-700">
                                                <template x-if="item.image_url">
                                                    <img :src="item.image_url" class="h-full w-full object-cover">
                                                </template>
                                                <template x-if="!item.image_url">
                                                    <div class="h-full w-full flex items-center justify-center text-gray-300">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m21 8-9-4-9 4m18 8-9 4-9-4m18-4-9 4-9-4"/></svg>
                                                    </div>
                                                </template>
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-black text-gray-900 dark:text-white leading-snug" x-text="item.name"></h5>
                                                <p class="text-xs text-gray-500 mt-0.5" x-text="'Requested Qty: ' + item.quantity"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Condition Selector -->
                                    <div class="w-full sm:w-40" x-show="item.verified" x-transition>
                                        <input type="hidden" :name="'items['+index+'][item_id]'" :value="item.item_id">
                                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1.5 px-1">Inspected Condition</label>
                                        <select :name="'items['+index+'][condition]'" x-model="item.condition"
                                                class="w-full h-10 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                                            <option value="sellable">Sellable (Restock)</option>
                                            <option value="damaged">Damaged (No Restock)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-8 py-6 border-t border-gray-50 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-800/50 flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex-1 text-center sm:text-left">
                            <p class="text-xs font-bold" :class="canSubmit ? 'text-emerald-600' : 'text-gray-400'">
                                <template x-if="canSubmit">
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        All items verified and ready for receipt
                                    </span>
                                </template>
                                <template x-if="!canSubmit">
                                    <span x-text="'Please verify ' + items.filter(i => !i.verified).length + ' remaining item(s)'"></span>
                                </template>
                            </p>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="button" @click="closeModal()" class="flex-1 sm:flex-none px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-700 dark:hover:text-white transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="!canSubmit"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-black rounded-2xl transition-all shadow-xl shadow-gray-900/10 dark:shadow-none hover:-translate-y-0.5 disabled:opacity-30 disabled:pointer-events-none disabled:grayscale">
                                <span>Complete Receipt</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
