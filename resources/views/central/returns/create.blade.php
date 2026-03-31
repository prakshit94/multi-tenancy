<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2
                    class="font-extrabold text-3xl leading-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    {{ __('Process Return') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Search for an order to initiate a return request.</p>
            </div>
            <a href="{{ route('central.returns.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back to Returns
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 animate-in fade-in duration-500" x-data="rmaForm()">
        <!-- Tabs Section -->
        <div class="flex items-center gap-1.5 mb-8 bg-gray-100/80 backdrop-blur-md p-1.5 rounded-2xl w-fit border border-gray-200/50 shadow-inner">
            <button @click="mode = 'single'" 
                    :class="mode === 'single' ? 'bg-white text-gray-900 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50'"
                    class="px-8 py-2.5 rounded-xl text-sm font-black transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                Manual Entry
            </button>
            <button @click="mode = 'bulk'" 
                    :class="mode === 'bulk' ? 'bg-white text-gray-900 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50'"
                    class="px-8 py-2.5 rounded-xl text-sm font-black transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Bulk Upload
            </button>
        </div>

        <!-- Search Section -->
        <div x-show="mode === 'single'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
             class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-visible relative z-30 mb-8">
            <div class="p-8 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-xl font-black text-gray-900 flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-xl bg-gray-900 text-white text-sm shadow-lg shadow-gray-900/20">1</span>
                    Find Order
                </h3>
            </div>
            <div class="p-8">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="searchOrders()"
                        @focus="showResults = true" @click.away="showResults = false"
                        placeholder="Search by Order ID, Customer Name, or Phone..."
                        class="block w-full rounded-2xl border-gray-200 pl-14 pr-4 py-4 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 sm:text-base bg-gray-50 focus:bg-white transition-all shadow-sm font-medium">

                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center" x-show="loading"
                        style="display: none;">
                        <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>

                    <!-- Dropdown Results -->
                    <div x-show="showResults && results.length > 0"
                        class="absolute z-50 mt-3 w-full bg-white shadow-2xl rounded-3xl border border-gray-100 max-h-96 overflow-auto divide-y divide-gray-50 p-2"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="transform opacity-100 scale-100 translate-y-0" style="display: none;">

                        <template x-for="order in results" :key="order.id">
                            <div @click="selectOrder(order)"
                                class="p-4 hover:bg-indigo-50/50 cursor-pointer transition-all group rounded-2xl relative">
                                <div class="flex justify-between items-center gap-4">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-indigo-600 group-hover:text-white group-hover:rotate-6 transition-all duration-300 shrink-0 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                                <path d="M3 6h18" />
                                                <path d="M16 10a4 4 0 0 1-8 0" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-base font-black text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                                                <span x-text="order.order_number"></span>
                                                <span class="text-xs font-medium text-gray-400 ml-1" x-text="'#' + order.id"></span>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1 truncate font-bold uppercase tracking-wider">
                                                <span x-text="order.customer_name"></span> &bull; <span
                                                    x-text="order.placed_at"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-base font-black text-gray-900"
                                            x-text="'₹' + Number(order.grand_total).toFixed(2)"></p>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mt-1.5 border shadow-sm"
                                            :class="{
                                                  'bg-emerald-50 text-emerald-700 border-emerald-100': order.status === 'completed' || order.status === 'delivered',
                                                  'bg-indigo-50 text-indigo-700 border-indigo-100': order.status === 'processing',
                                                  'bg-amber-50 text-amber-700 border-amber-100': order.status === 'pending' || order.status === 'placed',
                                                  'bg-gray-50 text-gray-700 border-gray-100': true
                                              }" x-text="order.status.replace('_', ' ')">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    <!-- 2. Select Items & 3. Finalize (Manual Mode) -->
    <div x-show="mode === 'single' && selectedOrder" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-8 mt-8">
        
        <form action="{{ route('central.returns.store') }}" method="POST" @submit="confirmSubmission" class="space-y-8">
            @csrf
            <input type="hidden" name="order_id" :value="selectedOrder?.id">

            <!-- Items Selection Card -->
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl overflow-hidden">
                <div class="p-8 border-b border-gray-50 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-indigo-600/20">2</div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900 tracking-tight">Select Return Items</h3>
                            <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mt-0.5 opacity-60">Order: <span class="text-indigo-600" x-text="selectedOrder?.order_number"></span></p>
                        </div>
                    </div>
                    <button type="button" @click="resetSelection()"
                        class="px-6 py-2.5 rounded-xl text-sm font-black text-red-500 hover:bg-red-50 transition-all border border-red-100/50">
                        Change Order
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-8">
                    <template x-for="(item, index) in orderItems" :key="item.id || index">
                        <div class="group relative bg-white rounded-[2rem] border transition-all duration-500 overflow-hidden shadow-sm hover:shadow-2xl h-full flex flex-col"
                            :class="item.selected ? 'border-indigo-500 ring-8 ring-indigo-500/10' : 'border-gray-100 hover:border-gray-200'">

                            <!-- Selection Badge -->
                            <div class="absolute top-4 left-4 z-10">
                                <input type="checkbox" x-model="item.selected" :disabled="item.available_qty <= 0"
                                    class="h-7 w-7 rounded-xl border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm transition-all disabled:opacity-30">
                            </div>

                            <div class="p-5 flex gap-5 h-full">
                                <!-- Image -->
                                <div class="h-24 w-24 rounded-3xl bg-gray-50 border border-gray-100 overflow-hidden flex-shrink-0 relative group-hover:scale-105 transition-transform duration-500">
                                    <template x-if="item.product && item.product.image_url">
                                        <img :src="item.product.image_url" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!item.product || !item.product.image_url">
                                        <div class="h-full w-full flex items-center justify-center text-gray-200">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    </template>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-base font-black text-gray-900 line-clamp-2 leading-tight" x-text="item.product_name || item.sku"></h4>
                                        <div class="flex flex-wrap items-center gap-2 mt-2">
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter" x-text="'SKU: ' + (item.sku || 'N/A')"></span>
                                            <span class="text-[10px] font-black px-2 py-1 rounded-lg"
                                                :class="item.available_qty > 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-red-50 text-red-600'"
                                                x-text="item.available_qty > 0 ? 'Ordered: ' + item.formatted_quantity + ' (Avail: ' + item.available_qty + ')' : 'Fully Returned'"></span>
                                        </div>
                                    </div>

                                    <div x-show="item.selected" x-transition class="mt-4 grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-1 px-1 tracking-tighter">Qty</label>
                                            <input type="number" x-model.number="item.return_qty" :min="1" :max="item.available_qty"
                                                class="block w-full h-11 bg-gray-50 border-none rounded-xl text-sm font-black text-center focus:ring-4 focus:ring-indigo-500/20 focus:bg-white transition-all shadow-inner">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-1 px-1 tracking-tighter">Status</label>
                                            <select x-model="item.condition" class="block w-full h-11 bg-gray-50 border-none rounded-xl text-[10px] font-black focus:ring-4 focus:ring-indigo-500/20 focus:bg-white transition-all shadow-inner uppercase tracking-tighter">
                                                <option value="sellable">Sellable</option>
                                                <option value="damaged">Damaged</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Finalize Card -->
            <div x-show="hasSelectedItems" x-transition class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl overflow-hidden">
                <div class="p-8 border-b border-gray-50 bg-gray-50/50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gray-900 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-gray-900/20 rotate-12">3</div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Finalize Request</h3>
                        <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mt-0.5 opacity-60">Complete the RMA details</p>
                    </div>
                </div>
                <div class="p-8">
                    <div class="mb-8">
                        <label class="block text-sm font-black text-gray-900 uppercase tracking-widest mb-3 opacity-60 px-1">Reason for Return</label>
                        <textarea name="reason" rows="3" required
                            class="block w-full rounded-[1.5rem] border-gray-100 bg-gray-50/50 shadow-inner focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 sm:text-sm p-6 placeholder-gray-400 resize-none font-medium transition-all"
                            placeholder="Please provide a detailed reason..."></textarea>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit"
                            class="group relative inline-flex items-center justify-center gap-3 bg-gray-900 text-white px-12 py-5 rounded-2xl text-lg font-black shadow-2xl shadow-gray-900/40 hover:bg-black hover:-translate-y-1 active:scale-95 transition-all">
                            <span>Process Total Return</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hidden Sync Loop -->
            <template x-for="(item, i) in orderItems.filter(x => x.selected)" :key="item.id || index">
                <div>
                    <input type="hidden" :name="'items['+i+'][product_id]'" :value="item.product_id">
                    <input type="hidden" :name="'items['+i+'][quantity]'" :value="item.return_qty">
                    <input type="hidden" :name="'items['+i+'][condition]'" :value="item.condition">
                </div>
            </template>
        </form>
    </div>

    <!-- BULK MODE CONTAINER -->
    <div x-show="mode === 'bulk'" x-transition class="space-y-8 mt-8">
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl overflow-hidden">
            <div class="p-8 border-b border-gray-50 bg-gradient-to-br from-amber-50 to-transparent flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-amber-600 text-white rounded-2xl shadow-xl shadow-amber-600/20 rotate-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Bulk Return Engine</h3>
                        <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mt-0.5 opacity-60">Process hundreds of returns at once</p>
                    </div>
                </div>
                <a href="data:text/csv;charset=utf-8,order_id,reason%0AORD-1001,Defective product%0AORD-1002,Not what I expected" 
                   download="returns_template.csv"
                   class="px-6 py-3 bg-white text-amber-700 text-xs font-black rounded-xl border border-amber-200 hover:bg-amber-50 transition-all shadow-sm">
                    Download CSV Template
                </a>
            </div>
            <div class="p-12">
                <!-- CSV Upload Stage -->
                <div x-show="bulkPreviewRows.length === 0">
                    <div class="w-full max-w-xl mx-auto p-16 border-4 border-dashed border-gray-100 rounded-[3rem] group hover:border-amber-400 hover:bg-amber-50/30 transition-all duration-500 text-center relative cursor-pointer"
                         @click="$refs.csvInput.click()">
                        <input type="file" name="csv_file" x-ref="csvInput" class="hidden" accept=".csv" 
                               @change="fileName = $event.target.files[0].name; uploadPreview()">
                        <div class="mb-6 flex justify-center">
                            <div class="p-8 bg-gray-50 rounded-3xl text-gray-400 group-hover:bg-amber-600 group-hover:text-white group-hover:scale-110 transition-all duration-500 shadow-sm group-hover:shadow-xl group-hover:shadow-amber-600/30">
                                <template x-if="!isUploading">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </template>
                                <template x-if="isUploading">
                                    <svg class="animate-spin h-12 w-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </template>
                            </div>
                        </div>
                        <span class="text-xl font-black text-gray-900 block" x-text="isUploading ? 'Analyzing Batch...' : (fileName ? 'Selected: ' + fileName : 'Upload Batch CSV File')"></span>
                        <span class="text-[10px] text-gray-400 mt-4 block font-black uppercase tracking-[0.2em] opacity-60">CSV Format Only | Max 4MB</span>
                    </div>
                </div>

                <!-- Preview & Confirmation Stage -->
                <div x-show="bulkPreviewRows.length > 0" x-transition>
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h4 class="text-2xl font-black text-gray-900" x-text="'Preview Batch (' + bulkPreviewRows.length + ' rows)'"></h4>
                            <p class="text-sm text-gray-500 font-bold uppercase mt-1">Review validation results before final submission</p>
                        </div>
                        <button @click="bulkPreviewRows = []; fileName = ''" class="text-xs font-black text-red-600 hover:text-red-700 bg-red-50 px-4 py-2 rounded-xl transition-all">Clear & Start Over</button>
                    </div>

                    <div class="overflow-hidden border border-gray-100 rounded-3xl mb-8">
                        <table class="min-w-full divide-y divide-gray-50">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">SKU / Item</th>
                                    <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <template x-for="(row, idx) in bulkPreviewRows" :key="idx">
                                    <tr :class="{
                                        'bg-red-50/20': row.preview_status === 'error',
                                        'hover:bg-gray-50/50 transition-colors': row.preview_status !== 'error',
                                        'border-t-4 border-gray-200/60': idx > 0 && bulkPreviewRows[idx-1].order_id !== row.order_id
                                    }">
                                        <td class="px-5 py-4 whitespace-nowrap align-top">
                                            <div x-show="idx === 0 || bulkPreviewRows[idx-1].order_id !== row.order_id" 
                                                 class="text-sm font-black text-gray-900 bg-gray-100/80 inline-flex px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm" 
                                                 x-text="(row.order_number || row.order_id) ? '#' + (row.order_number || row.order_id) : 'Unknown'"></div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap max-w-[200px] truncate">
                                            <div class="text-sm font-semibold text-gray-900 truncate" x-text="row.product_name || row.sku"></div>
                                            <div class="text-[10px] font-medium text-gray-500 uppercase" x-text="row.sku"></div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900" x-text="row.quantity"></div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap max-w-[150px] truncate">
                                            <div class="text-xs font-medium text-gray-700 capitalize truncate" x-text="row.reason || 'No specific reason'"></div>
                                            <div class="text-[10px] font-medium text-gray-500 capitalize" x-text="row.condition || 'Sellable'"></div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide border shadow-sm"
                                                  :class="row.preview_status === 'valid' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100'">
                                                <span x-text="row.preview_message"></span>
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                        <form action="{{ route('central.returns.bulk-upload') }}" method="POST">
                            @csrf
                            <template x-for="(row, idx) in validBulkRows" :key="'valid-' + idx">
                                <div>
                                    <input type="hidden" :name="'rows['+idx+'][order_id]'" :value="row.order_id">
                                    <input type="hidden" :name="'rows['+idx+'][product_id]'" :value="row.product_id">
                                    <input type="hidden" :name="'rows['+idx+'][quantity]'" :value="row.quantity">
                                    <input type="hidden" :name="'rows['+idx+'][condition]'" :value="row.condition">
                                    <input type="hidden" :name="'rows['+idx+'][reason]'" :value="row.reason">
                                    <input type="hidden" :name="'rows['+idx+'][preview_status]'" :value="row.preview_status">
                                </div>
                            </template>

                        <div class="p-8 bg-gray-50 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <span class="block text-2xl font-black text-emerald-600" x-text="validBulkRows.length"></span>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Valid Rows</span>
                                </div>
                                <div class="text-center" x-show="invalidBulkCount > 0">
                                    <span class="block text-2xl font-black text-red-600" x-text="invalidBulkCount"></span>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Errors Skip</span>
                                </div>
                            </div>
                            
                            <button type="submit" 
                                    :disabled="validBulkRows.length === 0"
                                    class="w-full md:w-auto inline-flex items-center justify-center gap-4 px-12 py-5 bg-gray-900 text-white text-lg font-black rounded-2xl shadow-2xl shadow-gray-900/40 hover:bg-black disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                                <span>Confirm & Create <span x-text="validBulkRows.length"></span> RMAs</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
        function rmaForm() {
            return {
                mode: 'single',
                searchQuery: '',
                loading: false,
                results: [],
                showResults: false,
                selectedOrder: null,
                orderItems: [],
                fileName: '',
                isUploading: false,
                bulkPreviewRows: [],

                async uploadPreview() {
                    const fileInput = this.$refs.csvInput;
                    if (!fileInput.files || fileInput.files.length === 0) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', message: 'Please select a CSV file first.' } }));
                        return;
                    }

                    this.isUploading = true;
                    this.bulkPreviewRows = [];
                    const formData = new FormData();
                    formData.append('csv_file', fileInput.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const response = await fetch('{{ route('central.returns.bulk-preview') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        
                        // Parse JSON safely
                        let result;
                        try {
                            result = await response.json();
                        } catch (e) {
                            throw new Error('Server returned invalid JSON. It might be an error page.');
                        }

                        if (response.ok && result.success) {
                            this.bulkPreviewRows = result.rows || [];
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'CSV preview loaded successfully.' } }));
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: result.message || 'Failed to parse CSV due to an error.' } }));
                        }
                    } catch (error) {
                        console.error('Preview error:', error);
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Error uploading file for preview. Please check console for details.' } }));
                    } finally {
                        this.isUploading = false;
                        fileInput.value = ''; // Reset input to allow same file re-upload
                    }
                },

                get validBulkRows() {
                    return this.bulkPreviewRows.filter(r => r.preview_status === 'valid');
                },

                get invalidBulkCount() {
                    return this.bulkPreviewRows.filter(r => r.preview_status === 'error').length;
                },

                init() {
                    const preSelectedOrder = @json($preSelectedOrder);
                    if (preSelectedOrder) {
                        this.selectOrder(preSelectedOrder);
                    }

                    window.addEventListener('set-mode', (e) => {
                        this.mode = e.detail;
                    });
                },

                async searchOrders() {
                    if (this.searchQuery.length < 1) {
                        this.results = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch(`{{ route('central.api.search.all-orders') }}?q=${encodeURIComponent(this.searchQuery)}`);
                        if (response.ok) {
                            this.results = await response.json();
                            this.showResults = true;
                        }
                    } catch (error) {
                        console.error('Search failed:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                selectOrder(order) {
                    this.selectedOrder = order;
                    this.orderItems = (order.items || []).map(item => {
                        const available = item.available_quantity !== undefined ? parseFloat(item.available_quantity) : parseFloat(item.quantity);
                        return {
                            ...item,
                            selected: false,
                            available_qty: available,
                            return_qty: available > 0 ? available : 0,
                            condition: 'sellable',
                            product_name: item.product?.name || item.product_name || 'Unknown Item',
                            sku: item.product?.sku || item.sku || 'N/A',
                            formatted_quantity: parseFloat(item.quantity)
                        };
                    });

                    this.showResults = false;
                    this.searchQuery = order.order_number;
                },

                resetSelection() {
                    this.selectedOrder = null;
                    this.orderItems = [];
                    this.searchQuery = '';
                },

                get hasSelectedItems() {
                    return this.orderItems && this.orderItems.length > 0 && this.orderItems.some(item => item.selected);
                },

                confirmSubmission(e) {
                    if (!confirm('Are you sure you want to process this return?')) {
                        e.preventDefault();
                    }
                }
            }
        }
    </script>
</x-app-layout>