<x-app-layout>
    <x-slot name="header">
        <div x-data="{
            selected: [],
            allIds: {{ json_encode(isset($invoices) ? $invoices->pluck('id') : []) }},
            selectAll: false,
            toggleAll() {
                if (this.selectAll) {
                    this.selected = [...this.allIds];
                } else {
                    this.selected = [];
                }
            },
            init() {
                this.$watch('selected', value => {
                    this.selectAll = value.length === this.allIds.length && this.allIds.length > 0;
                });
            },
            exportSelected() {
                let url = new URL('{{ route('tenant.invoices.export') }}', window.location.origin);
                url.searchParams.set('format', 'xlsx');

                let currentUrl = new URL(window.location.href);
                currentUrl.searchParams.forEach((value, key) => {
                    if (key !== 'page' && key !== 'format' && key !== 'ids') {
                        url.searchParams.set(key, value);
                    }
                });

                if (this.selected.length > 0) {
                    url.searchParams.set('ids', this.selected.join(','));
                }

                window.location.href = url.toString();
            },
            async performSearch(event) {
                const url = new URL(window.location.href);
                
                if (event?.target?.name === 'search') {
                    url.searchParams.set('search', event.target.value);
                }
                
                if (event?.target?.name === 'per_page') {
                    url.searchParams.set('per_page', event.target.value);
                } else {
                    const perPageSelect = document.getElementById('per_page');
                    if(perPageSelect) {
                        url.searchParams.set('per_page', perPageSelect.value);
                    }
                }

                url.searchParams.delete('page'); 
                
                const newUrl = url.toString();
                window.history.pushState({}, '', newUrl);

                try {
                    const response = await fetch(newUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (response.ok) {
                        const html = await response.text();
                        document.getElementById('invoices-list-container').innerHTML = html;
                    }
                } catch (error) {
                    console.error('Search Failed:', error);
                }
            }
        }">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 mt-6">
                <div class="space-y-1">
                    <h1
                        class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                        Invoices
                    </h1>
                    <p class="text-muted-foreground text-sm font-medium">Manage and track your customer billing.</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Removed Import CSV from Header to place it in the Toolbar for layout consistency -->
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div
                class="sticky top-0 z-30 bg-background/95 backdrop-blur-xl border-b border-border/40 -mx-6 px-6 md:-mx-8 md:px-8 mb-6 pt-2">
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar pb-0">
                    @php
                        $navItems = [
                            ['label' => 'All Invoices', 'route' => route('tenant.invoices.index', ['status' => 'all']), 'active' => request('status') === 'all' || !request()->has('status')],
                            ['label' => 'Paid', 'route' => route('tenant.invoices.index', ['status' => 'paid']), 'active' => request('status') === 'paid', 'color' => 'green'],
                            ['label' => 'Partial', 'route' => route('tenant.invoices.index', ['status' => 'partial']), 'active' => request('status') === 'partial', 'color' => 'blue'],
                            ['label' => 'Pending', 'route' => route('tenant.invoices.index', ['status' => 'pending']), 'active' => request('status') === 'pending', 'color' => 'amber'],
                            ['label' => 'Overdue', 'route' => route('tenant.invoices.index', ['status' => 'overdue']), 'active' => request('status') === 'overdue', 'color' => 'red'],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        <a href="{{ $item['route'] }}"
                            class="relative px-5 py-3 text-sm font-medium transition-all duration-200 whitespace-nowrap {{ $item['active'] ? 'text-foreground' : 'text-muted-foreground hover:text-foreground' }}">
                            {{ $item['label'] }}
                            @if($item['active'])
                                <div
                                    class="absolute bottom-0 left-0 w-full h-0.5 bg-primary rounded-t-full shadow-[0_-2px_6px_rgba(0,0,0,0.1)]">
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Toolbar -->
            <div
                class="flex flex-col xl:flex-row items-center justify-between gap-4 py-4 px-2 bg-background/50 backdrop-blur-sm border-y border-border/40 mb-6">
                <!-- Left: Selection & Bulk Actions -->
                <div class="flex items-center gap-3 w-full xl:w-auto">
                    <div class="flex items-center justify-center p-2 rounded-xl bg-white border border-input shadow-sm hover:bg-muted/50 transition-colors cursor-pointer"
                        title="Select All on Page">
                        <input type="checkbox" x-on:change="toggleAll()" x-model="selectAll"
                            class="h-5 w-5 rounded-md border-input text-primary focus:ring-primary/20 bg-background cursor-pointer transition-all checked:bg-primary checked:border-primary">
                    </div>

                    <div x-cloak x-show="selected.length > 0"
                        class="flex items-center gap-2 animate-in fade-in slide-in-from-left-4">
                        <div
                            class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-bold shadow-sm whitespace-nowrap">
                            <span x-text="selected.length"></span> Selected
                        </div>
                    </div>
                </div>

                <!-- Right: Filters, Search & Actions -->
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
                    <!-- Per Page -->
                    <div
                        class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-input shadow-sm h-[42px] shrink-0">
                        <label for="per_page"
                            class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Show</label>
                        <select id="per_page" name="per_page" @change="performSearch"
                            class="border-none bg-transparent p-0 pr-8 text-sm font-semibold focus:ring-0 cursor-pointer">
                            @foreach([5, 10, 15] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 5) == $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="relative w-full sm:w-72">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-foreground">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search invoices..." @input.debounce.500ms="performSearch"
                            class="flex h-[42px] w-full rounded-xl border border-input bg-white pl-9 pr-3 py-2 text-sm font-medium shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all outline-none">
                    </div>

                    <!-- Action Buttons Group -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="button" @click="exportSelected()"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 h-[42px] rounded-xl bg-orange-100/80 text-orange-700 border border-orange-200/50 shadow-sm hover:bg-orange-200 hover:shadow-md transition-all text-sm font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Export
                        </button>

                        <button type="button" @click="$dispatch('open-upload-modal')"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 h-[42px] rounded-xl border border-emerald-200/50 bg-emerald-100/50 hover:bg-emerald-100 hover:shadow-md text-sm font-bold transition-all shadow-sm text-emerald-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Import
                        </button>
                    </div>
                </div>
            </div>

            <div id="invoices-list-container">
                @include('tenant.invoices.partials.invoices-list')
            </div>



            <!-- 🔹 Bulk Upload Modal -->
            <div x-data="{ showUploadModal: false }" @open-upload-modal.window="showUploadModal = true"
                x-show="showUploadModal" style="display: none;"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

                <div @click.away="showUploadModal = false"
                    class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">

                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold flex items-center gap-2 text-gray-900">
                            <svg class="size-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Upload Bulk Payments
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Upload an Excel or CSV file to bulk process
                            invoice
                            payments.</p>
                    </div>

                    <form action="{{ route('tenant.invoices.bulk-upload') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-gray-50 transition-colors cursor-pointer relative">
                                <input type="file" name="payment_file" accept=".csv, .xlsx, .xls" required
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <svg class="size-10 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-900">Click to upload or drag and drop
                                </p>
                                <p class="text-xs text-gray-500 mt-1">CSV or Excel (MAX. 5MB)</p>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-lg flex items-start gap-3 border border-blue-100">
                                <svg class="size-5 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium mb-1">File Requirements</p>
                                    <ul class="list-disc list-inside space-y-1 text-xs text-blue-700/80">
                                        <li>Must contain: <strong>Invoice #</strong></li>
                                        <li>Must contain: <strong>Amount</strong></li>
                                        <li>Optional: <strong>Method</strong> <span
                                                class="text-[10px] opacity-80">(cash,
                                                bank_transfer, online,
                                                cheque)</span></li>
                                        <li>Optional: <strong>Transaction ID / Reference</strong></li>
                                        <li>Optional: <strong>Notes</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                            <a href="{{ route('tenant.invoices.download-template') }}"
                                class="px-4 py-2 text-sm font-medium rounded-lg text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors mr-auto flex items-center gap-2">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Template
                            </a>
                            <button type="button" @click="showUploadModal = false"
                                class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white hover:bg-gray-100 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                                Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        </div>
</x-app-layout>