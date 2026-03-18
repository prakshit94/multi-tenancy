<x-app-layout>
    <div class="py-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 animate-in fade-in duration-700">
        <!-- Breadcrumbs / Back -->
        <div class="mb-8 flex items-center justify-between">
            <a href="{{ route('central.returns.show', $orderReturn) }}" 
               class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to RMA Details
            </a>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-sm">
                Status: {{ ucfirst($orderReturn->status) }}
            </span>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50 overflow-hidden">
            <div class="p-8 border-b border-gray-50 bg-gradient-to-br from-gray-50 to-white">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-3 bg-purple-600 rounded-2xl shadow-lg shadow-purple-600/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 leading-tight">Process Refund</h2>
                        <p class="text-sm text-gray-500 font-medium">Issue credit to <span class="text-gray-900 font-bold underline decoration-purple-500/30">{{ $orderReturn->order->customer->name }}</span></p>
                    </div>
                </div>
            </div>

            <form action="{{ route('central.returns.refund.store', $orderReturn) }}" method="POST" class="p-8 space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Payment Status Card -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-2 opacity-5">
                                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                </svg>
                            </div>
                            
                            <div class="flex items-center justify-between mb-6">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Customer Payment Portfolio</p>
                                @php
                                    $latestInvoice = $orderReturn->order->invoices->last();
                                    $paidAmount = (float)($latestInvoice->paid_amount ?? 0);
                                    $totalAmount = (float)($latestInvoice->total_amount ?? $orderReturn->order->grand_total);
                                    $isPaid = $latestInvoice ? $paidAmount >= $totalAmount : false;
                                    $isUnpaid = $latestInvoice ? $paidAmount <= 0 : true;
                                    $isPartial = !$isPaid && !$isUnpaid;
                                @endphp
                                
                                @if($latestInvoice)
                                    @if($isPaid)
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black bg-emerald-500 text-white border border-emerald-600 shadow-sm">FULLY PAID</span>
                                    @elseif($isPartial)
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black bg-amber-500 text-white border border-amber-600 shadow-sm">PARTIALLY PAID</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black bg-red-500 text-white border border-red-600 shadow-sm">UNPAID</span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[9px] font-black bg-gray-500 text-white border border-gray-600 shadow-sm">NO INVOICE</span>
                                @endif
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center group">
                                    <span class="text-xs text-gray-500 font-bold group-hover:text-gray-700 transition-colors">Invoice Total:</span>
                                    <span class="text-sm font-black text-gray-900">₹{{ number_format($totalAmount, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center group">
                                    <span class="text-xs text-gray-500 font-bold group-hover:text-gray-700 transition-colors">Amount Received:</span>
                                    <span class="text-sm font-black text-emerald-600">₹{{ number_format($paidAmount, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-4 border-t border-gray-200/50 group">
                                    <span class="text-xs text-gray-500 font-bold group-hover:text-gray-700 transition-colors">Current Debt:</span>
                                    <span class="text-sm font-black text-red-600">₹{{ number_format($totalAmount - $paidAmount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Refund & Recommendation Calculation -->
                        <div class="bg-indigo-50/30 rounded-2xl p-6 border border-indigo-100/50">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Smart Recommendation</p>
                            @php
                                $returnItemsValue = 0;
                                $calculationDetails = [];
                                foreach($orderReturn->items as $item) {
                                    $orderItem = $orderReturn->order->items->where('product_id', $item->product_id)->first();
                                    $itemPrice = $orderItem ? $orderItem->unit_price : ($item->product->price ?? 0);
                                    $calcQty = ($item->quantity_received > 0) ? $item->quantity_received : $item->quantity;
                                    $itemTotal = $calcQty * $itemPrice;
                                    $returnItemsValue += $itemTotal;
                                    $calculationDetails[] = [
                                        'name' => $item->product->name,
                                        'qty' => $calcQty,
                                        'price' => $itemPrice,
                                        'total' => $itemTotal
                                    ];
                                }

                                // Apply conditional suggested amount
                                if ($isPaid) {
                                    $suggestedAmount = $returnItemsValue;
                                    $suggestionType = 'full';
                                } elseif ($isUnpaid) {
                                    $suggestedAmount = 0;
                                    $suggestionType = 'zero';
                                } else {
                                    // Partial - suggestion is capped at paid amount
                                    $suggestedAmount = min($returnItemsValue, $paidAmount);
                                    $suggestionType = ($suggestedAmount < $returnItemsValue) ? 'capped' : 'partial_full';
                                }
                            @endphp
                            
                            <div class="space-y-2.5 mb-5 max-h-32 overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($calculationDetails as $detail)
                                    <div class="flex justify-between text-[11px] text-indigo-600/80 font-bold italic">
                                        <span>{{ (int)$detail['qty'] }}x {{ Str::limit($detail['name'], 24) }}</span>
                                        <span>₹{{ number_format($detail['total'], 2) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="bg-white/60 rounded-xl p-4 border border-indigo-100 shadow-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-black text-indigo-900/50 uppercase">Safe Suggestion:</span>
                                    <span class="text-xl font-black text-indigo-900 animate-in zoom-in duration-500">₹{{ number_format($suggestedAmount, 2) }}</span>
                                </div>
                                <p class="text-[9px] text-indigo-500 font-bold italic leading-tight">
                                    @if($suggestionType === 'zero')
                                        Refund set to ₹0 because the invoice is unpaid.
                                    @elseif($suggestionType === 'capped')
                                        Capped at ₹{{ number_format($paidAmount, 2) }} (actual amount paid by customer).
                                    @elseif($suggestionType === 'partial_full')
                                        Items value within the partially paid amount.
                                    @else
                                        Full value of returned items suggested.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Section -->
                    <div class="space-y-8">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase text-gray-400 tracking-wider flex items-center gap-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Final Refund Total (₹)
                            </label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-black text-2xl group-focus-within:text-purple-600 transition-colors">₹</span>
                                <input type="number" name="refunded_amount" step="0.01" value="{{ old('refunded_amount', round($suggestedAmount, 2)) }}"
                                    class="block w-full pl-10 pr-6 py-5 bg-gray-50 border-gray-100 rounded-2xl text-2xl font-black text-gray-900 focus:bg-white focus:ring-4 focus:ring-purple-600/10 focus:border-purple-600 transition-all shadow-inner focus:shadow-md"
                                    placeholder="0.00" required>
                            </div>
                            
                            @if($isUnpaid || $isPartial)
                                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/50 flex gap-4 shadow-sm animate-in slide-in-from-right-4">
                                    <div class="p-2 bg-amber-500 rounded-lg shrink-0 h-fit">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black text-amber-900 mb-0.5 uppercase tracking-tight">Active Balance Management</p>
                                        <p class="text-[10px] text-amber-700 font-bold leading-relaxed">
                                            @if($isUnpaid)
                                                Refund is restricted to **₹0.00** because no payments have been received. Processing this will reduce the customer's debt.
                                            @else
                                                Suggested refund is capped at **₹{{ number_format($paidAmount, 2) }}**. Any amount beyond this will further reduce their outstanding debt.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/50 flex gap-4 shadow-sm animate-in slide-in-from-right-4">
                                    <div class="p-2 bg-emerald-500 rounded-lg shrink-0 h-fit">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black text-emerald-900 mb-0.5 uppercase tracking-tight">Payment Verified</p>
                                        <p class="text-[10px] text-emerald-700 font-bold leading-relaxed">The customer has fully paid for this order. This refund will be credited back to their account profile.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Internal Notes</label>
                            <textarea name="notes" rows="3" 
                                class="block w-full px-4 py-4 bg-gray-50 border-gray-100 rounded-2xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition-all shadow-inner resize-none"
                                placeholder="Details about this refund event...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-gray-50 flex items-center justify-end gap-4">
                    <button type="submit" 
                            class="group relative inline-flex items-center gap-3 px-10 py-4 bg-purple-600 text-white text-sm font-black rounded-2xl shadow-xl shadow-purple-600/30 hover:bg-purple-700 hover:shadow-purple-700/40 transition-all hover:-translate-y-1">
                        Confirm & Issue Refund
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>