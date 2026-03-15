<x-layouts.app>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Inventory Management</h1>
                <p class="text-muted-foreground mt-1">Monitor and adjust stock levels across all warehouses.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="$dispatch('open-adjustment-modal')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/><path d="m16 14-4-4-4 4"/></svg>
                    Stock Adjustment
                </button>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="rounded-2xl border border-border/60 bg-card/50 p-4 backdrop-blur-sm">
            <form action="{{ route('tenant.inventory.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[300px]">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-muted-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by SKU or Product Name..." 
                        class="w-full rounded-xl border-border/60 bg-background/50 pl-10 pr-4 py-2 text-sm focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <button type="submit" class="rounded-xl border border-border/60 bg-background px-4 py-2 text-sm font-medium hover:bg-accent transition-all">
                    Search
                </button>
                @if(request()->anyFilled(['search', 'category_id']))
                    <a href="{{ route('tenant.inventory.index') }}" class="text-sm text-muted-foreground hover:text-foreground">Clear Filters</a>
                @endif
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="rounded-2xl border border-border/60 bg-card/50 overflow-hidden backdrop-blur-sm shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border/60 bg-muted/30">
                            <th class="p-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Product</th>
                            <th class="p-4 text-xs font-bold uppercase tracking-wider text-muted-foreground text-center">SKU</th>
                            <th class="p-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Category</th>
                            @foreach($warehouses as $wh)
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-muted-foreground text-center">{{ $wh->code }}</th>
                            @endforeach
                            <th class="p-4 text-xs font-bold uppercase tracking-wider text-muted-foreground text-right">Total Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        @forelse($products as $product)
                        <tr class="group hover:bg-accent/30 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold shadow-sm">
                                        {{ substr($product->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-foreground">{{ $product->name }}</div>
                                        <div class="text-[10px] text-muted-foreground uppercase">{{ $product->unit_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-center font-mono text-xs text-muted-foreground">{{ $product->sku ?? '-' }}</td>
                            <td class="p-4">
                                <span class="rounded-full bg-muted px-2 py-0.5 text-[10px] font-bold uppercase text-muted-foreground border border-border/60">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            @foreach($warehouses as $wh)
                                @php 
                                    $stock = $product->stocks->firstWhere('warehouse_id', $wh->id);
                                    $oh = $stock ? $stock->quantity : 0;
                                    $res = $stock ? $stock->reserve_quantity : 0;
                                    $avl = $oh - $res;
                                @endphp
                                <td class="p-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-background border border-border/60 shadow-sm">
                                            <span class="text-[8px] font-black uppercase tracking-tighter text-muted-foreground/40">OH:</span>
                                            <span class="text-[10px] font-bold {{ $oh <= 5 ? 'text-amber-600' : 'text-foreground' }}">
                                                {{ $oh > 0 ? '+' : '' }}{{ number_format($oh, 0) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 px-1.5 py-0.5 rounded bg-muted/30 text-[8px] font-medium tracking-tight text-muted-foreground/60">
                                            <span title="Reserved" class="flex items-center gap-0.5">
                                                <span class="opacity-40 font-black uppercase text-[7px]">Res:</span>
                                                {{ $res > 0 ? '+' : '' }}{{ number_format($res, 0) }}
                                            </span>
                                            <span class="text-muted-foreground/20">|</span>
                                            <span title="Available" class="flex items-center gap-0.5 {{ $avl < 0 ? 'text-rose-600 font-black' : ($avl == 0 ? 'text-muted-foreground/40' : 'text-emerald-600') }}">
                                                <span class="opacity-40 font-black uppercase text-[7px]">Avl:</span>
                                                {{ $avl > 0 ? '+' : '' }}{{ number_format($avl, 0) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            @endforeach
                            <td class="p-4 text-right">
                                <div class="flex flex-col items-end gap-2">
                                    <!-- Main Stock Status -->
                                    <div class="space-y-1">
                                        @if($product->stock_on_hand <= 0)
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/10 w-fit shadow-sm ml-auto">
                                                <span class="flex h-1 w-1 rounded-full bg-rose-500 animate-pulse"></span>
                                                <span class="text-[8px] font-black uppercase tracking-widest">Out of Stock</span>
                                            </div>
                                        @elseif($product->stock_on_hand <= $product->reorder_level)
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/10 w-fit shadow-sm ml-auto">
                                                <span class="flex h-1 w-1 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span class="text-[8px] font-black uppercase tracking-widest">Low: {{ floatval($product->stock_on_hand) }}</span>
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/10 w-fit shadow-sm ml-auto">
                                                <span class="flex h-1 w-1 rounded-full bg-emerald-500"></span>
                                                <span class="text-[8px] font-black uppercase tracking-widest">Total: {{ floatval($product->stock_on_hand) }}</span>
                                            </div>
                                        @endif

                                        <!-- Statistics Section -->
                                        <div class="flex flex-col gap-1 items-end pr-2 border-r-2 border-gray-100/80">
                                            @if($product->pending_order_qty > 0)
                                                <span class="px-1 py-0.5 rounded bg-indigo-50/50 text-[8px] font-black text-indigo-500 uppercase tracking-widest border border-indigo-100/50 shadow-sm flex items-center gap-1">
                                                    Placed: {{ floatval($product->pending_order_qty) }}
                                                </span>
                                            @endif
                                            <span class="px-1 py-0.5 rounded bg-emerald-50/50 text-[8px] font-black text-emerald-600 uppercase tracking-widest border border-emerald-100/50 shadow-sm flex items-center gap-1">
                                                Sellable: {{ floatval($product->sellable_qty) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 4 + count($warehouses) }}" class="p-20 text-center">
                                <div class="flex flex-col items-center justify-center text-muted-foreground/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                    <p class="text-lg font-semibold text-foreground">No inventory data</p>
                                    <p class="text-sm mt-1 text-muted-foreground">Add products to see their stock levels here.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="border-t border-border/60 bg-muted/30 p-4">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Adjustment Modal -->
    <div x-data="{ open: false, product_id: '', warehouse_id: '', type: 'add', quantity: 0, reason: '' }"
         @open-adjustment-modal.window="open = true"
         x-show="open"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-cloak>
        <div @click.away="open = false" 
             class="w-full max-w-lg rounded-2xl border border-border/60 bg-background p-8 shadow-2xl animate-in zoom-in-95 duration-200">
            <h2 class="text-2xl font-bold mb-2">Stock Adjustment</h2>
            <p class="text-muted-foreground mb-6 text-sm">Manually add, remove, or set stock levels for products.</p>

            <form action="{{ route('tenant.inventory.adjust') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Product</label>
                    <select name="product_id" x-model="product_id" required 
                        class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                        <option value="">Select Product...</option>
                        @foreach(App\Models\Product::all() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Warehouse</label>
                    <select name="warehouse_id" x-model="warehouse_id" required 
                        class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                        <option value="">Select Warehouse...</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Action</label>
                        <select name="type" x-model="type" required 
                            class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                            <option value="add">Add Stock (+)</option>
                            <option value="subtract">Subtract Stock (-)</option>
                            <option value="set">Set Exact Quantity (=)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Quantity</label>
                        <input type="number" name="quantity" x-model="quantity" step="1" min="0" required 
                            class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-muted-foreground uppercase tracking-widest">Reason / Note</label>
                    <input type="text" name="reason" x-model="reason" placeholder="e.g. Initial stock, Restock, Damage..." required 
                        class="w-full rounded-xl border-border/60 bg-background px-4 py-2.5 shadow-sm focus:ring-primary/20 focus:border-primary">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="open = false" 
                        class="flex-1 rounded-xl border border-border bg-background px-4 py-3 font-semibold hover:bg-accent transition-all text-sm">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="flex-1 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-95 text-sm">
                        Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
