<div class="mt-4 mb-4">
    {{ $invoices->withQueryString()->links() }}
</div>
<div class="grid grid-cols-1 gap-6">
    @forelse($invoices as $inv)
        <div class="group relative rounded-2xl border border-border/40 bg-white/70 backdrop-blur-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden"
            :class="{ 'ring-2 ring-indigo-500/50 bg-indigo-50/10': selected.includes({{ $inv->id }}) }">
            {{-- Card Header --}}
            <div class="p-5 md:p-6 border-b border-border/40 bg-gradient-to-r from-gray-50/50 to-transparent">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        {{-- Checkbox --}}
                        <div
                            class="flex items-center h-10 w-10 rounded-lg bg-white shadow-sm border border-border/50 justify-center">
                            <input type="checkbox" value="{{ $inv->id }}" x-model="selected"
                                class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-gray-900 tracking-tight">
                                    {{ $inv->invoice_number }}
                                </span>
                                {{-- Status Badge --}}
                                @if($inv->status === 'paid')
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-green-50 text-green-700 border-green-200">Paid</span>
                                @elseif($inv->status === 'partial')
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-blue-50 text-blue-700 border-blue-200">Partial</span>
                                @elseif($inv->status === 'overdue')
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-red-50 text-red-700 border-red-200">Overdue</span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-amber-50 text-amber-700 border-amber-200">Pending</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1.5 font-medium">
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $inv->order->customer->name ?? $inv->order->customer->first_name ?? 'Unknown Customer' }}
                                </div>
                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                <span>Issued: {{ $inv->issue_date->format('M d, Y') }}</span>
                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                <span class="text-foreground">Ord #{{ $inv->order->order_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold text-foreground tracking-tight">
                            ${{ number_format($inv->total_amount, 2) }}</p>
                        @if($inv->status !== 'paid' && $inv->paid_amount > 0)
                            <p class="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider mt-1">
                                Paid: ${{ number_format($inv->paid_amount, 2) }} &bull; <span class="text-amber-600">Due:
                                    ${{ number_format(max(0, $inv->total_amount - $inv->paid_amount), 2) }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Bar --}}
            <div class="px-6 py-4 bg-gray-50/50 border-t border-border/40 flex justify-end">
                <a href="{{ route('tenant.invoices.show', $inv) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-200 shadow-sm rounded-lg text-sm font-semibold hover:bg-gray-50 hover:text-indigo-600 transition-all">
                    View Invoice
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200">
            <div class="w-20 h-20 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">No invoices found</h3>
            <p class="text-gray-500 mt-2 max-w-sm mx-auto">There are no invoices matching your current criteria. Try
                adjusting your search filters.</p>
            <a href="{{ route('tenant.invoices.index') }}"
                class="inline-flex items-center gap-2 mt-6 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Clear Filters
            </a>
        </div>
    @endforelse
</div>
<div class="mt-8">
    {{ $invoices->withQueryString()->links() }}
</div>