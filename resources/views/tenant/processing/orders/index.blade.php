@extends('layouts.app')

@section('content')
    <div x-data="{ 
        selected: [], 
        get allIds() { return Array.from(document.querySelectorAll(`input[type='checkbox'][data-status]`)).map(el => el.value); },
        statusFlow: ['placed', 'confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered'],
        isStatusValid(targetStatus) {
            if (this.selected.length === 0) return false;
            
            // Get statuses of selected orders from DOM to ensure they are up-to-date (even after AJAX)
            // We use document.querySelector because the checkboxes are likely inside the current component or its children
            // but for safety/simplicity we'll query by value.
            
            const selectedStatuses = this.selected.map(id => {
                const checkbox = document.querySelector(`input[type='checkbox'][value='${id}']`);
                return checkbox ? checkbox.getAttribute('data-status') : null;
            }).filter(s => s !== null);

            if (selectedStatuses.length === 0) return false;

            // Handle cancellation logic
            if (targetStatus === 'cancelled') {
                 // Can cancel if not delivered or already cancelled
                 return selectedStatuses.every(current => current !== 'delivered' && current !== 'cancelled');
            }

            const targetIndex = this.statusFlow.indexOf(targetStatus);
            if (targetIndex === -1) return false;

            // Forward transition: Target must be strictly greater than current for ALL selected
            return selectedStatuses.every(current => {
                const currentIndex = this.statusFlow.indexOf(current);
                
                // If current status is unknown or invalid for flow, block
                if (currentIndex === -1) return false; 
                
                return targetIndex > currentIndex;
            });
        },
        async loadData(urlStr) {
            try {
                const url = new URL(urlStr, window.location.origin);
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
                        // Use innerHTML from matched element if possible, else the whole response
                        container.innerHTML = newContent.id === 'orders-content' ? newContent.innerHTML : html;
                        
                        // Re-initialize Alpine logic for new DOM elements
                        if (window.Alpine) {
                            window.Alpine.initTree(container);
                        }
                    }
                    
                    // Push clean URL to state (strip ajax=1 from address bar)
                    const pushUrl = new URL(urlStr, window.location.origin);
                    pushUrl.searchParams.delete('ajax');
                    window.history.pushState({}, '', pushUrl.toString());
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (error) { console.error('Request Failed:', error); }
        }
    }"
    @click="if ($event.target.closest('nav a')) { $event.preventDefault(); loadData($event.target.closest('nav a').href); }"
    @pagination-click.window="loadData($event.detail.url)"
    @refresh-orders.window="loadData(window.location.href)"
    class="flex flex-1 flex-col space-y-6 p-6 md:p-8 max-w-[1600px] mx-auto w-full animate-in fade-in duration-500">

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-2">
            <div class="space-y-1">
                <h1
                    class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    Order Processing
                </h1>
                <p class="text-muted-foreground text-sm font-medium">Manage fulfillment, track shipments, and handle
                    returns.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <a href="{{ route('tenant.processing.returns.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border/60 bg-background/50 hover:bg-background hover:border-border text-sm font-semibold transition-all shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    Returns
                </a>
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
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-semibold shadow-lg shadow-gray-900/10 hover:shadow-gray-900/20 hover:bg-black transition-all transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export Orders
                    </button>
                </form>
            </div>
        </div>

        <div id="orders-content">
            @include('tenant.processing.orders.partials.orders-content')
        </div>
    </div>

    <!-- Dispatch Modal -->
    <div x-data="{ open: false, orderId: '', orderNumber: '' }"
        x-on:open-dispatch-modal.window="open = true; orderId = $event.detail.orderId; orderNumber = $event.detail.orderNumber"
        x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        style="display: none;">

        <div @click.away="open = false"
            class="bg-white border border-gray-100 shadow-2xl rounded-2xl w-full max-w-md p-8 space-y-6 animate-in fade-in zoom-in duration-200 relative overflow-hidden">

            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 to-teal-500"></div>

            <div class="space-y-1">
                <h3 class="text-xl font-bold text-gray-900">Dispatch Order</h3>
                <p class="text-sm text-gray-500">Enter courier details for <span x-text="orderNumber"
                        class="font-mono font-semibold text-gray-900"></span></p>
            </div>

            <form x-bind:action="'/processing/orders/' + orderId + '/dispatch'" method="POST" class="space-y-5">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Courier
                            Service</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="courier" required placeholder="e.g. DHL, FedEx, Local"
                                class="flex h-11 w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 text-sm focus:border-emerald-500 focus:ring-emerald-500/20 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">Tracking
                            Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                            </div>
                            <input type="text" name="tracking_number" required placeholder="Tracking ID"
                                class="flex h-11 w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 text-sm focus:border-emerald-500 focus:ring-emerald-500/20 transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold bg-gray-900 text-white hover:bg-black rounded-xl shadow-lg shadow-gray-900/20 transition-all transform hover:-translate-y-0.5">
                        Confirm Dispatch
                    </button>
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
                <p class="text-xs text-muted-foreground mt-1">Required columns: <code
                        class="bg-gray-100 px-1 py-0.5 rounded">order_number</code>, <code
                        class="bg-gray-100 px-1 py-0.5 rounded">courier</code>, <code
                        class="bg-gray-100 px-1 py-0.5 rounded">tracking_number</code></p>
            </div>

            <form action="{{ route('tenant.processing.orders.bulk-dispatch') }}" method="POST" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 block">CSV
                            File</label>
                        <input type="file" name="csv_file" accept=".csv, .txt" required
                            class="flex w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold bg-gray-900 text-white hover:bg-black rounded-xl shadow-lg shadow-gray-900/20 transition-all transform hover:-translate-y-0.5">
                        Upload & Dispatch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal (Reused) -->


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
                                    detail: {
                                        type: 'error',
                                        message: data.message || 'Error processing order'
                                    }
                                }));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: {
                                    type: 'error',
                                    message: 'An error occurred while processing the order'
                                }
                            }));
                        });
                    },
                    closeModal() {
                        this.open = false;
                        this.$dispatch('refresh-orders'); 
                    }
                }" @open-process-modal.window="processOrder($event.detail.orderId)">

        <div x-show="open" style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md animate-in fade-in duration-300">
            <div
                class="bg-white shadow-2xl rounded-3xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-300 relative border border-white/20">

                <!-- Decorative Background -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 via-purple-50/50 to-pink-50/50 -z-10">
                </div>

                <div class="p-8 space-y-8 max-h-[85vh] overflow-y-auto custom-scrollbar" @click.outside="closeModal()">
                    <div class="text-center space-y-3">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-tr from-green-400 to-emerald-600 text-white mb-2 shadow-lg shadow-green-500/30">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-black tracking-tight text-gray-900">Order Processed!</h2>
                        <p class="text-gray-500 text-lg">Order <span class="font-mono font-bold text-gray-900"
                                x-text="'#' + (order ? order.order_number : '')"></span> is ready for packing.</p>
                    </div>

                    <div
                        class="bg-white/60 backdrop-blur-sm rounded-2xl p-5 border border-gray-200/60 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Customer</p>
                                <p class="font-bold text-gray-900"
                                    x-text="order ? (order.customer.first_name + ' ' + order.customer.last_name) : ''"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Destination</p>
                            <p class="font-bold text-gray-900"
                                x-text="order?.shipping_address?.city || 'Local Pickup / N/A'"></p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="font-bold text-sm text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            Items to Pack
                        </h3>
                        <div class="max-h-[200px] overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                            <template x-for="item in order?.items" :key="item.id">
                                <div
                                    class="flex items-center gap-4 p-3 bg-white border border-gray-100 rounded-2xl shadow-sm">
                                    <div
                                        class="w-14 h-14 rounded-xl bg-gray-50 flex-shrink-0 overflow-hidden border border-gray-100">
                                        <img :src="item.product.image_url" :alt="item.product_name"
                                            class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-900 truncate" x-text="item.product_name"></p>
                                        <p class="text-xs text-gray-500 font-medium" x-text="'SKU: ' + item.sku"></p>
                                    </div>
                                    <div class="text-right px-2">
                                        <span class="block text-xl font-black text-indigo-600"
                                            x-text="item.quantity"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button @click="closeModal()"
                            class="w-full py-3.5 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-base shadow-lg shadow-gray-900/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection