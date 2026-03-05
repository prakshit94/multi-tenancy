@extends('layouts.app')

@section('content')
    <div id="products-page-wrapper"
        class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500 selection:bg-indigo-100 selection:text-indigo-800">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    Products
                </h1>
                <p class="text-gray-500 text-sm">Manage global product catalog, pricing, and stock inventory.</p>
            </div>

            <!-- Filter Pills -->
            <div class="flex items-center p-1 bg-white/60 rounded-xl border border-gray-200/50 backdrop-blur-sm shadow-sm">
                <a href="{{ route('central.products.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === null ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                    All Products
                </a>
                <div class="w-px h-4 bg-gray-200 mx-1"></div>
                <a href="{{ route('central.products.index', ['status' => 'active']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request('status') === 'active' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Active
                </a>
                <div class="w-px h-4 bg-gray-200 mx-1"></div>
                <a href="{{ route('central.products.index', ['stock' => 'low']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request('stock') === 'low' ? 'bg-white text-amber-600 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-amber-600 hover:bg-gray-50' }}">
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
            }">
            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <!-- Selection Counter -->
                    <div x-cloak x-show="selected.length > 0" x-transition.opacity.duration.300ms
                        class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4">
                        <div
                            class="px-3 py-2 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold shadow-sm">
                            <span x-text="selected.length"></span> selected
                        </div>
                    </div>

                    <!-- Search Box -->
                    <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex-1 sm:flex-none">
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        @if(request('stock')) <input type="hidden" name="stock" value="{{ request('stock') }}"> @endif
                        @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                        @endif

                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search products, SKU..."
                                class="block w-full sm:w-72 rounded-xl border-gray-200 pl-10 pr-3 py-2.5 text-sm placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all bg-white/80 backdrop-blur-sm hover:bg-white">
                        </div>
                    </form>
                </div>

                @can('products create')
                    <a href="{{ route('central.products.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:scale-[1.02] active:scale-95 transition-all duration-200 w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Add Product</span>
                    </a>
                @endcan
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative">
                <!-- Loading Overlay -->
                <div id="table-loading"
                    class="absolute inset-0 z-50 bg-white/60 backdrop-blur-[2px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
                    <div
                        class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-600 border-t-transparent shadow-lg">
                    </div>
                </div>

                <!-- Pagination/Count Header -->
                <div
                    class="border-b border-gray-100 p-4 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-500">
                        Showing <span class="font-semibold text-gray-900">{{ $products->firstItem() ?? 0 }}</span> to <span
                            class="font-semibold text-gray-900">{{ $products->lastItem() ?? 0 }}</span> of <span
                            class="font-semibold text-gray-900">{{ $products->total() }}</span> products
                    </div>

                    <div class="flex items-center gap-3">
                        <form id="per-page-form" method="GET" action="{{ url()->current() }}"
                            class="flex items-center gap-2">
                            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach

                            <label for="per_page" class="text-xs font-medium text-gray-500 uppercase tracking-wider">Per
                                Page</label>
                            <select name="per_page" id="per_page"
                                class="form-select block w-20 h-9 rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="p-4 w-[40px]">
                                    <input type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                        @click="selected = $event.target.checked ? [{{ $products->pluck('id')->join(',') }}] : []">
                                </th>
                                <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Product Info
                                </th>
                                <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category &
                                    Brand</th>
                                <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Inventory</th>
                                <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">
                                    Pricing</th>
                                <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tax Details
                                </th>
                                <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($products as $product)
                                <tr class="group hover:bg-gray-50/80 transition-colors duration-200">
                                    <td class="p-4 align-top pt-5">
                                        <input type="checkbox" value="{{ $product->id }}" x-model="selected"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                    </td>

                                    <!-- Product Info -->
                                    <td class="p-4">
                                        <div class="flex gap-4">
                                            <div
                                                class="h-16 w-16 flex-shrink-0 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden shadow-sm">
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                    class="h-full w-full object-cover">
                                            </div>
                                            <div class="space-y-1">
                                                <div
                                                    class="font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                    {{ $product->name }}</div>
                                                <div class="flex items-center gap-2 text-xs text-gray-500 font-mono">
                                                    {{ $product->sku }}
                                                </div>
                                                <div class="flex gap-2 mt-1">
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">{{ ucfirst($product->type) }}</span>
                                                    @if($product->is_featured)
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">Featured</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td class="p-4 align-top pt-5">
                                        <div class="flex flex-col gap-1">
                                            <span
                                                class="text-sm text-gray-900">{{ $product->category->name ?? 'Uncategorized' }}</span>
                                            @if($product->brand)
                                                <span class="text-xs text-gray-500">{{ $product->brand->name }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Inventory -->
                                    <td class="p-4 align-top pt-5">
                                        <div class="space-y-2">
                                            @if($product->manage_stock)
                                                @if($product->stock_on_hand <= 0)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span> Out of Stock
                                                    </span>
                                                @elseif($product->stock_on_hand <= $product->reorder_level)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span> Low Stock:
                                                        {{ floatval($product->stock_on_hand) }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> In Stock:
                                                        {{ floatval($product->stock_on_hand) }}
                                                    </span>
                                                @endif
                                                <div class="text-xs text-gray-500 pl-1">{{ $product->unit_type }}</div>
                                            @else
                                                <span class="text-xs text-gray-500 italic">Stock Not Tracked</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Pricing -->
                                    <td class="p-4 text-right align-top pt-5">
                                        @php
                                            $gstRate = 0;
                                            if ($product->taxClass) {
                                                $gstRate = $product->taxClass->rates->first()->rate ?? 0;
                                            } elseif ($product->tax_rate > 0) {
                                                $gstRate = $product->tax_rate;
                                            }
                                            $priceWithGst = $product->price + ($product->price * ($gstRate / 100));
                                        @endphp
                                        <div class="flex flex-col items-end">
                                            <span
                                                class="text-sm font-bold text-gray-900">₹{{ number_format($product->price, 2) }}</span>
                                            @if($gstRate > 0)
                                                <span
                                                    class="text-[10px] font-medium text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded mt-0.5">₹{{ number_format($priceWithGst, 2) }}
                                                    Incl. GST</span>
                                            @endif
                                            @if($product->mrp > $product->price)
                                                <span
                                                    class="text-xs text-gray-400 line-through mt-0.5">₹{{ number_format($product->mrp, 2) }}</span>
                                            @endif
                                            @hasrole('Super Admin')
                                            @if($product->cost_price > 0)
                                                <span class="text-[10px] text-gray-400 mt-1">Cost:
                                                    ₹{{ number_format($product->cost_price, 2) }}</span>
                                            @endif
                                            @endhasrole
                                        </div>
                                    </td>

                                    <!-- Tax Details (Premium Bifurcation) -->
                                    <td class="p-4 align-top pt-5">
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
                                            <div class="flex flex-col gap-1.5">
                                                <!-- Tax Class Badge -->
                                                <span
                                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 w-fit">
                                                    {{ $className }} ({{ floatval($rate) }}%)
                                                </span>

                                                <!-- Bifurcation Display -->
                                                <div class="flex items-center gap-2 text-[10px] font-mono leading-none">
                                                    <div
                                                        class="flex flex-col items-center bg-gray-50 rounded border border-gray-100 px-1.5 py-1">
                                                        <span class="text-gray-500 mb-0.5">SGST</span>
                                                        <span
                                                            class="font-bold text-gray-700">{{ number_format($rate / 2, 2) }}%</span>
                                                    </div>
                                                    <div class="text-gray-300">|</div>
                                                    <div
                                                        class="flex flex-col items-center bg-gray-50 rounded border border-gray-100 px-1.5 py-1">
                                                        <span class="text-gray-500 mb-0.5">CGST</span>
                                                        <span
                                                            class="font-bold text-gray-700">{{ number_format($rate / 2, 2) }}%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Exempt / Zero Rated</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="p-4 text-right align-middle">
                                        <div class="flex items-center justify-end gap-2 text-right">
                                            @can('products edit')
                                                <a href="{{ route('central.products.edit', $product) }}"
                                                    class="text-gray-400 hover:text-indigo-600 transition-colors p-1.5 rounded-lg hover:bg-indigo-50">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </a>
                                            @endcan
                                            <button @click="viewingProduct = products.find(p => p.id == {{ $product->id }})"
                                                class="text-gray-400 hover:text-blue-600 transition-colors p-1.5 rounded-lg hover:bg-blue-50"
                                                title="View Details">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            @can('products delete')
                                                <form action="{{ route('central.products.destroy', $product) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this product?');"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-gray-400 hover:text-red-600 transition-colors p-1.5 rounded-lg hover:bg-red-50">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p class="text-lg font-medium text-gray-900">No products found</p>
                                            <p class="text-sm mt-1">Get started by creating a new product.</p>
                                            @can('products create')
                                                <a href="{{ route('central.products.create') }}"
                                                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                                    Add Product
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
                    <div class="border-t border-gray-100 p-4 bg-gray-50/50">
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
                    const newContent = doc.getElementById('products-table-container');
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
                const link = e.target.closest('a.page-link') || e.target.closest('.pagination a');
                if (link && container.contains(link) && link.href) {
                    e.preventDefault();
                    loadContent(link.href);
                }
            });

            container.addEventListener('input', (e) => {
                if (e.target.name === 'search') {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const form = e.target.closest('form');
                        const url = new URL(form.action);
                        const params = new URLSearchParams(new FormData(form));
                        loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                    }, 400);
                }
            });

            container.addEventListener('change', (e) => {
                if (e.target.id === 'per_page') {
                    const form = e.target.closest('form');
                    const url = new URL(form.action);
                    const params = new URLSearchParams(new FormData(form));
                    loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                }
            });
        });
    </script>
@endsection