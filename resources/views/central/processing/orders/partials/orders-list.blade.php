<div class="mt-2 mb-4">
    {{ $orders->withQueryString()->links() }}
</div>
<div class="flex flex-col gap-3">
    @forelse($orders as $order)
        <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-primary/20 transition-all duration-300 overflow-hidden relative">
            
            {{-- Main Row: Core Info --}}
            <div class="p-3 sm:p-4 border-b border-gray-100 bg-gradient-to-r from-white to-gray-50/30">
                <div class="flex items-center gap-4">
                    {{-- Checkbox --}}
                    <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-xl bg-white border border-gray-200 shadow-sm group-hover:border-primary/30 transition-all">
                        <input type="checkbox" value="{{ $order->id }}" data-status="{{ $order->status }}" x-model="selected" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                    </div>

                    {{-- Order Primary Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="flex items-center gap-2" x-data="{ 
                                copied: false,
                                copyText(text) {
                                    if (navigator.clipboard && window.isSecureContext) {
                                        navigator.clipboard.writeText(text);
                                    } else {
                                        const textArea = document.createElement('textarea');
                                        textArea.value = text;
                                        textArea.style.position = 'absolute';
                                        textArea.style.left = '-999999px';
                                        document.body.prepend(textArea);
                                        textArea.select();
                                        try { document.execCommand('copy'); } catch (e) {} finally { textArea.remove(); }
                                    }
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 2000);
                                }
                            }">
                                <button @click="copyText('{{ $order->order_number }}')" class="text-base font-black text-gray-900 hover:text-primary transition-colors flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold text-gray-400 font-mono">#{{ $order->id }}</span>
                                    {{ $order->order_number }}
                                </button>
                            </div>
                            
                            {{-- Small Status Badge --}}
                            @php
                                $statusConfig = [
                                    'placed' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'dot' => 'bg-gray-400'],
                                    'confirmed' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'dot' => 'bg-blue-500'],
                                    'processing' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'dot' => 'bg-purple-500'],
                                    'ready_to_ship' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'dot' => 'bg-emerald-500'],
                                    'shipped' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'dot' => 'bg-indigo-500'],
                                    'delivered' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'dot' => 'bg-green-500'],
                                    'cancelled' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'dot' => 'bg-red-500'],
                                ];
                                $config = $statusConfig[$order->status] ?? $statusConfig['placed'];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $config['bg'] }} {{ $config['text'] }} border border-black/5">
                                <span class="w-1 h-1 rounded-full {{ $config['dot'] }}"></span>
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </div>

                        {{-- Metadata Row --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-500 font-medium">
                            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>{{ $order->customer->name }}</span>
                            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>{{ $order->created_at->format('d M, h:i A') }}</span>
                            <span class="flex items-center gap-1.5 text-gray-400 font-bold uppercase text-[9px] tracking-tight bg-gray-50 px-1.5 py-0.5 rounded">{{ $order->shipping_address->district ?? 'N/A' }}</span>
                        </div>
                    </div>

                    {{-- Financial Info --}}
                    <div class="text-right flex-shrink-0">
                        <p class="text-lg font-black text-primary tracking-tight">₹{{ number_format($order->grand_total, 0) }}</p>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">{{ $order->items->count() }} Items</p>
                    </div>
                </div>
            </div>

            {{-- Compact Content Area --}}
            <div class="px-4 py-3 bg-white flex flex-col md:flex-row md:items-center justify-between gap-6">
                
                {{-- Product Thumbnails --}}
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    @foreach($order->items->take(6) as $item)
                        <div class="relative flex-shrink-0 group/item">
                            <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 p-0.5 overflow-hidden shadow-sm hover:border-primary/30 transition-all">
                                @if($item->product && $item->product->image_url)
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover rounded-lg">
                                @else
                                    <div class="h-full w-full flex items-center justify-center text-gray-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                <div class="absolute -top-1.5 -right-1.5 bg-gray-900 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md shadow ring-1 ring-white">
                                    {{ $item->quantity }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($order->items->count() > 6)
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gray-50 border border-gray-200 border-dashed flex items-center justify-center text-[10px] font-black text-gray-400">
                            +{{ $order->items->count() - 6 }}
                        </div>
                    @endif
                </div>

                {{-- Status Progress (Simplified) --}}
                <div class="flex-1 max-w-md hidden lg:block">
                    <div class="relative flex justify-between items-center px-1">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-100 rounded-full z-0"></div>
                        @php
                            $statusOrder = ['placed', 'confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered'];
                            $orderStatus = ($order->status == 'completed') ? 'delivered' : $order->status;
                            $currentIdx = array_search($orderStatus, $statusOrder);
                            $currentIdx = $currentIdx === false ? -1 : $currentIdx; 
                        @endphp
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-primary rounded-full z-0 transition-all duration-700" style="width: {{ max(0, min(100, $currentIdx * 20)) }}%"></div>

                        @foreach(['confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered'] as $idx => $step)
                            @php
                                $stepIdx = array_search($step, $statusOrder);
                                $isCompleted = $stepIdx <= $currentIdx;
                                $isCurrent = $stepIdx === $currentIdx;
                            @endphp
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-3.5 h-3.5 rounded-full border-2 bg-white transition-all duration-300 {{ $isCompleted ? 'border-primary bg-primary' : 'border-gray-200' }} {{ $isCurrent ? 'ring-4 ring-primary/10 scale-125' : '' }}"></div>
                                <span class="absolute top-5 text-[8px] font-black uppercase tracking-tighter {{ $isCompleted ? 'text-gray-900' : 'text-gray-400' }} whitespace-nowrap">{{ str_replace('_', ' ', $step) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Row Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($order->status === 'confirmed')
                        <button @click="$dispatch('open-process-modal', { orderId: '{{ $order->id }}' })" class="px-4 py-2 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-primary transition-all shadow-md active:scale-95">
                            Pack Order
                        </button>
                    @elseif($order->status === 'processing')
                        <button @click="$dispatch('open-dispatch-modal', { orderId: '{{ $order->id }}', orderNumber: '{{ $order->order_number }}', actionUrl: '{{ route('central.processing.orders.ready', $order) }}', mode: 'ready' })" class="px-4 py-2 bg-purple-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-purple-700 transition-all shadow-md active:scale-95">
                            Ready
                        </button>
                    @elseif($order->status === 'ready_to_ship')
                        <form action="{{ route('central.processing.orders.dispatch', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all shadow-md active:scale-95" onclick="return confirm('Confirm Dispatch?')">
                                Dispatch
                            </button>
                        </form>
                    @endif

                    {{-- Mini Dropdown/Tools --}}
                    <div class="flex bg-gray-50 rounded-xl p-0.5 border border-gray-100">
                        <a href="{{ route('central.orders.invoice', $order) }}" class="p-1.5 text-gray-400 hover:text-primary transition-colors" title="Invoice">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </a>
                        <a href="{{ route('central.orders.receipt', $order) }}" class="p-1.5 text-gray-400 hover:text-orange-500 transition-colors border-l border-gray-200" title="Receipt">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Compact Shipment Strip (Only if exists) --}}
            @if($order->shipments->isNotEmpty())
                <div class="px-4 py-1.5 border-t border-gray-50 bg-blue-50/20 flex items-center gap-3">
                    <span class="text-[9px] font-black text-blue-400 uppercase tracking-tighter">Shipment:</span>
                    @foreach($order->shipments as $shipment)
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-blue-900/60">{{ $shipment->carrier }}</span>
                            <span class="text-[10px] font-mono font-black text-blue-900 select-all cursor-pointer">{{ $shipment->tracking_number }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="py-12 text-center bg-white rounded-2xl border border-dashed border-gray-200">
            <h3 class="text-sm font-bold text-gray-900">No orders found</h3>
            <p class="text-[11px] text-gray-500 mt-1">Try adjusting your filters.</p>
        </div>
    @endforelse
</div>
<div class="mt-6">
    {{ $orders->withQueryString()->links() }}
</div>
