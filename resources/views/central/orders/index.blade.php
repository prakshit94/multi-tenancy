@extends('layouts.app')

@section('content')
<div id="orders-page-wrapper" class="flex flex-1 flex-col space-y-8 p-4 md:p-8 animate-in fade-in zoom-in-[0.98] duration-700 bg-background/30 relative overflow-hidden">
   <!-- Premium Animated Background Elements -->
   <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-primary/15 rounded-full blur-[100px] pointer-events-none z-0 animate-pulse" style="animation-duration: 7s;"></div>
   <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] bg-indigo-500/15 rounded-full blur-[120px] pointer-events-none z-0 animate-pulse" style="animation-duration: 10s;"></div>
   <div class="absolute top-[30%] left-[50%] w-[400px] h-[400px] bg-purple-500/10 rounded-full blur-[100px] pointer-events-none z-0 animate-pulse" style="animation-duration: 12s;"></div>
   
   <!-- Header Area -->
   <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-6 z-10 relative mt-2">
      <div class="space-y-3">
         <h1 class="text-4xl font-black tracking-tighter bg-gradient-to-br from-foreground via-foreground/90 to-muted-foreground bg-clip-text text-transparent flex items-center gap-4">
            <div class="p-3 bg-gradient-to-br from-primary/20 to-primary/5 rounded-2xl border border-primary/10 shadow-inner">
               <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary drop-shadow-sm"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            All Orders
         </h1>
         <!-- <p class="text-muted-foreground text-sm font-medium ml-1 max-w-xl leading-relaxed">Manage customer orders, track fulfillment states in real-time, and seamlessly handle payments.</p> -->
      </div>


   </div>

   <div id="orders-table-container" x-data="{ selected: [] }">
      
      <!-- Control Bar (Glassmorphism) -->
      <div class="flex flex-wrap items-center justify-between gap-5 p-3 pl-4 bg-white/60 dark:bg-black/30 border border-white/50 dark:border-white/10 backdrop-blur-3xl rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.05)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] mb-8 transition-all duration-500 hover:shadow-[0_12px_40px_rgb(0,0,0,0.08)] group/control relative z-20">
         
         <div class="flex flex-wrap items-center gap-4 flex-1 min-w-[280px]">
            <!-- Filters Form -->
            <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3 w-full xl:w-auto relative z-10 w-full">
               
               <!-- Date Range (Joined) -->
               <div class="flex items-center rounded-xl border border-white/60 dark:border-white/10 bg-white/50 dark:bg-black/40 overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-primary/30 focus-within:border-primary/30 transition-all hover:bg-white/80 dark:hover:bg-black/60 group">
                   <div class="pl-3 pr-1 text-muted-foreground/60 group-focus-within:text-primary transition-colors">
                       <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                   </div>
                   <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-10 border-none bg-transparent text-[13px] px-2 focus:ring-0 outline-none w-32 text-foreground font-semibold cursor-pointer placeholder-muted-foreground/50" placeholder="Start Date">
                   <div class="w-px h-5 bg-border/50"></div>
                   <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-10 border-none bg-transparent text-[13px] px-2 focus:ring-0 outline-none w-32 text-foreground font-semibold cursor-pointer placeholder-muted-foreground/50" placeholder="End Date">
               </div>

               <!-- Status Dropdowns -->
               <div class="flex flex-wrap items-center gap-3">
                   @php 
                       $bgStr = "bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2216%22%20height%3D%2216%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2394a3b8%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E')]"; 
                       
                       $oQ = \App\Models\Order::query();
                       if (!auth()->user()->hasRole('Super Admin')) {
                           $oQ->where('created_by', auth()->id());
                       }
                       $statusCounts = clone $oQ;
                       $statusMap = $statusCounts->select('status', \DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status');
                       $totalOrders = $statusMap->sum();
                   @endphp
                   
                   <select name="status" class="appearance-none h-10 rounded-xl border border-white/60 dark:border-white/10 bg-white/50 dark:bg-black/40 text-[13px] font-bold text-foreground cursor-pointer shadow-sm hover:bg-white/80 dark:hover:bg-black/60 transition-colors focus:ring-2 focus:ring-primary/30 outline-none pl-4 pr-9 min-w-[160px] max-w-[200px] text-ellipsis {!! $bgStr !!} bg-[length:16px_16px] bg-[right_12px_center] bg-no-repeat">
                       <option value="">Order: All ({{ $totalOrders }})</option>
                       <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending ({{ $statusMap['pending'] ?? 0 }})</option>
                       <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled ({{ $statusMap['scheduled'] ?? 0 }})</option>
                       <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed ({{ $statusMap['confirmed'] ?? 0 }})</option>
                       <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing ({{ $statusMap['processing'] ?? 0 }})</option>
                       <option value="ready_to_ship" {{ request('status') === 'ready_to_ship' ? 'selected' : '' }}>Ready To Ship ({{ $statusMap['ready_to_ship'] ?? 0 }})</option>
                       <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Dispatched ({{ $statusMap['shipped'] ?? 0 }})</option>
                       <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered ({{ $statusMap['delivered'] ?? 0 }})</option>
                       <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed ({{ $statusMap['completed'] ?? 0 }})</option>
                       <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled ({{ $statusMap['cancelled'] ?? 0 }})</option>
                       <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned ({{ $statusMap['returned'] ?? 0 }})</option>
                   </select>

                   <select name="payment_status" class="appearance-none h-10 rounded-xl border border-white/60 dark:border-white/10 bg-white/50 dark:bg-black/40 text-[13px] font-bold text-foreground cursor-pointer shadow-sm hover:bg-white/80 dark:hover:bg-black/60 transition-colors focus:ring-2 focus:ring-primary/30 outline-none pl-4 pr-9 min-w-[120px] {!! $bgStr !!} bg-[length:16px_16px] bg-[right_12px_center] bg-no-repeat">
                       <option value="">Payment: All</option>
                       <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                       <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                       <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                   </select>

                   <select name="shipping_status" class="appearance-none h-10 rounded-xl border border-white/60 dark:border-white/10 bg-white/50 dark:bg-black/40 text-[13px] font-bold text-foreground cursor-pointer shadow-sm hover:bg-white/80 dark:hover:bg-black/60 transition-colors focus:ring-2 focus:ring-primary/30 outline-none pl-4 pr-9 min-w-[120px] {!! $bgStr !!} bg-[length:16px_16px] bg-[right_12px_center] bg-no-repeat">
                       <option value="">Shipping: All</option>
                       <option value="pending" {{ request('shipping_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                       <option value="shipped" {{ request('shipping_status') === 'shipped' ? 'selected' : '' }}>Dispatched</option>
                       <option value="delivered" {{ request('shipping_status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                   </select>

               </div>

               <!-- Search -->
               <div class="relative transition-all duration-500 group-focus-within:w-[320px] w-64 lg:w-72">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/60 group-focus-within:text-primary transition-colors">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                     </svg>
                  </div>
                  <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders by ID, Name..." 
                     class="block w-full h-10 rounded-xl border border-white/60 dark:border-white/10 py-2 pl-10 pr-10 text-foreground font-semibold bg-white/70 dark:bg-black/50 placeholder:text-muted-foreground/50 placeholder:font-medium focus:bg-white dark:focus:bg-black focus:ring-2 focus:ring-primary/30 focus:border-primary/30 text-[13px] leading-6 transition-all shadow-[0_2px_10px_rgba(0,0,0,0.02)] outline-none backdrop-blur-md">
                  <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                      <kbd class="hidden sm:inline-flex h-5 items-center gap-1 rounded bg-muted/40 px-1.5 font-mono text-[10px] font-bold text-muted-foreground/80 ring-1 ring-border/20"><span class="text-xs">/</span></kbd>
                  </div>
               </div>
            </form>
         </div>


      </div>

    <!-- Floating Bulk Action Bar -->
    <div x-cloak 
         x-show="selected.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 w-full max-w-xl px-4 pointer-events-none">
        
        <div class="pointer-events-auto flex items-center justify-between gap-4 p-2.5 pl-5 bg-black/90 dark:bg-black/80 text-white backdrop-blur-2xl rounded-full shadow-[0_20px_40px_rgb(0,0,0,0.3)] border border-white/10 ring-1 ring-white/5 relative overflow-hidden before:absolute before:inset-0 before:bg-gradient-to-r before:from-primary/20 before:to-transparent before:pointer-events-none">
            
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center h-6 w-6 rounded-full bg-primary text-primary-foreground text-xs font-bold shadow-sm">
                    <span x-text="selected.length"></span>
                </div>
                <span class="text-sm font-medium">Selected</span>
                
                <div class="h-4 w-px bg-background/20"></div>
                
                <button @click="selected = []" class="text-xs text-background/70 hover:text-background transition-colors font-medium">
                    Clear
                </button>
            </div>

            <div class="flex items-center gap-2">
                <form id="bulk-print-form" action="{{ route('central.orders.bulk-print') }}" method="POST" class="flex gap-2">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    
                    <button type="submit" name="type" value="invoice" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold bg-white/10 hover:bg-white/20 text-white transition-colors border border-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                        Invoices
                    </button>
                    
                    <button type="submit" name="type" value="cod" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold bg-white/10 hover:bg-white/20 text-white transition-colors border border-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        COD
                    </button>
                </form>
            </div>
        </div>
    </div>
      <div class="rounded-[2rem] border border-white/60 dark:border-white/10 bg-white/60 dark:bg-black/30 backdrop-blur-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] overflow-hidden relative transition-all duration-500 hover:shadow-[0_12px_40px_rgb(0,0,0,0.08)]">
         <div id="table-loading" class="absolute inset-0 z-50 bg-background/40 backdrop-blur-md flex flex-col items-center justify-center opacity-0 pointer-events-none transition-all duration-500 rounded-[2rem]">
            <div class="relative flex items-center justify-center">
                <div class="absolute w-16 h-16 bg-primary/20 rounded-full blur-xl animate-pulse"></div>
                <svg class="animate-spin h-10 w-10 text-primary drop-shadow-md relative z-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <span class="mt-4 text-[10px] font-black uppercase tracking-widest text-primary animate-pulse drop-shadow-sm bg-primary/10 px-3 py-1 rounded-full border border-primary/20">Syncing Data</span>
         </div>
         <div class="border-b border-white/40 dark:border-white/10 p-3 sm:p-4 bg-white/50 dark:bg-black/20 flex flex-col xl:flex-row items-center justify-between gap-4 relative z-10 w-full overflow-hidden">
            <div class="flex items-center gap-3 text-xs text-muted-foreground font-bold px-2 w-full xl:w-auto">
               <div class="text-[11px] uppercase tracking-widest text-muted-foreground/80 flex items-center gap-2 bg-white/40 dark:bg-black/40 px-3 py-1.5 rounded-xl border border-white/60 dark:border-white/5 shadow-sm">
                   @if($orders->total() > 0)
                       <span>Page <span class="font-black text-foreground">{{ $orders->currentPage() }}</span> of <span class="font-black text-foreground">{{ max(1, $orders->lastPage()) }}</span></span>
                       <span class="opacity-50">|</span> 
                       <span><span class="font-black text-foreground">{{ $orders->firstItem() ?? 0 }}</span> - <span class="font-black text-foreground">{{ $orders->lastItem() ?? 0 }}</span> of <span class="font-black text-foreground">{{ $orders->total() }}</span></span>
                   @else
                       <span>0 orders found</span>
                   @endif
               </div>
            </div>
            @if($orders->hasPages())
            <div class="pagination-premium w-full xl:w-auto overflow-x-auto pb-2 xl:pb-0 scrollbar-none sm:[&>nav>div.hidden]:flex sm:[&>nav>div.hidden]:items-center sm:[&>nav>div.hidden]:justify-end [&_p.leading-5]:hidden">
               {{ $orders->links() }}
            </div>
            @endif
         </div>
         <div class="relative w-full overflow-auto scrollbar-thin scrollbar-thumb-white/20 scrollbar-track-transparent">
            <table class="w-full caption-bottom text-sm relative">
               <thead class="[&_tr]:border-b relative z-10">
                  <tr class="border-b border-white/40 dark:border-white/10 bg-white/60 dark:bg-black/40 backdrop-blur-xl transition-colors">
                     <th class="h-10 w-[40px] px-4 text-left align-middle">
                        <div class="flex items-center">
                           <input type="checkbox" class="h-3.5 w-3.5 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary shadow-sm" @click="selected = $event.target.checked ? [{{ $orders->pluck('id')->join(',') }}] : []">
                        </div>
                     </th>
                     <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Order & Date</th>
                     <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Customer</th>
                     <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Items</th>
                     <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Warehouse</th>
                     <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Payment</th>
                     <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Shipping</th>
                     <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Total</th>
                     <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Status</th>
                     <th class="h-10 px-4 text-right align-middle font-bold text-muted-foreground/60 uppercase tracking-widest text-[10px]">Actions</th>
                  </tr>
               </thead>
               <tbody class="[&_tr:last-child]:border-0 text-sm">
                            @forelse($orders as $order)
                           <tr class="group border-b border-white/20 dark:border-white/5 transition-all duration-300 hover:bg-white/90 dark:hover:bg-white/5 data-[state=selected]:bg-primary/5 data-[state=selected]:border-primary/20 relative hover:shadow-[0_4px_30px_rgb(0,0,0,0.03)] hover:z-10 hover:border-transparent dark:hover:shadow-[0_4px_30px_rgb(0,0,0,0.2)]">

                               <!-- Checkbox -->
                               <td class="p-4 px-4 align-middle">
                                   <input type="checkbox"
                                          value="{{ $order->id }}"
                                          x-model="selected"
                                          class="h-3.5 w-3.5 rounded border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary shadow-sm">
                               </td>

                               <!-- Order Number + Date -->
                               <td class="p-4 px-4 align-middle">
                                   <div class="flex flex-col space-y-1">
                                       <a href="{{ route('central.orders.show', $order) }}"
                                          class="font-bold text-primary hover:underline text-sm tracking-tight transition-colors">
                                           {{ $order->order_number }}
                                       </a>
                                       <div class="flex items-center gap-1.5 text-muted-foreground/80">
                                            @if($order->is_future_order && $order->scheduled_at)
                                                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wide bg-indigo-500/10 px-1 py-px rounded">Scheduled</span>
                                                <span class="text-[10px] font-mono">{{ $order->scheduled_at->format('M d, H:i') }}</span>
                                            @else
                                                <span class="text-[10px] font-mono">{{ $order->created_at->format('M d, H:i') }}</span>
                                            @endif
                                       </div>
                                       <div class="text-[10px] text-muted-foreground/60">
                                           By: {{ $order->creator->name ?? 'System' }}
                                       </div>
                                   </div>
                               </td>

                               <!-- Customer -->
                               <td class="p-4 px-4 align-middle">
                                   <div class="flex items-center gap-2">
                                       <div class="h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-[9px] font-bold text-white shadow-sm ring-1 ring-white/20">
                                           {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                       </div>
                                       <div class="flex flex-col">
                                           <a href="{{ $order->customer_id ? route('central.customers.show', $order->customer_id) : '#' }}"
                                              class="text-xs font-semibold hover:text-primary hover:underline truncate max-w-[120px]">
                                               {{ $order->customer->name ?? 'Guest' }}
                                           </a>
                                           @if($order->customer && $order->customer->mobile)
                                               <span class="text-[10px] text-muted-foreground leading-none mt-0.5">{{ $order->customer->mobile }}</span>
                                           @endif
                                       </div>
                                   </div>
                               </td>

                               <!-- Items (Count) -->
                               <td class="p-4 px-4 align-middle">
                                   <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-muted/40 text-xs font-medium text-muted-foreground">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-70"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                       {{ $order->items->count() }}
                                   </span>
                               </td>

                               <!-- Warehouse -->
                               <td class="p-4 px-4 align-middle">
                                   <div class="flex items-center gap-1.5 text-xs font-medium text-foreground/80">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/></svg>
                                       {{ $order->warehouse->name ?? 'N/A' }}
                                   </div>
                               </td>

                               <!-- Payment Method & Status -->
                               <td class="p-4 px-4 align-middle">
                                   <div class="flex flex-col gap-1">
                                       <div class="flex items-center gap-1.5">
                                            @if(strtolower($order->payment_method) === 'cash')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-500"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                            @endif
                                            <span class="text-[11px] font-medium capitalize text-muted-foreground">{{ str_replace('_', ' ', $order->payment_method ?? '-') }}</span>
                                       </div>
                                       @switch($order->payment_status)
                                            @case('paid')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 w-fit">Paid</span>
                                                @break
                                            @case('partial')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20 w-fit">Partial</span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-destructive/10 text-destructive border border-destructive/20 w-fit">Unpaid</span>
                                       @endswitch
                                   </div>
                               </td>

                               <!-- Shipping Method & Status -->
                               <td class="p-4 px-4 align-middle">
                                   <div class="flex flex-col gap-1">
                                       <div class="flex items-center gap-1.5">
                                           <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sky-500"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                           <span class="text-[11px] font-medium capitalize text-muted-foreground">{{ str_replace('_', ' ', $order->shipping_method ?? '-') }}</span>
                                       </div>
                                       @if(in_array($order->shipping_status, ['shipped','in_transit','delivered']) && $order->shipments->isNotEmpty())
                                            <span class="text-[10px] font-mono text-muted-foreground/80 tracking-tight pl-4">
                                                #{{ $order->shipments->first()->tracking_number }}
                                            </span>
                                       @else
                                            <span class="text-[10px] font-medium text-muted-foreground/60 pl-4 capitalize">{{ str_replace('_', ' ', $order->shipping_status ?? 'Pending') }}</span>
                                       @endif
                                   </div>
                               </td>

                               <!-- Total -->
                               <td class="p-4 px-4 align-middle">
                                   <div class="flex flex-col items-start">
                                       <span class="font-bold text-sm text-foreground">Rs {{ number_format($order->grand_total, 2) }}</span>
                                       @if($order->payment_status === 'paid')
                                            <span class="text-[10px] text-emerald-600 font-medium">Fully Paid</span>
                                       @elseif($order->payment_status === 'partial')
                                            <span class="text-[10px] text-amber-600 font-medium whitespace-nowrap">Due: {{ number_format($order->grand_total - $order->paid_amount, 2) }}</span>
                                       @endif
                                   </div>
                               </td>

                               <!-- Status -->
                               <td class="p-4 px-4 align-middle">
                                   @switch($order->status)

                                       @case('completed')
                                           @if($order->payment_status === 'paid')
                                               <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 shadow-sm">
                                                   <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                   Completed
                                               </span>
                                           @else
                                               <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-teal-500/10 text-teal-600 border border-teal-500/20 shadow-sm">
                                                   <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                                                   Delivered
                                               </span>
                                           @endif
                                           @break

                                       @case('shipped')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-indigo-500/10 text-indigo-600 border border-indigo-500/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                               Dispatched
                                           </span>
                                           @break

                                       @case('in_transit')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-orange-500/10 text-orange-600 border border-orange-500/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                               In Transit
                                           </span>
                                           @break

                                       @case('delivered')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-teal-500/10 text-teal-600 border border-teal-500/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                                               Delivered
                                           </span>
                                           @break

                                       @case('returned')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-600 border border-rose-500/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                               Returned
                                           </span>
                                           @break

                                       @case('confirmed')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-blue-500/10 text-blue-600 border border-blue-500/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                               Confirmed
                                           </span>
                                           @break

                                       @case('ready_to_ship')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-sky-500/10 text-sky-600 border border-sky-500/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                               Ready To Ship
                                           </span>
                                           @break

                                       @case('processing')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-purple-500/10 text-purple-600 border border-purple-500/20 shadow-sm">
                                               <span class="animate-pulse h-1.5 w-1.5 rounded-full bg-purple-500"></span>
                                               Processing
                                           </span>
                                           @break

                                       @case('scheduled')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-violet-500/10 text-violet-600 border border-violet-500/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                               Scheduled
                                           </span>
                                           @break

                                       @case('cancelled')
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-destructive/10 text-destructive border border-destructive/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-destructive"></span>
                                               Cancelled
                                           </span>
                                           @break

                                       @default
                                           <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-600 border border-amber-500/20 shadow-sm">
                                               <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                               Pending
                                           </span>
                                   @endswitch
                               </td>

                               <!-- Actions -->
                               <td class="p-4 px-4 align-middle text-right">
                                   <div class="relative flex justify-end" x-data="{ open: false }" @click.away="open = false">

                                       <button @click="open = !open"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-accent hover:text-foreground transition-all duration-200">
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                       </button>

                                       <div x-show="open" x-transition.opacity.scale.95
                                            class="absolute right-0 top-9 z-50 min-w-[160px] rounded-xl border border-border/60 bg-popover/95 backdrop-blur-xl p-1 shadow-lg ring-1 ring-black/5">

                                           <a href="{{ route('central.orders.show', $order) }}"
                                              class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-accent rounded-lg transition-colors">
                                               <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                               View Details
                                           </a>

                                           @if(!in_array($order->status, ['completed','cancelled']))
                                               <a href="{{ route('central.orders.edit', $order) }}"
                                                  class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-accent rounded-lg transition-colors">
                                                   <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                                   Edit Order
                                               </a>
                                           @endif

                                           @if(in_array($order->status, ['completed','delivered']))
                                               <a href="{{ route('central.returns.create', ['order_id'=>$order->id]) }}"
                                                  class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-orange-500/10 text-orange-600 rounded-lg transition-colors">
                                                   <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                                                   Request Return
                                               </a>
                                           @endif

                                           <div class="h-px bg-border/50 my-1"></div>

                                           @if($order->invoices->isNotEmpty())
                                               <a href="{{ route('central.invoices.pdf', $order->invoices->first()) }}"
                                                  @click="$dispatch('notify', { type: 'success', message: 'Invoice download started' })"
                                                  class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-accent rounded-lg transition-colors">
                                                   <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                   Print Invoice
                                               </a>
                                           @endif

                                           <a href="{{ route('central.orders.receipt', $order) }}"
                                              @click="$dispatch('notify', { type: 'success', message: 'Receipt download started' })"
                                              class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium hover:bg-accent rounded-lg transition-colors">
                                               <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17V7"/></svg>
                                               Print Receipt
                                           </a>
                                       </div>
                                   </div>
                               </td>

                           </tr>
                           @empty
<tr>
    <td colspan="10" class="p-20 text-center relative overflow-hidden">
        <!-- Premium Empty State -->
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-primary/5 pointer-events-none z-0"></div>
        <div class="relative z-10 flex flex-col items-center justify-center space-y-4">
            <div class="h-24 w-24 rounded-full bg-gradient-to-br from-white to-muted/30 dark:from-muted/20 dark:to-background flex items-center justify-center border border-white/60 dark:border-white/10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] mb-2 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary/40 group-hover:text-primary transition-colors duration-500 group-hover:scale-110 drop-shadow-sm"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-xl font-black tracking-tighter text-foreground bg-gradient-to-br from-foreground to-muted-foreground bg-clip-text text-transparent">No orders found</h3>
                <p class="text-sm font-medium text-muted-foreground max-w-[260px] mx-auto leading-relaxed">We could not find any active orders matching your current filters and search.</p>
            </div>
        </div>
    </td>
</tr>
@endforelse
</tbody>

            </table>
         </div>
         @if($orders->hasPages())
         <div class="border-t border-white/40 dark:border-white/10 p-4 sm:p-5 bg-white/50 dark:bg-black/20 backdrop-blur-xl relative z-10 transition-colors duration-300 flex flex-col xl:flex-row items-center justify-between gap-4 w-full">
            <div class="flex items-center gap-3 text-xs text-muted-foreground font-bold px-2 w-full xl:w-auto">
               <div class="text-[11px] uppercase tracking-widest text-muted-foreground/80 flex items-center gap-2 bg-white/40 dark:bg-black/40 px-3 py-1.5 rounded-xl border border-white/60 dark:border-white/5 shadow-sm">
                   <span>Page <span class="font-black text-foreground">{{ $orders->currentPage() }}</span> of <span class="font-black text-foreground">{{ max(1, $orders->lastPage()) }}</span></span>
                   <span class="opacity-50">|</span> 
                   <span><span class="font-black text-foreground">{{ $orders->firstItem() ?? 0 }}</span> - <span class="font-black text-foreground">{{ $orders->lastItem() ?? 0 }}</span> of <span class="font-black text-foreground">{{ $orders->total() }}</span></span>
               </div>
            </div>
            <div class="pagination-premium w-full xl:w-auto overflow-x-auto pb-2 xl:pb-0 scrollbar-none sm:[&>nav>div.hidden]:flex sm:[&>nav>div.hidden]:items-center sm:[&>nav>div.hidden]:justify-end [&_p.leading-5]:hidden">
                {{ $orders->links() }}
            </div>
         </div>
         @endif
      </div>
   </div>
</div>
<script>
   document.addEventListener('DOMContentLoaded', () => {
       const container = document.getElementById('orders-table-container');
       const loading = document.getElementById('table-loading');
       let searchTimeout;
   
       async function loadContent(url, pushState = true) {
           if (loading) loading.style.opacity = '1';
           try {
               const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
               if (!res.ok) throw new Error('Network response was not ok');
               const html = await res.text();
               const parser = new DOMParser();
               const doc = parser.parseFromString(html, 'text/html');
               const newContent = doc.getElementById('orders-table-container');
               if (newContent) {
                   container.innerHTML = newContent.innerHTML;
                   if (pushState) window.history.pushState({}, '', url);
                   if (typeof Alpine !== 'undefined') Alpine.initTree(container);
               } else {
                   window.location.href = url;
               }
           } catch (err) {
               window.location.href = url;
           } finally {
               if (loading) loading.style.opacity = '0';
           }
       }
   
       window.addEventListener('popstate', () => loadContent(window.location.href, false));
   
       container.addEventListener('click', (e) => {
           const link = e.target.closest('a.page-link') || e.target.closest('nav[role="navigation"] a') || e.target.closest('.pagination a');
           if (link && container.contains(link) && link.href) {
               e.preventDefault();
               loadContent(link.href);
           }
       });
   
        function updateFilters(forcePerPage = null) {
            const searchForm = document.getElementById('search-form');
            const params = new URLSearchParams();
            
            if (searchForm) {
                new FormData(searchForm).forEach((value, key) => {
                    if (value) params.append(key, value);
                });
            }

            if (forcePerPage) {
                params.set('per_page', forcePerPage);
            } else {
                const perPageSelect = document.querySelector('select[name="per_page"]');
                if (perPageSelect && perPageSelect.value) {
                    params.set('per_page', perPageSelect.value);
                }
            }
            
            const url = new URL(searchForm ? searchForm.action : window.location.href);
            loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
        }

        container.addEventListener('input', (e) => {
            if (e.target.name === 'search') {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    updateFilters();
                }, 400);
            }
        });
       
       container.addEventListener('change', (e) => {
            if (e.target.name === 'start_date' || 
                e.target.name === 'end_date' ||
                e.target.name === 'status' || 
                e.target.name === 'payment_status' ||
                e.target.name === 'shipping_status') {
                updateFilters();
           }
       });

        window.addEventListener('per-page-change', (e) => {
            updateFilters(e.detail?.value);
        });
       
       container.addEventListener('submit', (e) => {
           if (e.target.id === 'search-form' || e.target.id === 'per-page-form') {
               e.preventDefault();
               updateFilters();
           }
       });
   });
</script>
@endsection