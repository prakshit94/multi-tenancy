@extends('layouts.app')

@section('content')
    <div id="products-page-wrapper"
        class="flex flex-1 flex-col space-y-8 p-6 sm:p-10 animate-in fade-in duration-700 bg-gradient-to-br from-gray-50/50 via-white to-indigo-50/30 selection:bg-indigo-100 selection:text-indigo-900 min-h-full relative overflow-hidden">
        
        <!-- Decorative subtle background glow -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute top-1/2 -right-20 w-80 h-80 bg-purple-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 relative z-10">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 mb-2 border border-indigo-100/50 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    <span class="text-[10px] font-black text-indigo-700 tracking-[0.15em] uppercase">Catalog Management</span>
                </div>
                <h1
                    class="text-4xl sm:text-5xl font-black tracking-tighter text-gray-900 drop-shadow-sm">
                    Products
                </h1>
                <p class="text-gray-500 font-medium text-sm sm:text-base max-w-xl leading-relaxed">Oversee your global product catalog, fine-tune pricing, and monitor real-time stock inventory.</p>
            </div>

            <!-- Filter Pills (Apple iOS Style Segmented Control) -->
            <div class="flex items-center p-1.5 bg-gray-100/80 rounded-2xl border border-gray-200/60 backdrop-blur-xl shadow-sm w-max transition-all duration-300 hover:shadow-md">
                <a href="{{ route('central.products.index') }}"
                    class="relative px-5 py-2.5 rounded-[12px] text-sm font-bold transition-all duration-300 {{ request('status') === null ? 'bg-white text-indigo-900 shadow-md ring-1 ring-black/5 origin-center scale-100' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50 scale-95 hover:scale-100' }}">
                    All Items
                </a>
                <a href="{{ route('central.products.index', ['status' => 'active']) }}"
                    class="relative px-5 py-2.5 rounded-[12px] text-sm font-bold transition-all duration-300 {{ request('status') === 'active' ? 'bg-white text-emerald-600 shadow-md ring-1 ring-black/5 origin-center scale-100' : 'text-gray-500 hover:text-emerald-600 hover:bg-gray-200/50 scale-95 hover:scale-100' }}">
                    Active
                </a>
                <a href="{{ route('central.products.index', ['stock' => 'low']) }}"
                    class="relative px-5 py-2.5 rounded-[12px] text-sm font-bold transition-all duration-300 {{ request('stock') === 'low' ? 'bg-white text-amber-600 shadow-md ring-1 ring-black/5 origin-center scale-100' : 'text-gray-500 hover:text-amber-600 hover:bg-gray-200/50 scale-95 hover:scale-100' }}">
                    Low Stock
                </a>
            </div>
        </div>

        <div id="products-table-container" x-data="{ 
                selected: [], 
                viewingProduct: null,
                products: {{ \Illuminate\Support\Js::from($products->items()) }},
                showCost: {{ auth()->user()->hasRole('Super Admin') ? 'true' : 'false' }},
                formatPrice(price) {
                    return '₹' + parseFloat(price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric' });
                }
            }" class="relative z-10 space-y-6">
            
            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                    <!-- Selection Counter -->
                    <div x-cloak x-show="selected.length > 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                        class="flex items-center">
                        <div
                            class="px-4 py-2 rounded-2xl bg-gray-900 border border-black text-white text-sm font-black shadow-xl shadow-gray-900/20 flex items-center gap-2">
                            <span class="flex items-center justify-center bg-white/20 rounded-lg w-6 h-6 text-xs" x-text="selected.length"></span>
                            <span class="tracking-widest uppercase text-[10px]">Selected</span>
                        </div>
                    </div>

                    <!-- Search Box -->
                    <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex-1 sm:flex-none relative group w-full sm:w-auto">
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        @if(request('stock')) <input type="hidden" name="stock" value="{{ request('stock') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                        @endif

                        <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-[1.25rem] blur opacity-0 group-focus-within:opacity-20 transition duration-500"></div>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-600 transition-colors duration-300"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search products, SKU..." autocomplete="off"
                                class="block w-full sm:w-80 rounded-2xl border-0 py-3 pl-11 pr-12 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 bg-white/90 backdrop-blur-md transition-all duration-300 hover:ring-gray-300">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="hidden sm:inline-flex items-center justify-center px-2 py-0.5 rounded border border-gray-200 text-[10px] font-sans font-bold text-gray-400 bg-gray-50/50">⌘K</span>
                            </div>
                        </div>
                    </form>
                </div>

                @can('products create')
                    <a href="{{ route('central.products.create') }}"
                        class="group relative inline-flex items-center justify-center gap-2 rounded-2xl bg-gray-900 px-7 py-3 text-sm font-bold text-white shadow-xl shadow-gray-900/20 hover:bg-gray-800 hover:shadow-gray-900/30 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 w-full sm:w-auto overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/30 to-purple-500/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="relative z-10">Add Product</span>
                    </a>
                @endcan
            </div>

            <!-- Table Card Glassmorphism -->
            <div class="bg-white/60 backdrop-blur-2xl rounded-[2rem] border border-white shadow-2xl shadow-indigo-100/50 overflow-hidden relative">
                <!-- Loading Overlay -->
                <div id="table-loading"
                    class="absolute inset-0 z-50 bg-white/50 backdrop-blur-sm flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
                    <div class="relative flex justify-center items-center">
                        <div class="absolute animate-ping w-12 h-12 rounded-full bg-indigo-400/30"></div>
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent shadow-lg relative z-10"></div>
                    </div>
                </div>

                <!-- Pagination/Count Header -->
                <div class="border-b border-gray-100/50 p-5 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white/40">
                    <div class="text-sm font-medium text-gray-500 flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 shadow-inner rounded-xl text-indigo-600 border border-indigo-100/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        </div>
                        <div>Showing <span class="font-black text-gray-900">{{ $products->firstItem() ?? 0 }}</span> to <span class="font-black text-gray-900">{{ $products->lastItem() ?? 0 }}</span> of <span class="font-black text-indigo-600">{{ $products->total() }}</span> items</div>
                    </div>

                    <div class="flex items-center gap-3">
                        <form id="per-page-form" method="GET" action="{{ url()->current() }}" class="flex items-center gap-3">
                            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach

                            <label for="per_page" class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rows</label>
                            <div class="relative">
                                <select name="per_page" id="per_page"
                                    class="appearance-none block w-20 pl-4 pr-8 py-2 rounded-xl border-gray-200/80 text-sm font-bold text-gray-700 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-50">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Premium Table -->
                <div class="overflow-x-auto px-4 pb-4">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 w-[50px]">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox"
                                            class="h-5 w-5 rounded-md border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-all duration-200 shadow-sm"
                                            @click="selected = $event.target.checked ? [{{ $products->pluck('id')->join(',') }}] : []">
                                    </div>
                                </th>
                                <th class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Product Identity</th>
                                <th class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Classification</th>
                                <th class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Inventory</th>
                                <th class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Valuation</th>
                                <th class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Taxation</th>
                                <th class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Controls</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr class="group bg-white hover:bg-indigo-50/40 transition-all duration-300 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_20px_-8px_rgba(79,70,229,0.15)] ring-1 ring-gray-900/5 hover:ring-indigo-500/20 rounded-2xl">
                                    <td class="p-5 align-middle rounded-l-2xl">
                                        <div class="flex items-center justify-center">
                                            <input type="checkbox" value="{{ $product->id }}" x-model="selected"
                                                class="h-5 w-5 rounded-md border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm">
                                        </div>
                                    </td>

                                    <!-- Product Info -->
                                    <td class="p-5">
                                        <div class="flex items-center gap-5">
                                            <div class="relative h-16 w-16 flex-shrink-0 rounded-2xl bg-white shadow-sm ring-1 ring-black/5 overflow-hidden group-hover:shadow-md transition-all duration-300">
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                    class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500">
                                            </div>
                                            <div class="space-y-1.5 max-w-[200px]">
                                                <div class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2" title="{{ $product->name }}">
                                                    {{ $product->name }}
                                                </div>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-[10px] font-bold text-gray-400 font-mono bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">{{ $product->sku }}</span>
                                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-0.5 text-[9px] font-black text-gray-500 uppercase tracking-[0.1em] border border-gray-100">{{ $product->type }}</span>
                                                    @if($product->is_featured)
                                                        <span class="inline-flex items-center rounded-md bg-gradient-to-r from-amber-100 to-yellow-100 px-2 py-0.5 text-[9px] font-black text-amber-700 uppercase tracking-[0.1em] border border-amber-200/50 shadow-sm">
                                                            <svg class="w-2.5 h-2.5 mr-0.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                            Star
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td class="p-5 align-middle">
                                        <div class="flex flex-col gap-2">
                                            <span class="text-xs font-bold text-gray-900 bg-gray-50/80 w-fit px-3 py-1.5 rounded-xl border border-gray-100 shadow-sm">{{ $product->category->name ?? 'Uncategorized' }}</span>
                                            @if($product->brand)
                                                <span class="text-[10px] font-black text-gray-400 flex items-center gap-1 uppercase tracking-widest pl-1">
                                                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m3-4h1m-1 4h1m-5 8h8"></path></svg>
                                                    {{ $product->brand->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Inventory -->
                                    <td class="p-5 align-middle">
                                        <div class="flex flex-col gap-2">
                                            @if($product->manage_stock)
                                                @if($product->stock_on_hand <= 0)
                                                    <div class="inline-flex items-center gap-2 rounded-full bg-red-50/80 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/10 w-fit shadow-sm">
                                                        <div class="relative flex h-2 w-2">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                                        </div>
                                                        Out of Stock
                                                    </div>
                                                @elseif($product->stock_on_hand <= $product->reorder_level)
                                                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-50/80 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/10 w-fit shadow-sm">
                                                        <div class="relative flex h-2 w-2">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                                        </div>
                                                        Low: {{ floatval($product->stock_on_hand) }}
                                                    </div>
                                                @else
                                                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50/80 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/10 w-fit shadow-sm">
                                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                        Safe: {{ floatval($product->stock_on_hand) }}
                                                    </div>
                                                @endif
                                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest pl-3">{{ $product->unit_type }}</div>
                                            @else
                                                <span class="text-xs font-bold text-gray-400 bg-gray-50 px-3 py-1.5 rounded-xl w-fit border border-gray-100/50 border-dashed">Not Tracked</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Pricing -->
                                    <td class="p-5 text-right align-middle">
                                        @php
                                            $gstRate = 0;
                                            if ($product->taxClass) {
                                                $gstRate = $product->taxClass->rates->first()->rate ?? 0;
                                            } elseif ($product->tax_rate > 0) {
                                                $gstRate = $product->tax_rate;
                                            }
                                            $priceWithGst = $product->price + ($product->price * ($gstRate / 100));
                                        @endphp
                                        <div class="flex flex-col items-end gap-1.5">
                                            @if($gstRate > 0)
                                                <div class="flex items-baseline justify-end group cursor-default">
                                                    <span class="text-lg font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-gray-900 to-gray-600 transition-all duration-300 group-hover:from-indigo-600 group-hover:to-purple-600">₹{{ number_format($priceWithGst, 2) }}</span>
                                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1 bg-gray-100 px-1 py-0.5 rounded-md">w/ GST</span>
                                                </div>
                                                <span class="text-[10px] font-bold text-gray-400 border-b border-dashed border-gray-300">Net: ₹{{ number_format($product->price, 2) }}</span>
                                            @else
                                                <span class="text-lg font-black tracking-tighter text-gray-900">₹{{ number_format($product->price, 2) }}</span>
                                            @endif
                                            
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if($product->mrp > $product->price)
                                                    <span class="text-[10px] font-bold text-gray-400 line-through decoration-gray-300">MRP ₹{{ number_format($product->mrp, 2) }}</span>
                                                    <span class="text-[9px] font-black text-emerald-700 bg-emerald-100 ring-1 ring-emerald-500/20 px-1.5 py-0.5 rounded-md shadow-sm">-{{ round((($product->mrp - $product->price) / $product->mrp) * 100) }}% off</span>
                                                @endif
                                            </div>
                                            
                                            @hasrole('Super Admin')
                                            @if($product->cost_price > 0)
                                                <span class="text-[9px] font-bold font-mono text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-lg mt-1 ring-1 ring-inset ring-indigo-500/10">
                                                    Buy: ₹{{ number_format($product->cost_price, 2) }}
                                                </span>
                                            @endif
                                            @endhasrole
                                        </div>
                                    </td>

                                    <!-- Tax Details -->
                                    <td class="p-5 align-middle">
                                        @php
                                            $rate = 0;
                                            $className = 'None';
                                            if ($product->taxClass) {
                                                $rate = $product->taxClass->rates->first()->rate ?? 0;
                                                $className = $product->taxClass->name;
                                            } elseif ($product->tax_rate > 0) {
                                                $rate = $product->tax_rate;
                                                $className = 'Custom';
                                            }
                                        @endphp

                                        @if($rate > 0)
                                            <div class="flex flex-col gap-2">
                                                <span class="inline-flex items-center justify-center rounded-lg bg-blue-50 px-3 py-1.5 text-[10px] font-black text-blue-700 ring-1 ring-inset ring-blue-600/10 w-fit drop-shadow-sm border-b border-blue-200">
                                                    {{ $className }} ({{ floatval($rate) }}%)
                                                </span>
                                                <div class="flex items-center gap-1.5 text-[9px] font-black font-mono uppercase bg-gray-50/50 p-1 rounded-xl w-fit ring-1 ring-gray-100">
                                                    <div class="flex flex-col items-center bg-white rounded-lg px-2 py-1 shadow-sm">
                                                        <span class="text-gray-400 mb-0.5 tracking-widest text-[8px]">SGST</span>
                                                        <span class="text-blue-700">{{ number_format($rate / 2, 2) }}%</span>
                                                    </div>
                                                    <div class="flex flex-col items-center bg-white rounded-lg px-2 py-1 shadow-sm">
                                                        <span class="text-gray-400 mb-0.5 tracking-widest text-[8px]">CGST</span>
                                                        <span class="text-blue-700">{{ number_format($rate / 2, 2) }}%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-3 py-1.5 rounded-xl border border-gray-200 border-dashed">Exempt</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="p-5 text-right align-middle rounded-r-2xl">
                                        <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-40 sm:group-hover:opacity-100 transition-opacity duration-300">
                                            @can('products edit')
                                                <a href="{{ route('central.products.edit', $product) }}"
                                                    class="flex items-center justify-center w-9 h-9 rounded-xl text-gray-500 bg-gray-50 border border-gray-200/60 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-1 transition-all duration-300 ease-out" title="Edit Product">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </a>
                                            @endcan
                                            <button @click="viewingProduct = products.find(p => p.id == {{ $product->id }})"
                                                class="flex items-center justify-center w-9 h-9 rounded-xl text-gray-500 bg-gray-50 border border-gray-200/60 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 hover:shadow-lg hover:shadow-emerald-500/30 hover:-translate-y-1 transition-all duration-300 ease-out" title="View Specifics">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </button>
                                            @can('products delete')
                                                <form action="{{ route('central.products.destroy', $product) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to completely remove this product? This action is irreversible.');" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="flex items-center justify-center w-9 h-9 rounded-xl text-gray-500 bg-gray-50 border border-gray-200/60 hover:bg-rose-500 hover:text-white hover:border-rose-500 hover:shadow-lg hover:shadow-rose-500/30 hover:-translate-y-1 transition-all duration-300 ease-out" title="Permanently Delete">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-20 text-center text-gray-500 bg-white/50 backdrop-blur-md rounded-[2rem] border border-dashed border-gray-300/60 shadow-inner">
                                        <div class="flex flex-col items-center justify-center space-y-6">
                                            <div class="p-6 rounded-full bg-indigo-50 border border-indigo-100 shadow-inner relative">
                                                <div class="absolute inset-0 rounded-full animate-ping bg-indigo-200/50"></div>
                                                <svg class="h-16 w-16 text-indigo-400 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                            </div>
                                            <div class="space-y-2">
                                                <p class="text-2xl font-black text-gray-900 tracking-tight">No products discovered</p>
                                                <p class="text-sm font-medium text-gray-500 max-w-sm mx-auto leading-relaxed">Expand your global catalog by creating a new product entry. Information added instantly syncs.</p>
                                            </div>
                                            @can('products create')
                                                <a href="{{ route('central.products.create') }}" class="mt-2 inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 hover:shadow-indigo-300/50 transition-all duration-300 ease-out">
                                                    Create Your First Product
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer/Pagination -->
                @if($products->hasPages())
                    <div class="border-t border-gray-100/50 p-5 bg-white/40 backdrop-blur-md">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

            <!-- View Details Modal (Premium UI) -->
            <template x-teleport="body">
                <div x-show="viewingProduct" x-transition.opacity.duration.400ms
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-zinc-950/80 backdrop-blur-md p-4"
                    style="display: none;">
                    <div class="bg-white dark:bg-zinc-900 w-full max-w-4xl rounded-[40px] shadow-[0_0_100px_rgba(0,0,0,0.5)] border border-white/10 overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 fade-in duration-500 ease-out"
                        @click.away="viewingProduct = null">

                        <!-- Modal Header -->
                        <div class="px-10 pt-10 pb-6 relative overflow-hidden">
                            <div class="absolute -top-20 -right-20 size-60 bg-indigo-500/10 blur-[80px] rounded-full"></div>
                            <div class="relative z-10 flex justify-between items-start">
                                <div>
                                    <h3 class="font-black text-3xl tracking-tighter text-gray-900"
                                        x-text="viewingProduct?.name"></h3>
                                    <div class="flex items-center gap-3 mt-2">
                                        <span
                                            class="px-3 py-1 rounded-full bg-gray-100 text-[10px] font-black uppercase tracking-widest text-gray-500 border border-gray-200"
                                            x-text="viewingProduct?.sku"></span>
                                        <span
                                            class="px-3 py-1 rounded-full bg-indigo-50 text-[10px] font-black uppercase tracking-widest text-indigo-600 border border-indigo-100"
                                            x-text="viewingProduct?.category?.name || 'Uncategorized'"></span>
                                        <template x-if="viewingProduct?.is_active">
                                            <span
                                                class="px-3 py-1 rounded-full bg-emerald-50 text-[10px] font-black uppercase tracking-widest text-emerald-600 border border-emerald-100">Active</span>
                                        </template>
                                        <template x-if="!viewingProduct?.is_active">
                                            <span
                                                class="px-3 py-1 rounded-full bg-red-50 text-[10px] font-black uppercase tracking-widest text-red-600 border border-red-100">Inactive</span>
                                        </template>
                                    </div>
                                </div>
                                <button @click="viewingProduct = null"
                                    class="text-gray-400 hover:text-gray-900 p-2 hover:bg-gray-100 rounded-full transition-all duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-10 pb-10 overflow-y-auto custom-scrollbar">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                                <!-- Image Section -->
                                <div class="md:col-span-4">
                                    <div
                                        class="aspect-square rounded-[32px] overflow-hidden border border-gray-100 bg-gray-50 shadow-inner group">
                                        <img :src="viewingProduct?.image_url"
                                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                    </div>
                                    <div class="mt-6 p-6 rounded-[32px] bg-gray-50 border border-gray-100 space-y-4">
                                        <div class="flex justify-between items-center">
                                            <span
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Price</span>
                                            <span class="text-xl font-black text-indigo-600"
                                                x-text="formatPrice(viewingProduct?.price || 0)"></span>
                                        </div>
                                        <div class="flex justify-between items-center"
                                            x-show="viewingProduct?.mrp > viewingProduct?.price">
                                            <span
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest">MRP</span>
                                            <span class="text-sm font-bold text-gray-400 line-through"
                                                x-text="formatPrice(viewingProduct?.mrp || 0)"></span>
                                        </div>
                                        <div class="flex justify-between items-center"
                                            x-show="viewingProduct?.stock_on_hand !== null">
                                            <span
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Stock
                                                Status</span>
                                            <span class="text-sm font-black"
                                                :class="viewingProduct?.stock_on_hand > 10 ? 'text-emerald-600' : 'text-amber-600'"
                                                x-text="viewingProduct?.manage_stock ? parseFloat(viewingProduct?.stock_on_hand) + ' ' + (viewingProduct?.unit_type || '') : 'Not Tracked'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Details Section -->
                                <div class="md:col-span-8 space-y-8">
                                    <!-- Description -->
                                    <div class="space-y-3">
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Product
                                            Overview</h4>
                                        <div class="text-sm leading-relaxed text-gray-600 font-medium whitespace-pre-line"
                                            x-text="viewingProduct?.description || 'No description available.'"></div>
                                    </div>

                                    <!-- Specifications -->
                                    <div class="grid grid-cols-2 gap-6 pt-6 border-t border-gray-100">
                                        <div class="space-y-1">
                                            <span
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Brand</span>
                                            <p class="text-sm font-bold text-gray-900"
                                                x-text="viewingProduct?.brand?.name || 'N/A'"></p>
                                        </div>
                                        <div class="space-y-1">
                                            <span
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Packing
                                                Size</span>
                                            <p class="text-sm font-bold text-gray-900"
                                                x-text="viewingProduct?.packing_size || 'N/A'"></p>
                                        </div>
                                        <div class="space-y-1">
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">HSN
                                                Code</span>
                                            <p class="text-sm font-bold text-gray-900"
                                                x-text="viewingProduct?.hsn_code || 'N/A'"></p>
                                        </div>
                                        <div class="space-y-1">
                                            <span
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Product
                                                Type</span>
                                            <p class="text-sm font-bold text-gray-900"
                                                x-text="viewingProduct?.type || 'Standard'"></p>
                                        </div>
                                    </div>

                                    <!-- Agri Specifics -->
                                    <template x-if="viewingProduct?.technical_name || viewingProduct?.harvest_date">
                                        <div class="p-6 rounded-[32px] bg-indigo-50/50 border border-indigo-100 space-y-4">
                                            <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em]">
                                                Agri Details</h4>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div x-show="viewingProduct?.technical_name">
                                                    <span class="text-[9px] font-black text-gray-400 uppercase">Technical
                                                        Name</span>
                                                    <p class="text-xs font-bold text-gray-700 mt-0.5"
                                                        x-text="viewingProduct?.technical_name"></p>
                                                </div>
                                                <div x-show="viewingProduct?.application_method">
                                                    <span
                                                        class="text-[9px] font-black text-gray-400 uppercase">Application</span>
                                                    <p class="text-xs font-bold text-gray-700 mt-0.5"
                                                        x-text="viewingProduct?.application_method"></p>
                                                </div>
                                                <div x-show="viewingProduct?.harvest_date">
                                                    <span class="text-[9px] font-black text-gray-400 uppercase">Harvest
                                                        Date</span>
                                                    <p class="text-xs font-bold text-gray-700 mt-0.5"
                                                        x-text="formatDate(viewingProduct?.harvest_date)"></p>
                                                </div>
                                                <div x-show="viewingProduct?.expiry_date">
                                                    <span class="text-[9px] font-black text-gray-400 uppercase">Expiry
                                                        Date</span>
                                                    <p class="text-xs font-bold text-gray-700 mt-0.5"
                                                        x-text="formatDate(viewingProduct?.expiry_date)"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-10 py-8 border-t border-gray-100 bg-gray-50 flex justify-end">
                            <button @click="viewingProduct = null"
                                class="rounded-[22px] bg-gray-900 text-white px-8 py-4 text-xs font-black uppercase tracking-widest shadow-2xl hover:-translate-y-1 active:scale-95 transition-all duration-500">
                                Close Details
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- AJAX Script for smooth interactions (preserved) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('products-table-container');
            let loading = document.getElementById('table-loading');
            let searchTimeout;

            async function loadContent(url, pushState = true) {
                // Re-fetch loading element in case it was replaced
                loading = document.getElementById('table-loading');
                if (loading) loading.style.opacity = '1';
                
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('Network response was not ok');
                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('products-table-container');
                    
                    if (newContent) {
                        // Preserve search input state
                        const currentSearch = document.querySelector('input[name="search"]');
                        const isSearchFocused = currentSearch && document.activeElement === currentSearch;
                        const currentVal = currentSearch ? currentSearch.value : '';
                        let selectionStart = 0;
                        let selectionEnd = 0;
                        
                        if (isSearchFocused) {
                            selectionStart = currentSearch.selectionStart;
                            selectionEnd = currentSearch.selectionEnd;
                        }

                        // Apply current value to new content so it doesn't revert typed characters
                        if (currentSearch) {
                            const newSearch = newContent.querySelector('input[name="search"]');
                            if (newSearch) {
                                // Must use setAttribute so it survives innerHTML serialization
                                newSearch.setAttribute('value', currentVal);
                            }
                        }

                        // Replace HTML
                        container.innerHTML = newContent.innerHTML;
                        
                        // Update browser URL
                        if (pushState) window.history.pushState({}, '', url);
                        
                        // Reinitialize Alpine if present
                        if (typeof Alpine !== 'undefined') Alpine.initTree(container);

                        // Restore focus and cursor position
                        if (isSearchFocused) {
                            const replacedSearch = document.querySelector('input[name="search"]');
                            if (replacedSearch) {
                                // Ensure the value property is fully matched
                                replacedSearch.value = currentVal;
                                replacedSearch.focus();
                                replacedSearch.setSelectionRange(selectionStart, selectionEnd);
                            }
                        }
                    } else {
                        window.location.href = url;
                    }
                } catch (err) {
                    console.error('AJAX Load Error:', err);
                    window.location.href = url;
                } finally {
                    loading = document.getElementById('table-loading');
                    if (loading) loading.style.opacity = '0';
                }
            }

            window.addEventListener('popstate', () => loadContent(window.location.href, false));

            container.addEventListener('click', (e) => {
                const link = e.target.closest('a.page-link') || e.target.closest('.pagination a');
                if (link && container.contains(link) && link.href) {
                    e.preventDefault();
                    if (!link.href.includes('#')) {
                        loadContent(link.href);
                    }
                }
            });

            container.addEventListener('input', (e) => {
                if (e.target.name === 'search') {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const form = e.target.closest('form');
                        if (form) {
                            const url = new URL(form.action);
                            const params = new URLSearchParams(new FormData(form));
                            loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                        }
                    }, 400);
                }
            });

            container.addEventListener('change', (e) => {
                if (e.target.id === 'per_page') {
                    const form = e.target.closest('form');
                    if (form) {
                        const url = new URL(form.action);
                        const params = new URLSearchParams(new FormData(form));
                        loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                    }
                }
            });
        });
    </script>
@endsection