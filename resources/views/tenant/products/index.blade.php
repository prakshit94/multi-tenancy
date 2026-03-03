<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-bold">Product List</h3>
                        <a href="{{ route('tenant.products.create') }}"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add New Product
                        </a>
                    </div>

                    <div class="overflow-x-auto" x-data="{ 
                        viewingProduct: null,
                        products: {{ \Illuminate\Support\Js::from($products->items()) }},
                        formatPrice(price) {
                            return 'Rs ' + parseFloat(price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        },
                        formatDate(dateString) {
                            if (!dateString) return 'N/A';
                            return new Date(dateString).toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric' });
                        }
                    }">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Image</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                class="h-10 w-10 rounded-full object-cover border">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $product->sku }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->category->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Rs
                                                {{ number_format($product->price, 2) }}
                                            </div>
                                            @if($product->default_discount_value > 0)
                                                <span class="text-xs text-red-500">
                                                    -{{ $product->default_discount_type == 'percent' ? $product->default_discount_value . '%' : 'Rs ' . number_format($product->default_discount_value, 2) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->stock_on_hand > 10 ? 'bg-green-100 text-green-800' : ($product->stock_on_hand > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $product->stock_on_hand }} {{ $product->unit_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($product->is_featured)
                                                <span class="ml-1 text-xs text-yellow-500">★</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <button
                                                    @click="viewingProduct = products.find(p => p.id == {{ $product->id }})"
                                                    class="text-blue-600 hover:text-blue-900" title="View Details">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                                <a href="{{ route('tenant.products.edit', $product) }}"
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No products found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- View Details Modal -->
                        <template x-teleport="body">
                            <div x-show="viewingProduct" style="display: none;" class="relative z-[100]"
                                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <!-- Backdrop -->
                                <div x-show="viewingProduct" x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                    aria-hidden="true"></div>

                                <!-- Scroll Container -->
                                <div class="fixed inset-0 z-10 overflow-y-auto">
                                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                                        @click.self="viewingProduct = null">
                                        <!-- Panel -->
                                        <div x-show="viewingProduct" x-transition:enter="ease-out duration-300"
                                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                            x-transition:leave="ease-in duration-200"
                                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                            class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                                            @click.stop>

                                            <div class="absolute top-0 right-0 pt-4 pr-4">
                                                <button @click="viewingProduct = null" type="button"
                                                    class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none">
                                                    <span class="sr-only">Close</span>
                                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                        <h3 class="text-2xl leading-6 font-bold text-gray-900 mb-1"
                                                            id="modal-title" x-text="viewingProduct?.name"></h3>
                                                        <div class="flex items-center gap-2 mb-6">
                                                            <span
                                                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                                                x-text="viewingProduct?.sku"></span>
                                                            <span
                                                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                                                                x-text="viewingProduct?.type"></span>
                                                            <span x-show="viewingProduct?.is_active"
                                                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                                            <span x-show="!viewingProduct?.is_active"
                                                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                                        </div>

                                                        <!-- Grid Layout -->
                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                                            <!-- Column 1: Images & Key Stats -->
                                                            <div class="md:col-span-1 space-y-6">
                                                                <!-- Main Image (using image_url logic from model) -->
                                                                <div
                                                                    class="aspect-square rounded-xl bg-gray-100 overflow-hidden border border-gray-200">
                                                                    <img :src="viewingProduct?.image_url"
                                                                        class="w-full h-full object-cover">
                                                                </div>

                                                                <!-- Pricing Card -->
                                                                <div
                                                                    class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                                                    <h4
                                                                        class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                                                                        Pricing</h4>
                                                                    <div class="space-y-2">
                                                                        <div class="flex justify-between items-center">
                                                                            <span class="text-sm text-gray-600">Selling
                                                                                Price</span>
                                                                            <span
                                                                                class="text-lg font-bold text-indigo-700"
                                                                                x-text="formatPrice(viewingProduct?.price || 0)"></span>
                                                                        </div>
                                                                        <div class="flex justify-between items-center"
                                                                            x-show="viewingProduct?.mrp > viewingProduct?.price">
                                                                            <span
                                                                                class="text-sm text-gray-600">MRP</span>
                                                                            <span
                                                                                class="text-sm text-gray-400 line-through"
                                                                                x-text="formatPrice(viewingProduct?.mrp || 0)"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Column 2 & 3: Details -->
                                                            <div class="md:col-span-2 space-y-6">
                                                                <!-- Product Description -->
                                                                <div>
                                                                    <h4
                                                                        class="text-lg font-semibold text-gray-900 mb-2">
                                                                        Product Description</h4>
                                                                    <div class="text-sm text-gray-600 leading-relaxed bg-gray-50 p-4 rounded-xl border border-gray-100"
                                                                        x-text="viewingProduct?.description || 'No description provided.'">
                                                                    </div>
                                                                </div>

                                                                <!-- Basic Details -->
                                                                <div
                                                                    class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4 text-sm mt-4">
                                                                    <div class="sm:col-span-1">
                                                                        <dt class="font-medium text-gray-500">Category
                                                                        </dt>
                                                                        <dd class="mt-1 text-gray-900"
                                                                            x-text="viewingProduct?.category?.name || 'Uncategorized'">
                                                                        </dd>
                                                                    </div>
                                                                    <div class="sm:col-span-1">
                                                                        <dt class="font-medium text-gray-500">Brand</dt>
                                                                        <dd class="mt-1 text-gray-900"
                                                                            x-text="viewingProduct?.brand?.name || 'N/A'">
                                                                        </dd>
                                                                    </div>
                                                                    <div class="sm:col-span-1">
                                                                        <dt class="font-medium text-gray-500">Unit Type
                                                                        </dt>
                                                                        <dd class="mt-1 text-gray-900"
                                                                            x-text="viewingProduct?.unit_type"></dd>
                                                                    </div>
                                                                    <div class="sm:col-span-1">
                                                                        <dt class="font-medium text-gray-500">Packing
                                                                            Size</dt>
                                                                        <dd class="mt-1 text-gray-900"
                                                                            x-text="viewingProduct?.packing_size || 'N/A'">
                                                                        </dd>
                                                                    </div>
                                                                </div>

                                                                <!-- Agri Details (If applicable) -->
                                                                <div class="border-t border-gray-100 pt-4"
                                                                    x-show="viewingProduct?.technical_name || viewingProduct?.harvest_date">
                                                                    <h4
                                                                        class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wider">
                                                                        Agri Details</h4>
                                                                    <dl
                                                                        class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4 text-sm">
                                                                        <div class="sm:col-span-2"
                                                                            x-show="viewingProduct?.technical_name">
                                                                            <dt class="font-medium text-gray-500">
                                                                                Technical Name</dt>
                                                                            <dd class="mt-1 text-gray-900"
                                                                                x-text="viewingProduct?.technical_name">
                                                                            </dd>
                                                                        </div>
                                                                        <div class="sm:col-span-1"
                                                                            x-show="viewingProduct?.harvest_date">
                                                                            <dt class="font-medium text-gray-500">
                                                                                Harvest Date</dt>
                                                                            <dd class="mt-1 text-gray-900"
                                                                                x-text="formatDate(viewingProduct?.harvest_date)">
                                                                            </dd>
                                                                        </div>
                                                                        <div class="sm:col-span-1"
                                                                            x-show="viewingProduct?.expiry_date">
                                                                            <dt class="font-medium text-gray-500">Expiry
                                                                                Date</dt>
                                                                            <dd class="mt-1 text-gray-900"
                                                                                x-text="formatDate(viewingProduct?.expiry_date)">
                                                                            </dd>
                                                                        </div>
                                                                    </dl>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <button type="button" @click="viewingProduct = null"
                                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>