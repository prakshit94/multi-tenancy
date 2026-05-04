@extends('layouts.app')

@section('content')
<div x-data="{ 
    selected: [], 
    search: '{{ request('search') }}',
    id_search: '{{ request('id_search') }}',
    activeStatus: '{{ request('status', 'confirmed') }}',

    // ✅ FIX: scope checkboxes to current content only
    get allIds() { 
        return Array.from(document.querySelectorAll(`#orders-content input[type='checkbox'][data-status]`))
            .map(el => el.value); 
    },

    statusFlow: ['placed', 'confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered'],

    isStatusValid(targetStatus) {
        if (this.selected.length === 0) return false;

        const selectedStatuses = this.selected.map(id => {
            const checkbox = document.querySelector(`#orders-content input[type='checkbox'][value='${id}']`);
            return checkbox ? checkbox.getAttribute('data-status') : null;
        }).filter(s => s !== null);

        if (selectedStatuses.length === 0) return false;

        const normalizedStatuses = selectedStatuses.map(s => 
            s === 'completed' ? 'delivered' : s
        );

        const uniqueStatuses = [...new Set(normalizedStatuses)];
        if (uniqueStatuses.length > 1) return false;

        const currentStatus = uniqueStatuses[0];
        const currentIndex = this.statusFlow.indexOf(currentStatus);
        const targetIndex = this.statusFlow.indexOf(targetStatus);

        return targetIndex > currentIndex;
    },

    loadData(url) {
        const urlObj = new URL(url);
        urlObj.searchParams.set('ajax', '1');

        fetch(urlObj.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('orders-content').innerHTML = html;
            window.history.pushState({}, '', url);
            this.selected = [];
            
            // Re-sync state from URL
            const finalUrl = new URL(url, window.location.origin);
            const newStatus = finalUrl.searchParams.get('status') || 'confirmed';

            this.activeStatus = newStatus;
            this.search = finalUrl.searchParams.get('search') || '';
            this.id_search = finalUrl.searchParams.get('id_search') || '';

            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error loading orders:', error);
        });
    },

    performFilter() {
        const url = new URL(window.location.origin + window.location.pathname);
        
        url.searchParams.set('status', this.activeStatus);
        if (this.search) {
            url.searchParams.set('search', this.search);
        }
        if (this.id_search) {
            url.searchParams.set('id_search', this.id_search);
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
    },

    resetFilters() {
        this.search = '';
        this.id_search = '';
        
        // Reset filter-form
        const filterForm = document.getElementById('filter-form');
        if (filterForm) {
            filterForm.reset();
            // Manually clear hidden inputs because reset() doesn't clear them
            filterForm.querySelectorAll('input[type=hidden]').forEach(i => {
                if (!['per_page', '_token', 'status'].includes(i.name)) {
                    i.value = '';
                }
            });
        }

        // Reset date filter state in the URL if present
        const url = new URL(window.location.origin + window.location.pathname);
        url.searchParams.set('status', this.activeStatus);
        this.loadData(url.toString());
    }
}" @click="if ($event.target.closest('nav a')) { $event.preventDefault(); loadData($event.target.closest('nav a').href); }" @pagination-click.window="loadData($event.detail.url)" @refresh-orders.window="loadData(window.location.href)" class="flex flex-1 flex-col space-y-6 p-6 md:p-8 max-w-[1600px] mx-auto w-full">

        <!-- Header Area -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-2">
    <div class="space-y-1">
        <h1
            class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Order Processing
        </h1>
    </div>
</div>
<div id="orders-content">
    @include('central.processing.orders.partials.orders-content')
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

    <!-- ✅ SMART BULK IMPORT MODAL (UPDATED) -->
<div 
x-data="{
    open: false,
    previewRows: [],
    loading: false,

    async previewCSV(event) {
        const file = event.target.files[0];
        if (!file) return;

        let formData = new FormData();
        formData.append('csv_file', file);

        this.loading = true;

        try {
            const res = await fetch('/processing/orders/bulk-preview', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: formData
            });

            const data = await res.json();
            this.previewRows = data.rows;

        } catch (e) {
            console.error(e);
            alert('Preview failed');
        }

        this.loading = false;
    },

    async confirmImport() {
        if (!confirm('Proceed with import?')) return;

        try {
            const res = await fetch('/processing/orders/bulk-process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ rows: this.previewRows })
            });

            const data = await res.json();

            if (data.success) {
                alert('Bulk operation completed');
                this.open = false;
                this.previewRows = [];
                window.dispatchEvent(new CustomEvent('refresh-orders'));
            } else {
                alert(data.message || 'Error occurred');
            }

        } catch (e) {
            console.error(e);
            alert('Processing failed');
        }
    }
}"
x-on:open-import-modal.window="open = true"
x-show="open"
class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
style="display: none;"
>

    <div @click.away="open = false"
        class="bg-white border border-gray-100 shadow-2xl rounded-2xl w-full max-w-4xl p-8 space-y-6 animate-in fade-in zoom-in duration-200 relative overflow-hidden">

        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 to-teal-500"></div>

       <!-- Header -->
<div class="flex justify-between items-start">
    <div class="space-y-1">
        <h3 class="text-xl font-bold text-gray-900">Smart Bulk Import</h3>
        <p class="text-sm text-gray-500">
            Upload CSV to Dispatch or Deliver orders (Preview before confirm)
        </p>
    </div>

    <!-- ✅ NEW: Download Template Button -->
    <a href="{{ route('central.processing.orders.download-template') }}"
       class="text-xs font-bold text-primary hover:underline whitespace-nowrap">
        Download Template
    </a>
</div>

        <!-- File Input -->
        <input 
            type="file" 
            accept=".csv"
            @change="previewCSV($event)"
            class="flex w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">

        <!-- Loading -->
        <div x-show="loading" class="text-sm text-gray-500 font-semibold">
            Processing CSV...
        </div>

        <!-- Preview Table -->
        <template x-if="previewRows.length > 0">
            <div class="overflow-auto border rounded-xl max-h-[400px]">
                <table class="w-full text-xs">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="p-2 text-left">Order</th>
                            <th class="p-2 text-center">Action</th>
                            <th class="p-2 text-center">Current</th>
                            <th class="p-2 text-center">Status</th>
                            <th class="p-2 text-left">Message</th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="row in previewRows" :key="row.order_number">
                            <tr :class="row.status === 'error' ? 'bg-red-50' : 'bg-green-50'">
                                <td class="p-2 font-mono" x-text="row.order_number"></td>
                                <td class="p-2 text-center font-semibold" x-text="row.action"></td>
                                <td class="p-2 text-center" x-text="row.current_status"></td>
                                <td class="p-2 text-center font-bold" x-text="row.status"></td>
                                <td class="p-2 text-xs" x-text="row.message"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        <!-- Footer -->
        <div class="flex justify-end gap-3 pt-2">
            <button 
                type="button" 
                @click="open = false"
                class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">
                Cancel
            </button>

            <button 
                type="button"
                x-show="previewRows.length > 0"
                @click="confirmImport()"
                class="px-5 py-2.5 text-sm font-semibold bg-gray-900 text-white hover:bg-black rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5">
                Confirm Import
            </button>
        </div>
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
</div>
@endsection