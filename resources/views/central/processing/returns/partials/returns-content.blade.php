<div class="space-y-4">
    <div class="rounded-3xl border border-gray-100 bg-white/80 backdrop-blur-xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/50 text-gray-500 font-semibold border-b border-gray-100 uppercase tracking-wider text-xs">
                    <tr>
                        <th class="px-8 py-5">RMA #</th>
                        <th class="px-8 py-5">Order #</th>
                        <th class="px-8 py-5">Customer</th>
                        <th class="px-8 py-5">Status</th>
                        <th class="px-8 py-5">Items</th>
                        <th class="px-8 py-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($returns as $return)
                        <tr class="group hover:bg-gray-50/60 transition-colors duration-200">
                            <td class="px-8 py-5">
                                <div class="font-mono font-bold text-gray-900">{{ $return->rma_number }}</div>
                                <div class="text-[10px] uppercase font-bold tracking-wide text-gray-400 mt-0.5">
                                    {{ $return->created_at->format('M d, H:i') }}
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="font-mono text-gray-900 font-bold">{{ $return->order->order_number }}</div>
                                @if($return->order->shipments->isNotEmpty())
                                    <div class="mt-1 flex flex-col gap-0.5">
                                        <div class="text-[10px] uppercase font-black text-indigo-600 tracking-tight">{{ $return->order->shipments->first()->carrier }}</div>
                                        <div class="text-[10px] font-mono text-gray-400">{{ $return->order->shipments->first()->tracking_number }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-indigo-700 font-bold text-xs ring-2 ring-white shadow-sm">
                                        {{ substr($return->order->customer->first_name, 0, 1) }}{{ substr($return->order->customer->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $return->order->customer->first_name }} {{ $return->order->customer->last_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $return->order->customer->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold border shadow-sm
                                    {{ $return->status === 'approved' ? 'bg-blue-50 text-blue-700 border-blue-200 shadow-blue-100' : '' }}
                                    {{ $return->status === 'received' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-emerald-100' : '' }}
                                    {{ $return->status === 'requested' ? 'bg-amber-50 text-amber-700 border-amber-200 shadow-amber-100' : '' }}
                                    {{ $return->status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200 shadow-red-100' : '' }}
                                ">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $return->status === 'approved' ? 'bg-blue-500' : ($return->status === 'received' ? 'bg-emerald-500' : ($return->status === 'requested' ? 'bg-amber-500' : 'bg-red-500')) }}"></span>
                                    {{ ucfirst($return->status) }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1.5">
                                    @foreach($return->items as $item)
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="font-bold text-gray-900">{{ $item->quantity }}x</span>
                                            <span class="text-gray-600 truncate max-w-[150px]" title="{{ $item->product->name }}">{{ $item->product->name }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500 border border-gray-200">{{ $item->condition }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                @if($return->status === 'approved')
                                    <button @click="$dispatch('open-receive-modal', { 
                                        id: {{ $return->id }}, 
                                        rma: '{{ $return->rma_number }}', 
                                        items: {{ $return->items->map(fn($i) => ['id' => $i->id, 'name' => $i->product->name, 'sku' => $i->product->sku, 'quantity' => $i->quantity, 'condition' => $i->condition, 'image' => $i->product->image_url ?? null]) }} 
                                    })"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-black hover:scale-105 active:scale-95 transition-all shadow-lg shadow-gray-900/20 group-hover:shadow-gray-900/30">
                                        <span>Receive</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" /><path d="m9 11 3 3L22 4" /></svg>
                                        Processed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /><path d="M16 13H8" /><path d="M16 17H8" /><path d="M10 9H8" /></svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">No Returns Found</h3>
                                    <p class="text-sm text-gray-500 max-w-xs mx-auto">There are no return requests matching the current criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 bg-gray-50/30 ajax-pagination">
            {{ $returns->links() }}
        </div>
    </div>
</div>
