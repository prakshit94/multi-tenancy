<div class="space-y-4">
    @forelse($returns as $return)
        <div class="group bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm hover:shadow-md transition-all overflow-hidden">
            <div class="flex flex-col lg:flex-row">
                <!-- Left: Return Info -->
                <div class="flex-1 p-6 border-b lg:border-b-0 lg:border-r border-gray-50 dark:border-zinc-800/50">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-gray-50 dark:bg-zinc-800 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">RMA Number</span>
                                <h4 class="text-base font-black text-gray-900 dark:text-white leading-none mt-0.5">{{ $return->rma_number }}</h4>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider border 
                            {{ $return->status === 'approved' ? 'bg-blue-50 text-blue-600 border-blue-200' : '' }}
                            {{ $return->status === 'received' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : '' }}">
                            <span class="w-1 h-1 rounded-full {{ $return->status === 'approved' ? 'bg-blue-600' : 'bg-emerald-600' }}"></span>
                            {{ $return->status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-6">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Customer</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $return->order->customer->first_name }} {{ $return->order->customer->last_name }}</p>
                            <p class="text-xs text-gray-500">Order #{{ $return->order->order_number }}</p>
                            @if($return->order->shipments->isNotEmpty())
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-zinc-800 text-[10px] font-bold rounded uppercase tracking-tight text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-zinc-700">{{ $return->order->shipments->first()->carrier }}</span>
                                    <span class="text-[10px] font-mono text-gray-400">{{ $return->order->shipments->first()->tracking_number }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Requested On</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $return->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $return->created_at->format('H:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Items & Action -->
                <div class="flex-1 flex flex-col p-6 bg-gray-50/50 dark:bg-zinc-800/10">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Items to Receive</p>
                    <div class="flex-1 space-y-3">
                        @foreach($return->items as $item)
                            <div class="flex items-center gap-3 p-2 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                                <div class="h-10 w-10 rounded-lg bg-gray-50 dark:bg-zinc-800 overflow-hidden flex-shrink-0 border border-gray-100 dark:border-zinc-700">
                                    @if($item->product && $item->product->image_url)
                                        <img src="{{ $item->product->image_url }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-gray-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m21 8-9-4-9 4m18 8-9 4-9-4m18-4-9 4-9-4"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $item->product->name }}</p>
                                    <p class="text-[10px] text-gray-500 leading-none mt-0.5">Qty: {{ $item->quantity }} • <span class="capitalize">{{ $item->condition }}</span></p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        @if($return->status === 'approved')
                            <button type="button"
                                @click="inspect({{ $return->toJson() }})"
                                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-bold rounded-xl hover:bg-black dark:hover:bg-gray-100 transition-all shadow-lg shadow-gray-900/10 dark:shadow-none hover:-translate-y-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                Inspect & Receive Items
                            </button>
                        @else
                            <div class="w-full py-3 px-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-xl flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Inventory Updated</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="py-20 text-center bg-white dark:bg-zinc-900 rounded-3xl border border-dashed border-gray-200 dark:border-zinc-800">
            <div class="w-20 h-20 bg-gray-50 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">No returns found</h3>
            <p class="text-sm text-gray-500 max-w-xs mx-auto mt-1">There are currently no return requests matching the selected status.</p>
        </div>
    @endforelse

    <!-- Pagination -->
    <div class="mt-6 ajax-pagination">
        {{ $returns->links() }}
    </div>
</div>
