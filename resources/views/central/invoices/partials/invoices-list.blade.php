<div class="mt-4 mb-4">
    {{ $invoices->withQueryString()->links() }}
</div>
<div class="grid grid-cols-1 gap-6">
    @forelse($invoices as $invoice)
        <div class="group relative rounded-2xl border border-border/40 bg-white/70 backdrop-blur-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden"
            :class="{ 'ring-2 ring-primary/50 bg-primary/5': selected.includes({{ $invoice->id }}) }">
            {{-- Card Header --}}
            <div class="p-5 md:p-6 border-b border-border/40 bg-gradient-to-r from-muted/20 to-transparent">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        {{-- Checkbox --}}
                        <div
                            class="flex items-center h-10 w-10 rounded-lg bg-background shadow-sm border border-border/50 justify-center">
                            <input type="checkbox" value="{{ $invoice->id }}" x-model="selected"
                                class="h-4 w-4 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-foreground tracking-tight">
                                    {{ $invoice->invoice_number }}
                                </span>
                                {{-- Status Badge --}}
                                @if($invoice->status === 'paid')
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-emerald-100 text-emerald-700 border-emerald-200">Paid</span>
                                @elseif($invoice->status === 'partial')
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-blue-100 text-blue-700 border-blue-200">Partial</span>
                                @elseif($invoice->status === 'overdue')
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-red-100 text-red-700 border-red-200">Overdue</span>
                                @elseif($invoice->status === 'cancelled')
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-gray-100 text-gray-700 border-gray-200">Cancelled</span>
                                @elseif($invoice->status === 'returned')
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-blue-100 text-blue-700 border-blue-200">Returned</span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider border bg-amber-100 text-amber-700 border-amber-200">Pending</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1.5 font-medium">
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $invoice->order->customer->name ?? $invoice->order->customer->first_name ?? 'Unknown Customer' }}
                                    @if($invoice->order->customer->email)
                                        <span class="text-[10px] opacity-70">({{ $invoice->order->customer->email }})</span>
                                    @endif
                                </div>
                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                <span>Due: {{ $invoice->due_date?->format('M d, Y') ?? '-' }}</span>
                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                <span class="text-foreground">Ord #{{ $invoice->order->order_number ?? 'Manual' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold text-foreground tracking-tight">₹
                            {{ number_format($invoice->total_amount, 2) }}</p>
                        @if($invoice->status !== 'paid' && $invoice->paid_amount > 0)
                            <p class="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider mt-1">
                                Paid: ₹ {{ number_format($invoice->paid_amount, 2) }} &bull; <span class="text-amber-600">Due: ₹
                                    {{ number_format(max(0, $invoice->total_amount - $invoice->paid_amount), 2) }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Progress Bar --}}
                @if($invoice->status !== 'paid')
                <div class="mt-6">
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1.5">
                        <span>Payment Progress</span>
                        <span>{{ number_format(($invoice->paid_amount / $invoice->total_amount) * 100, 0) }}%</span>
                    </div>
                    <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden shadow-inner">
                        <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-1000" 
                             style="width: {{ ($invoice->paid_amount / $invoice->total_amount) * 100 }}%"></div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Action Bar --}}
            <div class="px-6 py-4 bg-gray-50/50 border-t border-border/40 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('central.invoices.pdf', $invoice) }}" 
                       class="p-2 text-muted-foreground hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                       title="Download PDF">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </a>
                    
                    @if($invoice->status !== 'paid')
                    <a href="{{ route('central.invoices.show', $invoice) }}?action=pay" 
                       class="p-2 text-muted-foreground hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
                       title="Quick Payment">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </a>
                    @endif
                </div>

                <a href="{{ route('central.invoices.show', $invoice) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white text-foreground border border-border shadow-sm rounded-lg text-sm font-bold hover:bg-gray-50 transition-all">
                    View Details
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full py-20 text-center bg-background rounded-3xl border border-dashed border-border">
            <div class="w-20 h-20 mx-auto bg-muted/30 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-foreground">No invoices found</h3>
            <p class="text-muted-foreground mt-2 max-w-sm mx-auto">There are no invoices matching your current criteria. Try
                adjusting your search filters.</p>
            <a href="{{ route('central.invoices.index') }}"
                class="inline-flex items-center gap-2 mt-6 px-4 py-2 bg-background border border-border rounded-lg text-sm font-semibold text-foreground hover:bg-muted transition-colors">
                Clear Filters
            </a>
        </div>
    @endforelse
</div>
<div class="mt-8">
    {{ $invoices->withQueryString()->links() }}
</div>