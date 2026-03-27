@extends('layouts.app')

@section('content')
    <div x-data="{ 
        selected: [], 
        search: '{{ request('search') }}',
        activeStatus: '{{ request('status', 'confirmed') }}',
        get allIds() { return Array.from(document.querySelectorAll(`input[type='checkbox'][data-status]`)).map(el => el.value); },
        statusFlow: ['placed', 'confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered'],
        isStatusValid(targetStatus) {
            if (this.selected.length === 0) return false;

            const selectedStatuses = this.selected.map(id => {
                const checkbox = document.querySelector(`input[type='checkbox'][value='${id}']`);
                return checkbox ? checkbox.getAttribute('data-status') : null;
            }).filter(s => s !== null);

            if (selectedStatuses.length === 0) return false;

            if (targetStatus === 'cancelled') {
                return selectedStatuses.every(current => current !== 'delivered' && current !== 'cancelled');
            }

            const targetIndex = this.statusFlow.indexOf(targetStatus);
            if (targetIndex === -1) return false;

            return selectedStatuses.some(current => {
                let normalizedCurrent = current === 'completed' ? 'delivered' : current;
                const currentIndex = this.statusFlow.indexOf(normalizedCurrent);
                if (currentIndex === -1) return false; 
                return targetIndex > currentIndex;
            });
        },
        async loadData(urlStr) {
            try {
                const url = new URL(urlStr, window.location.origin);
                const pushUrl = new URL(urlStr, window.location.origin);
                pushUrl.searchParams.delete('ajax');
                
                url.searchParams.set('ajax', '1');
                
                const response = await fetch(url.toString(), { 
                    headers: { 'X-Requested-With': 'XMLHttpRequest' } 
                });
                
                if (response.ok) { 
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('orders-content') || doc.body;

                    const container = document.getElementById('orders-content');
                    if (container && newContent) {
                        container.innerHTML = newContent.id === 'orders-content' ? newContent.innerHTML : html;
                        
                        if (window.Alpine) {
                            window.Alpine.initTree(container);
                        }
                    }
                    
                    window.history.pushState({}, '', pushUrl.toString());
                    
                    const finalUrl = new URL(window.location.href);
                    this.activeStatus = finalUrl.searchParams.get('status') || 'confirmed';
                    this.search = finalUrl.searchParams.get('search') || '';

                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (error) { console.error('Request Failed:', error); }
        },
        performFilter() {
            const url = new URL(window.location.origin + window.location.pathname);
            
            url.searchParams.set('status', this.activeStatus);
            if (this.search) {
                url.searchParams.set('search', this.search);
            }
            
            const filterForm = document.getElementById('filter-form');
            if (filterForm) {
                const formData = new FormData(filterForm);
                for (let [key, value] of formData.entries()) {
                    if (key === 'status' || key === 'search' || key === 'page' || key === 'ajax') continue;
                    if (value) {
                        url.searchParams.set(key, value);
                    }
                }
            }
            
            this.loadData(url.toString());
        }
    }"
    @click="if ($event.target.closest('nav a')) { $event.preventDefault(); loadData($event.target.closest('nav a').href); }"
    @pagination-click.window="loadData($event.detail.url)"
    @refresh-orders.window="loadData(window.location.href)"
    class="flex flex-1 flex-col space-y-6 p-6 md:p-8 max-w-[1600px] mx-auto w-full">

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-2">
            <div class="space-y-1">
                <h1
                    class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    Order Processing
                </h1>
            </div>

        </div>

        <!-- Smart Search & Results Export (Fixed to preserve typing focus) -->
        <div class="bg-white border border-gray-100/50 rounded-[40px] p-4 mb-8 shadow-sm ring-1 ring-black/5 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 max-w-2xl relative group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                </div>
                <input type="text" name="search" x-model="search" @input.debounce.300ms="performFilter()"
                    placeholder="Scan order ID, reference, customer name or phone..."
                    class="w-full h-12 pl-12 pr-6 bg-white border border-gray-100 rounded-3xl text-sm font-bold focus:ring-4 focus:ring-primary/10 transition-all shadow-sm outline-none placeholder:text-gray-400">
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('central.orders.export') }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" :value="activeStatus">
                    <input type="hidden" name="search" :value="search">
                    <input type="hidden" name="ids" :value="selected.join(',')">
                    <button type="submit"
                        class="inline-flex items-center gap-2.5 h-12 px-6 rounded-3xl bg-primary text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-primary/20 hover:bg-black transition-all transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export Orders
                    </button>
                </form>
            </div>
        </div>

        <div id="orders-content">
            @include('central.processing.orders.partials.orders-content')
        </div>
    </div>

    <!-- Dispatch / Ready Modal -->
    <div x-data="{ open: false, orderId: '', orderNumber: '', actionUrl: '', modalMode: '', courier: 'India Post', trackingNumber: '' }"
        x-on:open-dispatch-modal.window="open = true; orderId = $event.detail.orderId; orderNumber = $event.detail.orderNumber; actionUrl = $event.detail.actionUrl; modalMode = $event.detail.mode || 'dispatch'; courier = 'India Post'; trackingNumber = '';"
        x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        style="display: none;">

        <div @click.away="open = false"
            class="bg-white border border-gray-100 shadow-2xl rounded-2xl w-full max-w-md p-8 space-y-6 animate-in fade-in zoom-in duration-200 relative overflow-hidden">

            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 to-teal-500"></div>

            <div class="space-y-1">
                <h3 class="text-xl font-bold text-gray-900"
                    x-text="modalMode === 'ready' ? 'Ready to Ship' : 'Dispatch Order'"></h3>
                <p class="text-sm text-gray-500">
                    Enter courier details for
                    <span x-text="orderNumber" class="font-mono font-semibold text-gray-900"></span>
                </p>
            </div>

            <form x-bind:action="actionUrl || ('/processing/orders/' + orderId + '/dispatch')" method="POST"
                class="space-y-5">
                @csrf

                <div class="space-y-4" x-show="modalMode === 'ready'">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Courier Service</label>
                        <select name="courier" x-model="courier" :required="modalMode === 'ready'"
                            class="flex h-11 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            <option value="India Post">India Post</option>
                            <option value="DTDC">DTDC</option>
                            <option value="Blue Dart">Blue Dart</option>
                            <option value="Delhivery">Delhivery</option>
                            <option value="Ecom Express">Ecom Express</option>
                            <option value="XpressBees">XpressBees</option>
                            <option value="Amazon Shipping">Amazon Shipping</option>
                            <option value="FedEx">FedEx</option>
                            <option value="DHL">DHL</option>
                            <option value="Vehicle">Vehicle</option>
                            <option value="LMD">LMD</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 block" x-text="courier === 'Vehicle' ? 'Vehicle Number' : (courier === 'LMD' ? 'Reference Number' : 'Tracking Number')"></label>
                        <input type="text" name="tracking_number" x-model="trackingNumber" :required="modalMode === 'ready'"
                            :placeholder="courier === 'Vehicle' ? 'Enter Vehicle Number' : (courier === 'LMD' ? 'Enter Reference' : 'Tracking ID')"
                            class="flex h-11 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500/20 transition-all">
                    </div>
                </div>
                
                <div x-show="modalMode === 'dispatch'" class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-indigo-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm font-medium text-indigo-900 leading-relaxed">Ensure the package has been physically handed over to the courier.</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-gray-900 text-white hover:bg-black rounded-xl shadow-lg shadow-gray-900/20 transition-all transform hover:-translate-y-0.5"
                        x-text="modalMode === 'ready' ? 'Confirm Details' : 'Confirm Dispatch'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Dispatch CSV Modal -->
    <div x-data="{ open: false }" x-on:open-import-modal.window="open = true" x-show="open"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        style="display: none;">

        <div @click.away="open = false"
            class="bg-white border border-gray-100 shadow-2xl rounded-2xl w-full max-w-md p-8 space-y-6 animate-in fade-in zoom-in duration-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
            <div class="space-y-1">
                <h3 class="text-xl font-bold text-gray-900">Bulk Dispatch</h3>
                <p class="text-sm text-gray-500">Upload a CSV file to dispatch multiple orders.</p>
            </div>
            <form action="{{ route('central.processing.orders.bulk-dispatch') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="file" name="csv_file" accept=".csv, .txt" required class="flex w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-gray-900 text-white hover:bg-black rounded-xl shadow-lg shadow-gray-900/20 transition-all transform hover:-translate-y-0.5">Upload & Dispatch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Premium Processed Order Modal (AJAX) -->
    <div x-data="{ 
            open: false, 
            order: null,
            processOrder(orderId) {
                fetch(`/processing/orders/${orderId}/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                })
                .then(async response => {
                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.order = data.order;
                        this.open = true;
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: { type: 'error', message: data.message || 'Error processing order' }
                        }));
                    }
                })
                .catch(error => { console.error('Error:', error); });
            },
            closeModal() {
                this.open = false;
                this.$dispatch('refresh-orders'); 
            }
        }"
        @open-process-modal.window="processOrder($event.detail.orderId)">
        <div x-show="open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white shadow-2xl rounded-3xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-300 relative border border-white/20">
                <div class="p-8 space-y-8 max-h-[85vh] overflow-y-auto custom-scrollbar" @click.outside="closeModal()">
                    <div class="text-center space-y-3">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-tr from-green-400 to-emerald-600 text-white mb-2 shadow-lg shadow-green-500/30">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h2 class="text-3xl font-black text-gray-900">Order Processed!</h2>
                        <p class="text-gray-500">Order <span class="font-mono font-bold text-gray-900" x-text="'#' + (order ? order.order_number : '')"></span> is ready for packing.</p>
                    </div>
                    <div class="pt-4">
                        <button @click="closeModal()" class="w-full py-3.5 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-base shadow-lg shadow-gray-900/20 transition-all duration-200">Done</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection