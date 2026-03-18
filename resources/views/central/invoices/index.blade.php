@extends('layouts.app')

@section('content')
    <div id="invoices-page-wrapper" class="flex flex-1 flex-col space-y-8 p-8 animate-in fade-in duration-500">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                    Invoices
                </h1>
                <p class="text-muted-foreground text-sm">
                    Track customer payments and invoice statuses.
                </p>
            </div>

            <div class="flex items-center p-1 bg-muted/50 rounded-xl border border-border/50 backdrop-blur-sm">
                <a href="{{ route('central.invoices.index') }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
                                                                       {{ request('status') === null ? 'bg-background text-foreground shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-foreground hover:bg-background/50' }}">
                    All Invoices
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-md bg-muted/80 text-[10px]">{{ $stats['all_count'] }}</span>
                </a>

                <div class="w-px h-4 bg-border/40 mx-1"></div>

                <a href="{{ route('central.invoices.index', ['status' => 'paid']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
                                                                       {{ request('status') === 'paid' ? 'bg-background text-emerald-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-emerald-600 hover:bg-background/50' }}">
                    Paid
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-md bg-emerald-50 text-[10px]">{{ $stats['paid_count'] }}</span>
                </a>

                <div class="w-px h-4 bg-border/40 mx-1"></div>

                <a href="{{ route('central.invoices.index', ['status' => 'pending']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
                                                                       {{ request('status') === 'pending' ? 'bg-background text-amber-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-amber-600 hover:bg-background/50' }}">
                    Pending
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-md bg-amber-50 text-[10px]">{{ $stats['pending_total_count'] }}</span>
                </a>

                <div class="w-px h-4 bg-border/40 mx-1"></div>

                <a href="{{ route('central.invoices.index', ['status' => 'overdue']) }}"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
                                                                       {{ request('status') === 'overdue' ? 'bg-background text-red-600 shadow-sm ring-1 ring-border/20' : 'text-muted-foreground hover:text-red-600 hover:bg-background/50' }}">
                    Overdue
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-md bg-red-50 text-[10px]">{{ $stats['overdue_tab_count'] }}</span>
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Total Receivable</span>
                </div>
                <div class="text-2xl font-black text-gray-900">₹{{ number_format($stats['total_receivable'], 2) }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Collected (Month)</span>
                </div>
                <div class="text-2xl font-black text-gray-900">₹{{ number_format($stats['paid_this_month'], 2) }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-red-50 text-red-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Overdue (Qty)</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['overdue_count'] }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-orange-50 text-orange-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Overdue (Amt)</span>
                </div>
                <div class="text-2xl font-black text-gray-900">₹{{ number_format($stats['overdue_amount'], 2) }}</div>
            </div>
        </div>

        <div id="invoices-table-container" x-data="{
                selected: [],
                allIds: {{ json_encode($invoices->pluck('id')) }},
                selectAll: false,
                showPaymentModal: false,
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
                    let url = new URL('{{ route('central.invoices.export') }}', window.location.origin);
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
                    if (event?.target?.name === 'start_date') {
                        url.searchParams.set('start_date', event.target.value);
                    }
                    if (event?.target?.name === 'end_date') {
                        url.searchParams.set('end_date', event.target.value);
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

            <!-- Toolbar -->
            <div
                class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-6 py-6 px-5 bg-white border border-gray-100 rounded-3xl shadow-sm mb-8 flex-wrap">
                <!-- Left Section: Selection, Bulk Actions & Date Range -->
                <div class="flex flex-col md:flex-row md:items-center gap-4 w-full xl:w-auto flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center p-2 rounded-xl bg-gray-50 border border-gray-100 shadow-sm hover:bg-gray-100 transition-colors cursor-pointer"
                            title="Select All on Page">
                            <input type="checkbox" x-on:change="toggleAll()" x-model="selectAll"
                                class="h-5 w-5 rounded-md border-gray-200 text-primary focus:ring-primary/20 bg-white cursor-pointer transition-all checked:bg-primary checked:border-primary">
                        </div>

                        <div x-cloak x-show="selected.length > 0"
                            class="flex items-center gap-2 animate-in fade-in slide-in-from-left-4">
                            <div
                                class="px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs font-bold shadow-sm whitespace-nowrap">
                                <span x-text="selected.length"></span> Selected
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block h-8 w-px bg-gray-100 mx-1"></div>

                    <!-- Date Range -->
                    <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-1.5 border border-gray-100 shadow-sm w-full md:w-auto overflow-x-auto">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <input type="date" name="start_date" @change="performSearch" value="{{ request('start_date') }}"
                               class="border-none bg-transparent p-0 text-xs font-bold focus:ring-0 min-w-[100px]">
                        <span class="text-gray-300">-</span>
                        <input type="date" name="end_date" @change="performSearch" value="{{ request('end_date') }}"
                               class="border-none bg-transparent p-0 text-xs font-bold focus:ring-0 min-w-[100px]">
                    </div>
                </div>

                <!-- Right Section: Filters, Search & Actions -->
                <div class="flex flex-col lg:flex-row items-center gap-4 w-full xl:w-auto flex-wrap">
                    <!-- Per Page & Search -->
                    <div class="flex items-center gap-3 w-full lg:w-auto">
                        <!-- Per Page -->
                        <div class="flex items-center gap-2 px-3 h-[42px] rounded-xl bg-white border border-gray-100 shadow-sm shrink-0">
                            <label for="per_page" class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Show</label>
                            <select id="per_page" name="per_page" @change="performSearch"
                                class="border-none bg-transparent p-0 pr-8 text-sm font-bold focus:ring-0 cursor-pointer">
                                @foreach([10, 25, 50] as $size)
                                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="relative flex-1 lg:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoices..."
                                @input.debounce.500ms="performSearch"
                                class="flex h-[42px] w-full rounded-xl border border-gray-100 bg-white pl-10 pr-4 py-2 text-sm font-medium shadow-sm focus:ring-2 focus:ring-primary/10 focus:border-primary/30 transition-all outline-none">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 w-full lg:w-auto">
                        <button type="button" @click="exportSelected()"
                            class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2.5 px-5 h-[42px] rounded-xl bg-orange-50 text-orange-700 border border-orange-100 shadow-sm hover:bg-orange-100 hover:shadow-md active:scale-[0.98] transition-all text-sm font-bold whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Export
                        </button>

                        <button type="button" @click="$dispatch('open-upload-modal')"
                            class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2.5 px-5 h-[42px] rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:shadow-md active:scale-[0.98] text-sm font-bold transition-all shadow-sm whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Import
                        </button>
                    </div>
                </div>
            </div>

            <!-- List Container -->
            <div id="invoices-list-container">
                @include('central.invoices.partials.invoices-list')
            </div>

            <!-- 🔹 Bulk Upload Modal -->
            <div x-data="{ showUploadModal: false }" @open-upload-modal.window="showUploadModal = true"
                x-show="showUploadModal" style="display: none;"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

                <div @click.away="showUploadModal = false"
                    class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">

                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold flex items-center gap-2 text-gray-900">
                            <svg class="size-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Upload Bulk Payments
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Upload an Excel or CSV file to bulk process
                            invoice payments.</p>
                    </div>

                    <form action="{{ route('central.invoices.bulk-upload') }}" method="POST" enctype="multipart/form-data">
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
                                <p class="text-sm font-medium text-gray-900">Click to upload or drag and drop</p>
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
                                        <li>Optional: <strong>Method</strong> <span class="text-[10px] opacity-80">(cash,
                                                bank_transfer, online, cheque)</span></li>
                                        <li>Optional: <strong>Transaction ID / Reference</strong></li>
                                        <li>Optional: <strong>Notes</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                            <a href="{{ route('central.invoices.download-template') }}"
                                class="px-4 py-2 text-sm font-medium rounded-lg text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors mr-auto flex items-center gap-2">
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
                                class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                                Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection