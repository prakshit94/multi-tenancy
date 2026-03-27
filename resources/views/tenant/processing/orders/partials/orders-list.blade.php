<div class="mt-4 mb-4">
    {{ $orders->withQueryString()->links() }}
</div>
<div class="grid grid-cols-1 gap-6">
    @forelse($orders as $order)
        <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-primary/20 transition-all duration-300 overflow-hidden relative">
            <!-- Status Accent Line -->
            <div class="absolute left-0 top-0 bottom-0 w-1 
                {{ $order->status === 'placed' ? 'bg-gray-300' :
                   ($order->status === 'confirmed' ? 'bg-blue-500' :
                   ($order->status === 'processing' ? 'bg-purple-500' :
                   ($order->status === 'ready_to_ship' ? 'bg-emerald-500' :
                   ($order->status === 'shipped' ? 'bg-indigo-500' :
                   ($order->status === 'delivered' ? 'bg-green-500' :
                   ($order->status === 'cancelled' ? 'bg-red-500' : 'bg-gray-300')))))) }}">
            </div>

            {{-- Card Header --}}
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-white to-gray-50/50">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div class="flex items-start gap-5">
                        {{-- Checkbox --}}
                        <div class="mt-1 flex items-center h-11 w-11 rounded-xl bg-white shadow-sm border border-gray-200 justify-center group-hover:border-primary/30 transition-all">
                            <input type="checkbox" value="{{ $order->id }}" data-status="{{ $order->status }}" x-model="selected" class="h-5 w-5 rounded-md border-gray-300 text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                        </div>
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="relative" x-data="{ 
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
                                            try {
                                                document.execCommand('copy');
                                            } catch (error) {
                                                console.error(error);
                                            } finally {
                                                textArea.remove();
                                            }
                                        }
                                        this.copied = true;
                                        setTimeout(() => this.copied = false, 2000);
                                    }
                                }">
                                    <button @click="copyText('{{ $order->order_number }}')" class="text-xl font-black text-gray-900 tracking-tight hover:text-primary transition-colors flex items-center gap-2 group/copy" title="Click to copy">
                                        <span class="text-sm font-bold text-gray-400 font-mono">#{{ $order->id }}</span>
                                        {{ $order->order_number }}
                                        <svg x-show="!copied" class="w-4 h-4 text-gray-300 group-hover/copy:text-primary opacity-0 group-hover/copy:opacity-100 transition-all transform group-hover/copy:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        <svg x-show="copied" x-transition class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                    <span x-show="copied" x-transition class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-[10px] font-bold rounded-lg shadow-xl whitespace-nowrap z-10">Copied to Clipboard!</span>
                                </div>
                                {{-- Status Badge --}}
                                @php
                                    $statusConfig = [
                                        'placed' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400'],
                                        'confirmed' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'dot' => 'bg-blue-500'],
                                        'processing' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'dot' => 'bg-purple-500'],
                                        'ready_to_ship' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
                                        'shipped' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'dot' => 'bg-indigo-500'],
                                        'delivered' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'dot' => 'bg-green-500'],
                                        'cancelled' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'dot' => 'bg-red-500'],
                                    ];
                                    $config = $statusConfig[$order->status] ?? $statusConfig['placed'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $config['bg'] }} {{ $config['text'] }} ring-1 ring-inset ring-black/5 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }} animate-pulse"></span>
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] text-gray-500 font-bold tracking-tight">
                                <div class="flex items-center gap-2 group/info cursor-default hover:text-gray-900 transition-colors">
                                    <div class="p-1 rounded-md bg-gray-100 group-hover/info:bg-blue-50 group-hover/info:text-blue-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    {{ $order->customer->name }}
                                </div>
                                <div class="flex items-center gap-2 group/info cursor-default hover:text-gray-900 transition-colors">
                                    <div class="p-1 rounded-md bg-gray-100 group-hover/info:bg-purple-50 group-hover/info:text-purple-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    {{ $order->created_at->format('M d, Y • h:i A') }}
                                </div>
                                <div class="flex items-center gap-2 group/info cursor-default hover:text-gray-900 transition-colors">
                                    <div class="p-1 rounded-md bg-gray-100 group-hover/info:bg-orange-50 group-hover/info:text-orange-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    {{ $order->warehouse->name ?? 'Main Warehouse' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-right space-y-1">
                        <div class="inline-flex flex-col items-end">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Grand Total</span>
                            <p class="text-2xl font-black text-primary tracking-tighter">Rs {{ number_format($order->grand_total, 2) }}</p>
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $order->items->count() }} specialized items</p>
                    </div>
                </div>
            </div>

            {{-- Items Preview --}}
            <div class="px-6 py-5 bg-gray-50/40">
                <div class="flex items-center gap-5 overflow-x-auto no-scrollbar pb-1">
                    @foreach($order->items as $item)
                        <div class="group/item relative flex-shrink-0" title="{{ $item->product_name }} (x{{ $item->quantity }})">
                            <div class="w-20 h-20 rounded-2xl bg-white border border-gray-200 p-1.5 overflow-hidden shadow-sm transition-all duration-300 group-hover/item:shadow-md group-hover/item:border-primary/20 group-hover/item:-translate-y-1">
                                @if($item->product && $item->product->image_url)
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover rounded-xl transition-transform duration-700 group-hover/item:scale-110">
                                @else
                                    <div class="h-full w-full flex items-center justify-center bg-gray-50 text-gray-300 rounded-xl">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                <div class="absolute -top-2 -right-2 bg-primary text-white text-[11px] font-black px-2 py-1 rounded-lg shadow-lg shadow-primary/20 transform group-hover/item:scale-110 transition-transform ring-2 ring-white">
                                    {{ $item->quantity }}x
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($order->items->count() > 8)
                        <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-white border border-gray-200 border-dashed flex flex-col items-center justify-center text-gray-400 group/more cursor-pointer hover:bg-gray-50 hover:border-primary/30 transition-all">
                            <span class="text-xs font-black text-gray-500 group-hover/more:text-primary">+{{ $order->items->count() - 8 }}</span>
                            <span class="text-[8px] font-bold uppercase tracking-tighter">More</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Dispatch Details --}}
            @if($order->shipments->isNotEmpty())
                <div class="px-6 py-4 border-t border-gray-100 bg-blue-50/30 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 animate-pulse">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="space-y-0.5">
                            <span class="block text-[10px] font-black uppercase tracking-widest text-blue-900/60">Shipping Intelligence</span>
                            <span class="block text-xs font-bold text-blue-900">Live Shipment Tracked</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @foreach($order->shipments as $shipment)
                            <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl border border-blue-200 shadow-sm group/shipment hover:border-blue-400 transition-colors">
                                <span class="font-black text-gray-400 text-[10px] uppercase tracking-wider group-hover/shipment:text-blue-600 transition-colors">{{ $shipment->carrier }}</span>
                                <span class="h-3 w-px bg-gray-200"></span>
                                <span class="font-mono font-black text-sm text-blue-900 select-all cursor-copy">{{ $shipment->tracking_number }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Lifecycle & Actions --}}
            <div class="px-6 py-6 border-t border-gray-100 bg-white">
                <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-8">
                    <div class="flex-1 max-w-3xl">
                        <div class="relative flex justify-between items-center w-full px-2">
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1.5 bg-gray-100 rounded-full z-0"></div>
                            @php
                                $statusOrder = ['placed', 'confirmed', 'processing', 'ready_to_ship', 'shipped', 'delivered'];
                                $orderStatus = ($order->status == 'completed') ? 'delivered' : $order->status;
                                $currentIdx = array_search($orderStatus, $statusOrder);
                                $currentIdx = $currentIdx === false ? -1 : $currentIdx; 
                            @endphp
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-gradient-to-r from-primary to-primary-foreground rounded-full z-0 transition-all duration-1000 ease-out shadow-sm" style="width: {{ max(0, min(100, $currentIdx * 20)) }}%"></div>

                            @foreach($statusOrder as $idx => $step)
                                @if($step === 'placed') @continue @endif
                                @php
                                    $isCompleted = $idx <= $currentIdx;
                                    $isCurrent = $idx === $currentIdx;
                                    $label = match ($step) {
                                        'confirmed' => 'Verified',
                                        'processing' => 'Packing',
                                        'ready_to_ship' => 'Ready',
                                        'shipped' => 'On Way',
                                        'delivered' => 'Finished',
                                        default => ucfirst($step)
                                    };
                                @endphp
                                <div class="relative z-10 flex flex-col items-center group/step">
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center border-2 transition-all duration-500 {{ $isCompleted ? 'bg-white border-primary text-primary shadow-xl shadow-primary/10' : 'bg-white border-gray-200 text-gray-300' }} {{ $isCurrent ? 'ring-8 ring-primary/5 scale-110' : '' }}">
                                        @if($isCompleted)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                        @else
                                            <span class="text-xs font-black font-mono">{{ $idx }}</span>
                                        @endif
                                    </div>
                                    <span class="mt-3 text-[10px] font-black uppercase tracking-widest transition-colors duration-300 {{ $isCompleted ? 'text-gray-900' : 'text-gray-400' }}">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-end lg:self-center">
                        @if(!$order->hasInvoice() && in_array($order->status, ['processing', 'ready_to_ship']))
                            <span class="hidden md:inline-flex px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-100 items-center gap-1.5 animate-pulse">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Pending Invoice
                            </span>
                        @endif

                        @if($order->status === 'confirmed')
                            <button type="button" @click="$dispatch('open-process-modal', { orderId: '{{ $order->id }}' })" class="inline-flex items-center gap-2 px-5 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-black transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                Start Processing
                            </button>
                        @endif

                        @if($order->status === 'processing')
                            <div class="flex items-center gap-2">
                                <div class="flex bg-gray-100 rounded-lg p-0.5" x-data="{}">
                                    <a href="{{ $order->invoices->isNotEmpty() ? route('tenant.orders.invoice', $order) : '#' }}" 
                                       @click="if('{{ $order->invoices->isEmpty() }}' === '1') { $dispatch('notify', { type: 'error', message: 'Invoice not found' }); $event.preventDefault(); } else { $dispatch('notify', { type: 'success', message: 'Invoice download started' }); }"
                                       class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-white rounded-md transition-all shadow-sm" title="Print Invoice">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </a>
                                    <a href="{{ route('tenant.orders.receipt', $order) }}" 
                                       @click="$dispatch('notify', { type: 'success', message: 'Receipt download started' })"
                                       class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-white rounded-md transition-all shadow-sm" title="Print COD Receipt">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </a>
                                </div>
                                <form action="{{ route('tenant.processing.orders.ready', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs font-bold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-md shadow-purple-200 hover:shadow-lg hover:shadow-purple-300 transform hover:-translate-y-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                        Ready to Ship
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if($order->status === 'ready_to_ship')
                            <div class="flex items-center gap-2">
                                <div class="flex bg-gray-100 rounded-lg p-0.5" x-data="{}">
                                    <a href="{{ $order->invoices->isNotEmpty() ? route('tenant.orders.invoice', $order) : '#' }}" 
                                       @click="if('{{ $order->invoices->isEmpty() }}' === '1') { $dispatch('notify', { type: 'error', message: 'Invoice not found' }); $event.preventDefault(); } else { $dispatch('notify', { type: 'success', message: 'Invoice download started' }); }"
                                       class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-white rounded-md transition-all shadow-sm" title="Print Invoice">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </a>
                                    <a href="{{ route('tenant.orders.receipt', $order) }}" 
                                       @click="$dispatch('notify', { type: 'success', message: 'Receipt download started' })"
                                       class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-white rounded-md transition-all shadow-sm" title="Print COD Receipt">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </a>
                                </div>
                                <button type="button" @click="$dispatch('open-dispatch-modal', { orderId: '{{ $order->id }}', orderNumber: '{{ $order->order_number }}', actionUrl: '{{ route('tenant.processing.orders.dispatch', $order) }}' })" class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all shadow-md shadow-emerald-200 hover:shadow-lg hover:shadow-emerald-300 transform hover:-translate-y-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Dispatch
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200">
            <div class="w-20 h-20 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">No orders found</h3>
            <p class="text-gray-500 mt-2 max-w-sm mx-auto">There are no orders matching your current criteria. Try adjusting your filters.</p>
            <a href="{{ route('tenant.processing.orders.index') }}" class="inline-flex items-center gap-2 mt-6 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Clear Filters
            </a>
        </div>
    @endforelse
</div>
<div class="mt-8">
    {{ $orders->withQueryString()->links() }}
</div>
