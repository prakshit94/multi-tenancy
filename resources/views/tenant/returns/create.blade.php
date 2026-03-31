<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl leading-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    {{ __('New Return Request') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Select an order and choose items to return.</p>
            </div>
            <a href="{{ route('tenant.returns.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Returns
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 animate-in fade-in duration-500" x-data="rmaForm()">
        
        <!-- Tabs Section -->
        <div class="flex items-center gap-1.5 mb-8 bg-gray-100/80 backdrop-blur-md p-1.5 rounded-2xl w-fit border border-gray-200/50 shadow-inner">
            <button @click="mode = 'single'" type="button"
                    :class="mode === 'single' ? 'bg-white text-gray-900 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50'"
                    class="px-5 sm:px-8 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                Manual Entry
            </button>
            <button @click="mode = 'bulk'" type="button"
                    :class="mode === 'bulk' ? 'bg-white text-gray-900 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50'"
                    class="px-5 sm:px-8 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all duration-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Bulk Upload
            </button>
        </div>

        <form action="{{ route('tenant.returns.store') }}" method="POST" class="space-y-8" x-show="mode === 'single'">
            @csrf

            <!-- 1. Select Order -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">1</span>
                        Select Order
                    </h3>
                </div>
                <div class="p-4 sm:p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose an Order</label>
                    <div class="relative">
                        <select name="order_id" x-model="selectedOrder" @change="loadItems()" 
                                class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 pl-4 pr-10 bg-gray-50 hover:bg-white transition-colors cursor-pointer appearance-none truncate" required>
                            <option value="">Select an order...</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" 
                                    data-items="{{ json_encode($order->items) }}"
                                    {{ (isset($preSelectedOrderId) && $preSelectedOrderId == $order->id) ? 'selected' : '' }}>
                                    Order #{{ $order->order_number }} - {{ $order->customer->first_name ?? 'Guest' }} ({{ $order->created_at->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Only orders from the last 30 days are shown.</p>
                </div>
            </div>

            <!-- 2. Select Items -->
            <div x-show="selectedOrder" x-transition.opacity.duration.300ms class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative">
                <div class="p-4 sm:p-6 border-b border-gray-50 bg-gray-50/50 flex flex-wrap gap-2 justify-between items-center">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">2</span>
                        Select Items
                    </h3>
                    <div x-show="orderItems.length > 0" class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">
                        <span x-text="orderItems.filter(i => i.selected).length"></span> items selected
                    </div>
                </div>

                <div class="p-0">
                    <!-- Items List -->
                    <div class="divide-y divide-gray-100">
                        <template x-for="(item, index) in orderItems" :key="index">
                            <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors" :class="item.selected ? 'bg-indigo-50/30' : ''">
                                <div class="flex flex-col sm:flex-row items-start gap-4">
                                    <div class="flex items-start gap-4 w-full sm:w-auto">
                                        <!-- Checkbox -->
                                        <div class="pt-1">
                                            <input type="checkbox" x-model="item.selected" value="1" :disabled="item.available_qty <= 0"
                                                   class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                        </div>

                                        <!-- Image -->
                                        <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-xl bg-white border border-gray-200 overflow-hidden flex-shrink-0">
                                            <template x-if="item.product && item.product.image_url">
                                                <img :src="item.product.image_url" class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!item.product || !item.product.image_url">
                                                <div class="h-full w-full flex items-center justify-center text-gray-300 bg-gray-100">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                                </div>
                                            </template>
                                        </div>
                                        
                                        <!-- Mobile Details (Visible only on small screens if needed, but here we keep layout fluid) -->
                                        <div class="sm:hidden flex-1">
                                             <h4 class="text-sm font-bold text-gray-900 line-clamp-2" x-text="item.product_name || item.sku"></h4>
                                             <p class="text-xs text-gray-500 mt-0.5" x-text="'SKU: ' + (item.sku || 'N/A')"></p>
                                             <p class="text-xs font-medium mt-1" :class="item.available_qty > 0 ? 'text-gray-700' : 'text-red-500'" x-text="item.available_qty > 0 ? 'Ordered: ' + item.formatted_quantity + ' (Avail: ' + item.available_qty + ')' : 'Fully Returned'"></p>
                                        </div>
                                    </div>

                                    <!-- Details & Controls -->
                                    <div class="flex-1 w-full sm:min-w-0 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="hidden sm:block">
                                            <h4 class="text-sm font-bold text-gray-900" x-text="item.product_name || item.sku"></h4>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="'SKU: ' + (item.sku || 'N/A')"></p>
                                            <p class="text-xs font-medium mt-2" :class="item.available_qty > 0 ? 'text-gray-700' : 'text-red-500'" x-text="item.available_qty > 0 ? 'Ordered: ' + item.formatted_quantity + ' (Avail: ' + item.available_qty + ')' : 'Fully Returned'"></p>
                                        </div>

                                        <!-- Controls (Only visible if selected) -->
                                        <div x-show="item.selected" x-transition class="flex flex-row gap-3 items-center justify-between sm:justify-end w-full bg-gray-50/50 sm:bg-transparent p-3 sm:p-0 rounded-lg sm:rounded-none">
                                            <!-- Hidden Inputs -->
                                            <!-- Removed name attribute to prevent submission of all items -->
                                            
                                            <div class="flex-1 sm:flex-none sm:w-auto">
                                                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Return Qty</label>
                                                <input type="number" x-model="item.return_qty" min="1" :max="item.available_qty" 
                                                       class="block w-full sm:w-24 rounded-lg border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center font-bold">
                                            </div>

                                            <div class="flex-1 sm:flex-none sm:w-auto">
                                                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Condition</label>
                                                <select x-model="item.condition" 
                                                        class="block w-full sm:w-32 rounded-lg border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                    
                    <div x-show="orderItems.length === 0" class="p-8 text-center text-gray-500">
                        Select an order to view items.
                    </div>
                </div>
            </div>

            <!-- 3. Finalize -->
            <div x-show="hasSelectedItems" x-transition class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs">3</span>
                        Reason & Submit
                    </h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Why is the customer returning these items?</label>
                        <textarea name="reason" rows="3" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4 placeholder-gray-400 resize-none" placeholder="e.g. Items were damaged in transit, ordered wrong size..." required></textarea>
                    </div>

                    <!-- Hidden Inputs for Selected Items (Sequential Indices) -->
                    <template x-for="(item, i) in orderItems.filter(x => x.selected)" :key="item.id">
                        <div>
                            <input type="hidden" :name="'items['+i+'][product_id]'" :value="item.product_id">
                            <input type="hidden" :name="'items['+i+'][quantity]'" :value="item.return_qty">
                            <input type="hidden" :name="'items['+i+'][condition]'" :value="item.condition">
                        </div>
                    </template>

                    <div class="flex items-center justify-end pt-2">
                         <button type="submit" 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-gray-900 py-3.5 px-8 text-sm font-bold text-white shadow-lg shadow-gray-900/20 hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition-all hover:-translate-y-0.5 transform">
                            <span>Submit Return Request</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </div>
                </div>
            </div>

        </form>

        <!-- BULK MODE CONTAINER -->
        <div x-show="mode === 'bulk'" x-transition class="space-y-8" style="display: none;">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-50 bg-gradient-to-br from-indigo-50 to-transparent flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-600/20 rotate-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Bulk Return Upload</h3>
                            <p class="text-xs sm:text-sm text-gray-500 font-medium mt-0.5 opacity-80">Upload a CSV to process multiple returns at once.</p>
                        </div>
                    </div>
                    <a href="data:text/csv;charset=utf-8,order_id,reason%0AORD-1001,Defective product%0AORD-1002,Not what I expected" 
                       download="returns_template.csv"
                       class="px-4 py-2 sm:px-6 sm:py-3 bg-white text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm flex-shrink-0 text-center">
                        Download CSV
                    </a>
                </div>
                <div class="p-6 sm:p-12">
                    <!-- CSV Upload Stage -->
                    <div x-show="bulkPreviewRows.length === 0">
                        <div class="w-full max-w-xl mx-auto p-12 sm:p-16 border-4 border-dashed border-gray-100 rounded-[2rem] group hover:border-indigo-400 hover:bg-indigo-50/30 transition-all duration-300 text-center relative cursor-pointer"
                             @click="$refs.csvInput.click()">
                            <input type="file" name="csv_file" x-ref="csvInput" class="hidden" accept=".csv" 
                                   @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''; uploadPreview()">
                            <div class="mb-6 flex justify-center">
                                <div class="p-6 sm:p-8 bg-gray-50 rounded-2xl text-gray-400 group-hover:bg-indigo-600 group-hover:text-white group-hover:-translate-y-2 transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:shadow-indigo-600/30">
                                    <template x-if="!isUploading">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    </template>
                                    <template x-if="isUploading">
                                        <svg class="animate-spin h-9 w-9" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </template>
                                </div>
                            </div>
                            <span class="text-base sm:text-lg font-bold text-gray-900 block" x-text="isUploading ? 'Analyzing CSV...' : (fileName ? 'Selected: ' + fileName : 'Select or drop CSV here')"></span>
                            <span class="text-[10px] text-gray-400 mt-3 block font-bold uppercase tracking-wider">CSV format only</span>
                            <span class="text-xs text-indigo-500 font-medium mt-2 block group-hover:underline">Click to browse files</span>
                        </div>
                    </div>

                    <!-- Preview & Confirmation Stage -->
                    <div x-show="bulkPreviewRows.length > 0" x-transition style="display: none;">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4 border-b border-gray-100 pb-6">
                            <div>
                                <h4 class="text-xl sm:text-2xl font-bold text-gray-900" x-text="'Preview Batch (' + bulkPreviewRows.length + ' requests)'"></h4>
                                <p class="text-xs sm:text-sm text-gray-500 mt-1">Review the validation status before creating bulk returns.</p>
                            </div>
                            <button type="button" @click="bulkPreviewRows = []; fileName = ''" class="text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl transition-all border border-red-100 w-full sm:w-auto text-center flex-shrink-0">Upload Different File</button>
                        </div>

                        <div class="overflow-x-auto border border-gray-100 rounded-2xl mb-8 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-100">
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

                        <form action="{{ route('tenant.returns.bulk-upload') }}" method="POST">
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

                            <div class="p-6 sm:p-8 bg-gray-50/80 border border-gray-100 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
                                <div class="flex items-center gap-6 sm:gap-10">
                                    <div class="text-center bg-white p-3 rounded-xl border border-gray-100 shadow-sm min-w-[100px]">
                                        <span class="block text-2xl font-black text-emerald-600" x-text="validBulkRows.length"></span>
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1 block">Valid</span>
                                    </div>
                                    <div class="text-center bg-white p-3 rounded-xl border border-gray-100 shadow-sm min-w-[100px]" x-show="invalidBulkCount > 0">
                                        <span class="block text-2xl font-black text-red-600" x-text="invalidBulkCount"></span>
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1 block">Invalid</span>
                                    </div>
                                </div>
                                
                                <button type="submit" 
                                        :disabled="validBulkRows.length === 0"
                                        class="w-full md:w-auto inline-flex items-center justify-center gap-3 px-8 sm:px-10 py-3.5 sm:py-4 bg-gray-900 text-white text-sm sm:text-base font-bold rounded-xl shadow-lg shadow-gray-900/20 hover:bg-black disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:-translate-y-px">
                                    <span>Create <span x-text="validBulkRows.length"></span> RMAs</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
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
            const initialItems = @json($preSelectedOrder ? $preSelectedOrder->items : []);
            // Normalize initial items if any
            const normalizedInitial = initialItems.map(item => {
                const available = item.available_quantity !== undefined ? parseFloat(item.available_quantity) : parseFloat(item.quantity);
                return {
                    ...item,
                    selected: false,
                    available_qty: available,
                    return_qty: available > 0 ? 1 : 0,
                    condition: 'sellable'
                };
            });

            return {
                mode: 'single',
                selectedOrder: '{{ $preSelectedOrderId ?? "" }}',
                orderItems: normalizedInitial,

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
                        const response = await fetch('{{ route('tenant.returns.bulk-preview') }}', {
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
                    if (this.selectedOrder && this.orderItems.length === 0) {
                        this.loadItems();
                    }
                },

                loadItems() {
                    const select = document.querySelector('select[name="order_id"]');
                    if (!select) return;
                    const option = select.options[select.selectedIndex];
                    
                    if (option && option.dataset.items) {
                        const items = JSON.parse(option.dataset.items);
                        // Map items to include local state
                        this.orderItems = items.map(item => {
                            const available = item.available_quantity !== undefined ? parseFloat(item.available_quantity) : parseFloat(item.quantity);
                            return {
                                ...item,
                                selected: false,
                                available_qty: available,
                                return_qty: available > 0 ? 1 : 0, 
                                condition: 'sellable',
                                formatted_quantity: parseFloat(item.quantity)
                            };
                        });
                    } else {
                        this.orderItems = [];
                    }
                },

                get hasSelectedItems() {
                    return this.orderItems && this.orderItems.some(i => i.selected);
                }
            }
        }
    </script>
</x-app-layout>
