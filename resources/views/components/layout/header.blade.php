@props(['hideSearch' => false, 'hideDashboardLink' => false, 'hideSidebarToggle' => false, 'pageTitle' => ''])
@php
    $activePageName = 'Dashboard';
    $activePageUrl = '/dashboard';
    $activeGroupName = 'Home';

    $sections = [
        'products*' => ['Products', tenant() ? route('tenant.products.index') : route('central.products.index'), 'Catalog'],
        'categories*' => ['Categories', tenant() ? route('tenant.categories.index') : route('central.categories.index'), 'Catalog'],
        'brands*' => ['Brands', tenant() ? route('tenant.brands.index') : route('central.brands.index'), 'Catalog'],
        'units*' => ['Units', tenant() ? route('tenant.units.index') : route('central.units.index'), 'Catalog'],
        'product-types*' => ['Product Types', tenant() ? route('tenant.product_types.index') : route('central.product_types.index'), 'Catalog'],
        'processing/orders*' => ['Processing', tenant() ? route('tenant.processing.orders.index') : route('central.processing.orders.index'), 'Warehouse'],
        'processing/returns*' => ['Returns (RMA)', tenant() ? route('tenant.processing.returns.index') : route('central.processing.returns.index'), 'Warehouse'],
        'orders*' => ['Orders', tenant() ? route('tenant.orders.index') : route('central.orders.index'), 'Sales'],
        'invoices*' => ['Invoices', tenant() ? route('tenant.invoices.index') : route('central.invoices.index'), 'Sales'],
        'shipments*' => ['Shipments', tenant() ? route('tenant.shipments.index') : route('central.shipments.index'), 'Sales'],
        'returns*' => ['Returns', tenant() ? route('tenant.returns.index') : route('central.returns.index'), 'Sales'],
        'complaints*' => ['Complaints', tenant() ? route('tenant.complaints.index') : route('central.complaints.index'), 'Sales'],
        'expenses*' => ['Expenses', tenant() ? route('tenant.expenses.index') : route('central.expenses.index'), 'Finance'],
        'reports/profit-loss*' => ['Profit & Loss', tenant() ? route('tenant.reports.profit-loss') : route('central.reports.profit-loss'), 'Finance'],
        'reports*' => ['Reports', '/reports', 'Overview'],
        'customers*' => ['Customers', '/customers', 'Customers'],
        'users*' => ['Users', '/users', 'System'],
        'roles*' => ['Roles', '/roles', 'System'],
        'permissions*' => ['Permissions', '/permissions', 'System'],
        'villages*' => ['Villages', '/villages', 'System'],
        'tenants*' => ['Tenant Workspaces', '/tenants', 'System'],
        'settings*' => ['Settings', '/settings', 'System'],
        'activity-logs*' => ['Audit Logs', '/activity-logs', 'System'],
        'purchase-orders*' => ['Purchase Orders', tenant() ? route('tenant.purchase-orders.index') : route('central.purchase-orders.index'), 'Operations'],
        'inventory*' => ['Inventory', tenant() ? route('tenant.inventory.index') : route('central.inventory.index'), 'Operations'],
        'stock-transfers*' => ['Stock Transfers', tenant() ? route('tenant.stock-transfers.index') : route('central.stock-transfers.index'), 'Operations'],
        'suppliers*' => ['Suppliers', tenant() ? route('tenant.suppliers.index') : route('central.suppliers.index'), 'Operations'],
        'warehouses*' => ['Warehouses', tenant() ? route('tenant.warehouses.index') : route('central.warehouses.index'), 'Operations'],
    ];

    foreach ($sections as $pattern => $data) {
        if (request()->is($pattern)) {
            $activePageName = $data[0];
            $activePageUrl = $data[1];
            $activeGroupName = $data[2] ?? 'Home';
            break;
        }
    }

    if (request()->routeIs('*.orders.verification.*')) {
        $activePageName = 'Verification';
        $activePageUrl = tenant() ? route('tenant.orders.verification.index') : route('central.orders.verification.index');
        $activeGroupName = 'Sales';
    } elseif (request()->routeIs('*.orders.tracking.*')) {
        $activePageName = 'Tracking';
        $activePageUrl = tenant() ? route('tenant.orders.tracking.index') : route('central.orders.tracking.index');
        $activeGroupName = 'Sales';
    }
@endphp
<header
    class="sticky top-0 z-40 flex h-20 w-full items-center justify-between border-b border-zinc-200/50 dark:border-white/10 bg-white/70 dark:bg-zinc-950/80 px-6 backdrop-blur-3xl transition-all duration-500 ease-in-out shadow-[0_4px_30px_rgba(0,0,0,0.03)] group/header">

    <!-- Premium Ambient Glow -->
    <div class="absolute inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div
            class="absolute top-0 left-1/4 w-[500px] h-full bg-primary/5 blur-[80px] opacity-50 transform -translate-y-1/2 rounded-full transition-opacity duration-700 group-hover/header:opacity-80">
        </div>
        <div
            class="absolute top-0 right-1/4 w-[400px] h-full bg-purple-500/5 blur-[100px] opacity-20 transform -translate-y-1/2 rounded-full transition-opacity duration-700 group-hover/header:opacity-40">
        </div>
    </div>

    <!-- Left Side: Nav & Branding -->
    <div class="flex items-center gap-6">
        @if(!$hideSidebarToggle)
            <button
                class="group flex items-center justify-center rounded-2xl p-2.5 text-muted-foreground hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-foreground hover:shadow-inner transition-all duration-300 active:scale-95 focus-visible:outline-none focus:ring-2 focus:ring-primary/20 backdrop-blur-md border border-transparent hover:border-zinc-200 dark:hover:border-white/20"
                @click="toggleSidebar()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                    class="size-5 transition-transform duration-500 group-hover:rotate-180">
                    <path d="M4 6h16M4 12h16M14 18h6" />
                </svg>
                <span class="sr-only">Toggle Sidebar</span>
            </button>

            <div class="h-8 w-px bg-gradient-to-b from-transparent via-border/50 to-transparent"></div>
        @endif

        <!-- Premium Breadcrumbs -->
        <nav class="hidden md:flex items-center gap-2">
            <div
                class="flex items-center p-1 bg-secondary/20 dark:bg-white/5 rounded-xl border border-white/10 backdrop-blur-md shadow-sm">
                <a href="#"
                    class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider text-muted-foreground hover:text-primary hover:bg-white/50 dark:hover:bg-white/10 transition-all">
                    <div
                        class="size-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary-rgb),0.5)]">
                    </div>
                    {{ tenant() ? ucfirst(tenant('id')) : $activeGroupName }}
                </a>

                <div class="px-1 opacity-20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="size-4">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </div>

                @if($hideDashboardLink)
                    <span
                        class="group flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all bg-primary text-primary-foreground shadow-lg shadow-primary/20">
                        {{ $pageTitle ?: $activePageName }}
                    </span>
                @else
                    <a href="{{ $activePageUrl }}"
                        class="group flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all bg-primary text-primary-foreground shadow-lg shadow-primary/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="size-3.5 transition-transform group-hover:scale-110">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                        {{ $activePageName }}
                    </a>
                @endif
            </div>
        </nav>
    </div>

    <!-- Right Side: Search & User Actions -->
    <div class="flex items-center gap-3 md:gap-6">

        <!-- Premium Customer Search "Command Center" style -->
        @can('customers view')
            @if(!$hideSearch)
                <div
                    x-data="headerCustomerSearch({
                                                                                                                                searchUrl: '{{ tenant() ? route('tenant.api.search.customers') : route('central.api.search.customers') }}',
                                                                                                                                storeUrl: '{{ tenant() ? route('tenant.api.customers.store-quick') : route('central.api.customers.store-quick') }}',
                                                                                                                                addressStoreUrl: '{{ tenant() ? route('tenant.api.addresses.store') : route('central.api.addresses.store') }}',
                                                                                                                                orderUrl: '{{ tenant() ? route('tenant.orders.create') : route('central.orders.create') }}'
                                                                                                                            })">
                    <!-- Search Trigger Button -->
                    <button @click="openSearchModal()"
                        class="group flex items-center justify-center rounded-2xl p-2.5 text-muted-foreground hover:bg-zinc-100 dark:hover:bg-white/5 hover:text-foreground hover:shadow-inner transition-all duration-300 active:scale-95 focus-visible:outline-none focus:ring-2 focus:ring-primary/20 backdrop-blur-md border border-transparent hover:border-zinc-200 dark:hover:border-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="size-5">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                        <span class="sr-only">Search</span>
                    </button>

                    <!-- Search Modal (Teleported) -->
                    <template x-teleport="body">
                        <div x-show="searchOpen" x-transition.opacity.duration.300ms @keydown.escape.window="searchOpen = false"
                            class="fixed inset-0 z-[9999] flex items-start justify-center pt-20 bg-zinc-950/80 backdrop-blur-sm p-4"
                            style="display: none;">

                            <div class="bg-white dark:bg-zinc-900 w-full max-w-2xl rounded-[32px] shadow-2xl border border-white/10 overflow-hidden flex flex-col max-h-[80vh] animate-in slide-in-from-top-4 fade-in duration-300"
                                @click.away="searchOpen = false">

                                <!-- Search Input Header -->
                                <div class="p-4 border-b border-border/50 relative">
                                    <div class="relative flex items-center">
                                        <!-- Search Type Dropdown -->
                                        <div class="relative shrink-0 mr-2">
                                            <select x-model="searchType"
                                                class="h-10 pl-3 pr-8 rounded-xl border border-border/50 bg-secondary/30 dark:bg-zinc-800/50 focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-primary/20 text-xs font-bold shadow-sm appearance-none cursor-pointer outline-none">
                                                <option value="mobile">Mobile</option>
                                                <option value="name">Name</option>
                                                <option value="code">Code</option>
                                            </select>
                                            <div
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-muted-foreground">
                                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="absolute left-[6.5rem] text-muted-foreground pointer-events-none z-10">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="size-5">
                                                <circle cx="11" cy="11" r="8" />
                                                <path d="m21 21-4.3-4.3" />
                                            </svg>
                                        </div>
                                        <input type="text" x-model="customerQuery"
                                            @input="if (searchType === 'mobile') { hasSearchedMobile = false; if (/^\d+$/.test($el.value) && $el.value.length > 10) { $el.value = $el.value.slice(0, 10); customerQuery = $el.value; } }"
                                            @input.debounce.300ms="searchType !== 'mobile' ? searchCustomers() : null"
                                            @keydown.enter.prevent="handleEnterKey()" x-ref="searchInput"
                                            :placeholder="searchType === 'mobile' ? 'Enter 10-digit Mobile...' : 'Search customers...'"
                                            class="flex h-12 w-full rounded-2xl bg-secondary/50 dark:bg-zinc-800/50 pl-12 pr-12 text-base font-medium focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all placeholder:text-muted-foreground/50" />
                                        <button @click="searchOpen = false"
                                            class="absolute right-3 p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-muted-foreground transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="size-5">
                                                <path d="M18 6 6 18" />
                                                <path d="m6 6 12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Results Area -->
                                <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar min-h-[200px]">
                                    <!-- Active Results Header -->
                                    <div x-show="customerResults.length > 0"
                                        class="px-4 py-2 flex items-center justify-between text-xs font-bold text-muted-foreground uppercase tracking-wider">
                                        <span>Results</span>
                                        <span x-text="customerResults.length + ' Matches'"></span>
                                    </div>

                                    <!-- Loading State -->
                                    <template x-if="loading">
                                        <div class="flex flex-col items-center justify-center py-12 space-y-4">
                                            <div
                                                class="size-8 border-4 border-primary/30 border-t-primary rounded-full animate-spin">
                                            </div>
                                            <span
                                                class="text-xs font-bold text-muted-foreground animate-pulse">Searching...</span>
                                        </div>
                                    </template>

                                    <!-- Results List -->
                                    <template x-for="cust in customerResults" :key="cust.id">
                                        <div @click="selectCustomer(cust)"
                                            class="group flex items-center gap-4 p-3 rounded-2xl hover:bg-secondary/50 dark:hover:bg-white/5 cursor-pointer transition-all duration-200 border border-transparent hover:border-black/5 dark:hover:border-white/10">
                                            <div
                                                class="size-12 rounded-xl bg-gradient-to-br from-primary to-purple-600 text-white flex items-center justify-center font-bold text-lg shadow-lg">
                                                <span x-text="cust.first_name.charAt(0)"></span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <h4 class="font-bold text-foreground group-hover:text-primary transition-colors"
                                                        x-text="cust.first_name + ' ' + (cust.last_name || '')"></h4>
                                                    <span
                                                        class="text-xs font-mono font-bold bg-primary/10 text-primary px-1.5 py-0.5 rounded"
                                                        x-text="cust.customer_code"></span>
                                                </div>
                                                <div
                                                    class="flex items-center gap-3 mt-1 text-xs text-muted-foreground font-medium">
                                                    <span class="flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path
                                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                                        </svg>
                                                        <span x-text="cust.mobile"></span>
                                                    </span>
                                                    <span class="w-1 h-1 rounded-full bg-border"></span>
                                                    <span class="uppercase" x-text="cust.type"></span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] font-bold uppercase text-muted-foreground/70">Balance</p>
                                                <p class="font-mono font-bold text-sm"
                                                    :class="cust.outstanding_balance > 0 ? 'text-rose-500' : 'text-emerald-500'"
                                                    x-text="'₹' + parseFloat(cust.outstanding_balance || 0).toLocaleString()">
                                                </p>
                                            </div>
                                        </div>
                                    </template>


                                    <!-- No Results / Register Prompt -->
                                    <template
                                        x-if="!loading && customerResults.length === 0 && (searchType === 'mobile' ? (hasSearchedMobile && customerQuery.length === 10) : customerQuery.length >= 2)">
                                        <div class="text-center py-10 px-4">
                                            <div
                                                class="size-16 rounded-3xl bg-secondary/50 flex items-center justify-center mx-auto mb-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="size-8 text-muted-foreground">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="12" y1="8" x2="12" y2="12" />
                                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                                </svg>
                                            </div>
                                            <h3 class="font-bold text-foreground">No Customer Found</h3>
                                            <p class="text-xs text-muted-foreground mt-1 mb-6">Search term didn't match any
                                                records.</p>
                                            <button @click="openCreateCustomerModal()"
                                                class="w-full py-3 rounded-xl bg-primary text-primary-foreground font-bold text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                                Register New Customer
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Initial Prompt -->
                                    <div x-show="!loading && customerResults.length === 0 && customerQuery.length < 2"
                                        class="text-center py-12 text-muted-foreground">
                                        <p class="text-sm font-medium">Type to search customers...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Premeum Registration Modal (Teleported to Body to avoid Header Clipping) -->
                    <!-- Premium Fast Enrollment Modal -->
                    <template x-teleport="body">
                        <div x-show="showModal" x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 backdrop-blur-none"
                            x-transition:enter-end="opacity-100 backdrop-blur-xl"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 backdrop-blur-xl"
                            x-transition:leave-end="opacity-0 backdrop-blur-none"
                            class="fixed inset-0 z-[9999] flex items-center justify-center bg-zinc-950/60 backdrop-blur-xl p-4 sm:p-6"
                            style="display: none;">

                            <div class="relative w-full max-w-2xl bg-white/90 dark:bg-zinc-900/90 backdrop-blur-3xl rounded-[40px] shadow-[0_32px_128px_rgba(0,0,0,0.4)] border border-white/20 dark:border-white/5 overflow-hidden flex flex-col max-h-[95vh] animate-in zoom-in-95 duration-500 ease-out"
                                @click.away="showModal = false">

                                <!-- Floating Abstract Background Elements (System Level) -->
                                <div
                                    class="absolute -top-40 -right-40 size-96 bg-gradient-to-br from-primary/30 to-purple-600/30 blur-[100px] rounded-full pointer-events-none animate-pulse">
                                </div>
                                <div
                                    class="absolute top-1/2 -left-32 size-80 bg-teal-500/10 blur-[90px] rounded-full pointer-events-none">
                                </div>

                                <!-- Floating Close Command -->
                                <button @click="showModal = false"
                                    class="absolute top-8 right-8 z-[100] size-12 flex items-center justify-center rounded-2xl bg-secondary/50 hover:bg-black/5 dark:hover:bg-white/10 backdrop-blur-md border border-white/10 transition-all duration-300 group">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="size-5 text-muted-foreground group-hover:text-foreground transition-all group-hover:rotate-90">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                </button>

                                <!-- Integrated Scrollable Core -->
                                <div class="flex-1 overflow-y-auto custom-scrollbar px-12 pt-16 pb-12 space-y-12 relative z-10">

                                    <!-- Integrated Intro Section -->
                                    <div class="space-y-4 max-w-lg">
                                        <div
                                            class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-primary/10 text-primary text-[10px] font-black uppercase tracking-[0.2em] mb-2 border border-primary/10">
                                            <span class="size-2 rounded-full bg-primary animate-pulse"></span>
                                            Identity Protocol 2.0
                                        </div>
                                        <h2
                                            class="text-6xl font-black tracking-[-0.06em] leading-[0.9] bg-gradient-to-br from-foreground via-foreground to-foreground/40 bg-clip-text text-transparent">
                                            Fast<br />Enrollment
                                        </h2>
                                        <p class="text-sm font-medium text-muted-foreground/50 leading-relaxed">
                                            Initializing secure record entry. Ensure all identity attributes are verified
                                            against physical documentation for ledger integrity.
                                        </p>
                                    </div>

                                    <!-- Identity Section -->
                                    <section class="space-y-8 relative">
                                        <div class="flex items-center gap-4 group/sec">
                                            <div
                                                class="size-1.5 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--primary),0.5)]">
                                            </div>
                                            <h4 class="text-[11px] font-black uppercase tracking-[0.3em] text-foreground/80">
                                                01. Identity Blueprint
                                            </h4>
                                            <div class="h-px flex-1 bg-gradient-to-r from-border/60 to-transparent"></div>
                                        </div>

                                        <div class="space-y-6">
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">First
                                                        Name</label>
                                                    <input type="text" x-model="newCustomer.first_name"
                                                        class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold placeholder:text-muted-foreground/20 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 shadow-sm"
                                                        placeholder="Legal first name" />
                                                </div>
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Middle
                                                        Name</label>
                                                    <input type="text" x-model="newCustomer.middle_name"
                                                        class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold placeholder:text-muted-foreground/20 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 shadow-sm"
                                                        placeholder="Father/Spouse" />
                                                </div>
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Last
                                                        Name</label>
                                                    <input type="text" x-model="newCustomer.last_name"
                                                        class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold placeholder:text-muted-foreground/20 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 shadow-sm"
                                                        placeholder="Surname" />
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                                <div class="space-y-2 group/field relative">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Contact
                                                        Mobile</label>
                                                    <div class="relative group/input">
                                                        <div
                                                            class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                                            <span
                                                                class="text-primary font-black text-[10px] tracking-tighter bg-primary/10 px-2 py-0.5 rounded-md border border-primary/20 invisible sm:visible">INTL</span>
                                                        </div>
                                                        <input type="text" x-model="newCustomer.mobile" maxlength="10"
                                                            class="w-full h-14 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl sm:pl-20 pl-6 pr-5 text-2xl font-black tracking-[0.15em] focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 font-mono shadow-inner"
                                                            placeholder="0000000000" />
                                                        <div
                                                            class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                                            <div :class="newCustomer.mobile.length === 10 ? 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.8)]' : 'bg-muted/20'"
                                                                class="size-2.5 rounded-full transition-all duration-700">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Email
                                                        Address</label>
                                                    <input type="email" x-model="newCustomer.email"
                                                        class="w-full h-14 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 shadow-sm"
                                                        placeholder="example@identity.io" />
                                                </div>
                                            </div>

                                            <div class="space-y-3 group/field">
                                                <label
                                                    class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Relationship
                                                    Vector</label>
                                                <div class="flex flex-wrap gap-2.5">
                                                    <template x-for="type in ['farmer', 'buyer', 'dealer', 'vendor']">
                                                        <button @click="newCustomer.type = type"
                                                            :class="newCustomer.type === type ? 'bg-foreground text-background shadow-xl scale-[1.05] ring-4 ring-primary/10' : 'bg-secondary/40 dark:bg-white/5 text-muted-foreground hover:bg-secondary/60 dark:hover:bg-white/10'"
                                                            class="px-6 py-2.5 rounded-xl flex items-center justify-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] transition-all duration-500">
                                                            <span x-text="type"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Address Section -->
                                    <section class="space-y-8 relative">
                                        <div class="flex items-center gap-4 group/sec">
                                            <div
                                                class="size-1.5 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--primary),0.5)]">
                                            </div>
                                            <h4 class="text-[11px] font-black uppercase tracking-[0.3em] text-foreground/80">
                                                02. Geospatial Attributes
                                            </h4>
                                            <div class="h-px flex-1 bg-gradient-to-r from-border/60 to-transparent"></div>
                                        </div>

                                        <div class="space-y-6">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Village
                                                        / Town</label>
                                                    <div class="relative">
                                                        <input type="text" x-model="newCustomer.village"
                                                            @focus="activeLookupField = 'village'; lookupAddressOnFocus('village')"
                                                            @input.debounce.300ms="activeLookupField = 'village'; lookupAddress('village', $el.value)"
                                                            class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 placeholder:text-muted-foreground/20"
                                                            placeholder="Search village..." />
                                                        <!-- Suggestions Dropdown -->
                                                        <div x-show="addressSuggestions.length > 0 && activeLookupField === 'village'"
                                                            @click.away="addressSuggestions = []"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                            class="absolute z-50 left-0 right-0 mt-3 max-h-60 overflow-y-auto bg-white/95 dark:bg-zinc-900/95 backdrop-blur-3xl border border-border/40 rounded-[24px] shadow-2xl ring-1 ring-black/5 custom-scrollbar overflow-hidden">
                                                            <div
                                                                class="sticky top-0 bg-secondary/80 backdrop-blur-md px-5 py-2.5 text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 border-b border-border/20">
                                                                Matches Found</div>
                                                            <template x-for="item in addressSuggestions" :key="item.label">
                                                                <button @click="fillAddress(item.data)"
                                                                    class="w-full text-left px-6 py-4 hover:bg-primary/5 dark:hover:bg-white/5 transition-all text-xs font-bold border-b border-border/10 last:border-0 flex items-center justify-between group/item">
                                                                    <span x-text="item.label"></span>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="3"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="size-3 text-primary opacity-0 group-hover/item:opacity-100 transition-all -translate-x-2 group-hover/item:translate-x-0">
                                                                        <path d="m9 18 6-6-6-6" />
                                                                    </svg>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Pincode</label>
                                                    <div class="relative">
                                                        <input type="text" x-model="newCustomer.pincode" maxlength="6"
                                                            @focus="activeLookupField = 'pincode'; lookupAddressOnFocus('pincode')"
                                                            @input.debounce.300ms="activeLookupField = 'pincode'; lookupAddress('pincode', $el.value)"
                                                            class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 font-mono tracking-[0.3em]"
                                                            placeholder="000000" />
                                                        <!-- Suggestions Dropdown -->
                                                        <div x-show="addressSuggestions.length > 0 && activeLookupField === 'pincode'"
                                                            @click.away="addressSuggestions = []"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                            class="absolute z-50 left-0 right-0 mt-3 max-h-60 overflow-y-auto bg-white/95 dark:bg-zinc-900/95 backdrop-blur-3xl border border-border/40 rounded-[24px] shadow-2xl ring-1 ring-black/5 custom-scrollbar overflow-hidden">
                                                            <div
                                                                class="sticky top-0 bg-secondary/80 backdrop-blur-md px-5 py-2.5 text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 border-b border-border/20">
                                                                Matches Found</div>
                                                            <template x-for="item in addressSuggestions" :key="item.label">
                                                                <button @click="fillAddress(item.data)"
                                                                    class="w-full text-left px-6 py-4 hover:bg-primary/5 dark:hover:bg-white/5 transition-all text-xs font-bold border-b border-border/10 last:border-0 flex items-center justify-between group/item">
                                                                    <span x-text="item.label"></span>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="3"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="size-3 text-primary opacity-0 group-hover/item:opacity-100 transition-all -translate-x-2 group-hover/item:translate-x-0">
                                                                        <path d="m9 18 6-6-6-6" />
                                                                    </svg>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Post
                                                        Office</label>
                                                    <div class="relative">
                                                        <input type="text" x-model="newCustomer.post_office"
                                                            @focus="activeLookupField = 'post_office'; lookupAddressOnFocus('post_office')"
                                                            @input.debounce.300ms="activeLookupField = 'post_office'; lookupAddress('post_office', $el.value)"
                                                            class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 placeholder:text-muted-foreground/20"
                                                            placeholder="Search SO..." />
                                                        <!-- Suggestions Dropdown -->
                                                        <div x-show="addressSuggestions.length > 0 && activeLookupField === 'post_office'"
                                                            @click.away="addressSuggestions = []"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                            class="absolute z-50 left-0 right-0 mt-3 max-h-60 overflow-y-auto bg-white/95 dark:bg-zinc-900/95 backdrop-blur-3xl border border-border/40 rounded-[24px] shadow-2xl ring-1 ring-black/5 custom-scrollbar overflow-hidden">
                                                            <div
                                                                class="sticky top-0 bg-secondary/80 backdrop-blur-md px-5 py-2.5 text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 border-b border-border/20">
                                                                Matches Found</div>
                                                            <template x-for="item in addressSuggestions" :key="item.label">
                                                                <button @click="fillAddress(item.data)"
                                                                    class="w-full text-left px-6 py-4 hover:bg-primary/5 dark:hover:bg-white/5 transition-all text-xs font-bold border-b border-border/10 last:border-0 flex items-center justify-between group/item">
                                                                    <span x-text="item.label"></span>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="3"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="size-3 text-primary opacity-0 group-hover/item:opacity-100 transition-all -translate-x-2 group-hover/item:translate-x-0">
                                                                        <path d="m9 18 6-6-6-6" />
                                                                    </svg>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Taluka</label>
                                                    <div class="relative">
                                                        <input type="text" x-model="newCustomer.taluka"
                                                            @focus="activeLookupField = 'taluka'; lookupAddressOnFocus('taluka')"
                                                            @input.debounce.300ms="activeLookupField = 'taluka'; lookupAddress('taluka', $el.value)"
                                                            class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 placeholder:text-muted-foreground/20"
                                                            placeholder="Search taluka..." />
                                                        <!-- Suggestions Dropdown -->
                                                        <div x-show="addressSuggestions.length > 0 && activeLookupField === 'taluka'"
                                                            @click.away="addressSuggestions = []"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                            class="absolute z-50 left-0 right-0 mt-3 max-h-60 overflow-y-auto bg-white/95 dark:bg-zinc-900/95 backdrop-blur-3xl border border-border/40 rounded-[24px] shadow-2xl ring-1 ring-black/5 custom-scrollbar overflow-hidden">
                                                            <div
                                                                class="sticky top-0 bg-secondary/80 backdrop-blur-md px-5 py-2.5 text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 border-b border-border/20">
                                                                Matches Found</div>
                                                            <template x-for="item in addressSuggestions" :key="item.label">
                                                                <button @click="fillAddress(item.data)"
                                                                    class="w-full text-left px-6 py-4 hover:bg-primary/5 dark:hover:bg-white/5 transition-all text-xs font-bold border-b border-border/10 last:border-0 flex items-center justify-between group/item">
                                                                    <span x-text="item.label"></span>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="3"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="size-3 text-primary opacity-0 group-hover/item:opacity-100 transition-all -translate-x-2 group-hover/item:translate-x-0">
                                                                        <path d="m9 18 6-6-6-6" />
                                                                    </svg>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">District</label>
                                                    <div class="relative">
                                                        <input type="text" x-model="newCustomer.district"
                                                            @focus="activeLookupField = 'district'; lookupAddressOnFocus('district')"
                                                            @input.debounce.300ms="activeLookupField = 'district'; lookupAddress('district', $el.value)"
                                                            class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 placeholder:text-muted-foreground/20"
                                                            placeholder="Search district..." />
                                                        <!-- Suggestions Dropdown -->
                                                        <div x-show="addressSuggestions.length > 0 && activeLookupField === 'district'"
                                                            @click.away="addressSuggestions = []"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                            class="absolute z-50 left-0 right-0 mt-3 max-h-60 overflow-y-auto bg-white/95 dark:bg-zinc-900/95 backdrop-blur-3xl border border-border/40 rounded-[24px] shadow-2xl ring-1 ring-black/5 custom-scrollbar overflow-hidden">
                                                            <div
                                                                class="sticky top-0 bg-secondary/80 backdrop-blur-md px-5 py-2.5 text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 border-b border-border/20">
                                                                Matches Found</div>
                                                            <template x-for="item in addressSuggestions" :key="item.label">
                                                                <button @click="fillAddress(item.data)"
                                                                    class="w-full text-left px-6 py-4 hover:bg-primary/5 dark:hover:bg-white/5 transition-all text-xs font-bold border-b border-border/10 last:border-0 flex items-center justify-between group/item">
                                                                    <span x-text="item.label"></span>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="3"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="size-3 text-primary opacity-0 group-hover/item:opacity-100 transition-all -translate-x-2 group-hover/item:translate-x-0">
                                                                        <path d="m9 18 6-6-6-6" />
                                                                    </svg>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">State</label>
                                                    <div class="relative">
                                                        <input type="text" x-model="newCustomer.state"
                                                            @focus="activeLookupField = 'state'; lookupAddressOnFocus('state')"
                                                            @input.debounce.300ms="activeLookupField = 'state'; lookupAddress('state', $el.value)"
                                                            class="w-full h-12 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 placeholder:text-muted-foreground/20"
                                                            placeholder="Search state..." />
                                                        <!-- Suggestions Dropdown -->
                                                        <div x-show="addressSuggestions.length > 0 && activeLookupField === 'state'"
                                                            @click.away="addressSuggestions = []"
                                                            x-transition:enter="transition ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                            class="absolute z-50 left-0 right-0 mt-3 max-h-60 overflow-y-auto bg-white/95 dark:bg-zinc-900/95 backdrop-blur-3xl border border-border/40 rounded-[24px] shadow-2xl ring-1 ring-black/5 custom-scrollbar overflow-hidden">
                                                            <div
                                                                class="sticky top-0 bg-secondary/80 backdrop-blur-md px-5 py-2.5 text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 border-b border-border/20">
                                                                Matches Found</div>
                                                            <template x-for="item in addressSuggestions" :key="item.label">
                                                                <button @click="fillAddress(item.data)"
                                                                    class="w-full text-left px-6 py-4 hover:bg-primary/5 dark:hover:bg-white/5 transition-all text-xs font-bold border-b border-border/10 last:border-0 flex items-center justify-between group/item">
                                                                    <span x-text="item.label"></span>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="3"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="size-3 text-primary opacity-0 group-hover/item:opacity-100 transition-all -translate-x-2 group-hover/item:translate-x-0">
                                                                        <path d="m9 18 6-6-6-6" />
                                                                    </svg>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="space-y-6 pt-2">
                                                <div class="flex items-center gap-4 group/sec">
                                                    <div
                                                        class="size-1.5 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--primary),0.5)]">
                                                    </div>
                                                    <h4
                                                        class="text-[11px] font-black uppercase tracking-[0.3em] text-foreground/80">
                                                        03. Specific Location
                                                    </h4>
                                                    <div class="h-px flex-1 bg-gradient-to-r from-border/60 to-transparent">
                                                    </div>
                                                </div>

                                                <div class="space-y-2 group/field">
                                                    <label
                                                        class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] pl-1">Primary
                                                        Physical Location</label>
                                                    <div class="relative">
                                                        <input type="text" x-model="newCustomer.address_line1"
                                                            class="w-full h-14 bg-secondary/20 dark:bg-black/40 border border-border/40 dark:border-white/5 rounded-2xl px-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-zinc-800 transition-all duration-500 shadow-sm placeholder:text-muted-foreground/20"
                                                            placeholder="Building name, Street address, House number, Landmark etc." />
                                                        <div
                                                            class="absolute right-5 top-1/2 -translate-y-1/2 text-muted-foreground/20 group-focus-within/field:text-primary/40 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2.5" stroke-linecap="round"
                                                                stroke-linejoin="round" class="size-5">
                                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                                                <circle cx="12" cy="10" r="3" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Error Box -->
                                    <div x-show="error" x-transition:enter="transition ease-out duration-500"
                                        x-transition:enter-start="opacity-0 translate-y-4"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="mx-10 mb-8 p-6 rounded-[32px] bg-rose-500/5 border border-rose-500/10 backdrop-blur-sm flex items-center gap-5 group/err">
                                        <div
                                            class="size-12 rounded-[20px] bg-rose-500/10 flex items-center justify-center shrink-0 shadow-[0_4px_12px_rgba(244,63,94,0.1)] group-hover/err:scale-110 transition-transform duration-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" class="size-5 text-rose-500">
                                                <path
                                                    d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                                <line x1="12" x2="12" y1="9" y2="13" />
                                                <line x1="12" x2="12.01" y1="17" y2="17" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="text-[9px] font-black uppercase tracking-[0.3em] text-rose-500 opacity-60 mb-0.5">
                                                Registration Error</p>
                                            <p class="text-xs font-bold text-rose-600/80 dark:text-rose-400/80" x-text="error">
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Premium Footer -->
                                <div
                                    class="px-10 py-8 bg-white/50 dark:bg-black/50 backdrop-blur-3xl border-t border-border/40 flex items-center justify-between gap-6 shrink-0 z-20">
                                    <button @click="showModal = false"
                                        class="h-14 px-10 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 hover:text-foreground hover:bg-secondary/50 dark:hover:bg-white/5 transition-all duration-500 active:scale-95">
                                        Discard
                                    </button>

                                    <button @click="createCustomer()" :disabled="saving"
                                        class="group relative h-14 min-w-[240px] rounded-2xl bg-foreground text-background text-[11px] font-black uppercase tracking-[0.3em] shadow-[0_20px_50px_rgba(0,0,0,0.15)] dark:shadow-[0_20px_50px_rgba(255,255,255,0.05)] hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 disabled:opacity-50 overflow-hidden">

                                        <!-- Gradient Overlay -->
                                        <div
                                            class="absolute inset-0 bg-gradient-to-r from-primary/0 via-primary/10 to-primary/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000 ease-in-out">
                                        </div>

                                        <div class="relative flex items-center justify-center gap-3">
                                            <span x-show="!saving">Complete Enrollment</span>
                                            <span x-show="saving" class="flex items-center gap-3">
                                                <svg class="animate-spin size-4" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                        stroke-width="4" fill="none"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                Processing...
                                            </span>
                                            <svg x-show="!saving" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="size-4 group-hover:translate-x-1 transition-transform duration-300">
                                                <path d="M5 12h14" />
                                                <path d="m12 5 7 7-7 7" />
                                            </svg>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <script>
                        function headerCustomerSearch(routes) {
                            return {
                                searchType: 'mobile',
                                customerQuery: '',
                                customerResults: [],
                                searchOpen: false,
                                loading: false,
                                showModal: false,
                                saving: false,
                                error: '',
                                hasSearchedMobile: false,
                                addressSuggestions: [],
                                activeLookupField: '',
                                newCustomer: {
                                    first_name: '',
                                    middle_name: '',
                                    last_name: '',
                                    mobile: '',
                                    email: '',
                                    type: 'farmer',
                                    address_line1: '',
                                    address_line2: '',
                                    village: '',
                                    taluka: '',
                                    district: '',
                                    state: '',
                                    pincode: '',
                                    post_office: ''
                                },
                                addressSuggestions: [],

                                openSearchModal() {
                                    this.customerQuery = '';
                                    this.customerResults = [];
                                    this.hasSearchedMobile = false;
                                    this.searchOpen = true;
                                    this.$nextTick(() => {
                                        this.$refs.searchInput.focus();
                                    });
                                },

                                async handleEnterKey() {
                                    if (this.searchType === 'mobile' && /^\d{10}$/.test(this.customerQuery)) {
                                        if (!this.loading && this.customerResults.length === 0) {
                                            await this.searchCustomers();
                                        } else {
                                            let attempts = 0;
                                            while (this.loading && attempts < 40) {
                                                await new Promise(r => setTimeout(r, 50));
                                                attempts++;
                                            }
                                        }

                                        if (this.customerResults.length > 0) {
                                            this.selectCustomer(this.customerResults[0]);
                                        } else {
                                            this.openCreateCustomerModal();
                                        }
                                    }
                                },

                                async searchCustomers() {
                                    if (!this.customerQuery) {
                                        this.customerResults = [];
                                        this.hasSearchedMobile = false;
                                        return;
                                    }

                                    if (this.searchType === 'mobile') {
                                        if (!/^\d{10}$/.test(this.customerQuery)) {
                                            this.customerResults = [];
                                            this.hasSearchedMobile = false;
                                            return;
                                        }
                                    } else {
                                        if (this.customerQuery.length < 2) {
                                            this.customerResults = [];
                                            return;
                                        }
                                    }

                                    this.loading = true;
                                    this.hasSearchedMobile = false;
                                    try {
                                        const url = `${routes.searchUrl}?q=${this.customerQuery}&type=${this.searchType}`;
                                        let res = await fetch(url);
                                        if (!res.ok) throw new Error('Search failed');
                                        this.customerResults = await res.json();
                                        if (this.searchType === 'mobile') this.hasSearchedMobile = true;
                                    } catch (e) { console.error(e); }
                                    finally { this.loading = false; }
                                },

                                selectCustomer(cust) {
                                    window.location.href = `${routes.orderUrl}?customer_id=${cust.id}&reset=1`;
                                },

                                openCreateCustomerModal() {
                                    this.searchOpen = false;
                                    this.showModal = true;
                                    this.error = '';
                                    this.newCustomer = {
                                        first_name: '',
                                        middle_name: '',
                                        last_name: '',
                                        mobile: '',
                                        email: '',
                                        type: 'farmer',
                                        address_line1: '',
                                        address_line2: '',
                                        village: '',
                                        taluka: '',
                                        district: '',
                                        state: '',
                                        pincode: '',
                                        post_office: ''
                                    };
                                    if (/^\d+$/.test(this.customerQuery)) {
                                        this.newCustomer.mobile = this.customerQuery;
                                    } else {
                                        this.newCustomer.first_name = this.customerQuery;
                                    }
                                },

                                async createCustomer() {
                                    if (!this.newCustomer.first_name || !this.newCustomer.mobile) {
                                        this.error = 'Legal name and mobile hash required.';
                                        return;
                                    }
                                    this.saving = true;
                                    this.error = '';

                                    const f = this.newCustomer.first_name.trim();
                                    const m = this.newCustomer.middle_name ? this.newCustomer.middle_name.trim() : '';
                                    const l = this.newCustomer.last_name ? this.newCustomer.last_name.trim() : '';
                                    const payload = {
                                        ...this.newCustomer,
                                        display_name: [f, m, l].filter(Boolean).join(' ')
                                    };

                                    try {
                                        const url = routes.storeUrl;
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                        if (!csrfToken) {
                                            this.error = 'Security token missing. Refresh page.';
                                            this.saving = false;
                                            return;
                                        }

                                        let res = await fetch(url, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken
                                            },
                                            body: JSON.stringify(payload)
                                        });
                                        let data = await res.json();

                                        if (data.success) {
                                            // CUSTOMER CREATED - NOW CHECK FOR ADDRESS
                                            if ((this.newCustomer.address_line1 || this.newCustomer.village) && this.newCustomer.pincode) {
                                                try {
                                                    const addrPayload = {
                                                        customer_id: data.customer.id,
                                                        label: 'Home',
                                                        address_line1: this.newCustomer.address_line1 || this.newCustomer.village,
                                                        address_line2: this.newCustomer.address_line2,
                                                        village: this.newCustomer.village,
                                                        taluka: this.newCustomer.taluka,
                                                        district: this.newCustomer.district,
                                                        state: this.newCustomer.state,
                                                        pincode: this.newCustomer.pincode,
                                                        post_office: this.newCustomer.post_office,
                                                        is_default: true,
                                                        type: 'both'
                                                    };

                                                    await fetch(routes.addressStoreUrl, {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'Accept': 'application/json',
                                                            'X-CSRF-TOKEN': csrfToken
                                                        },
                                                        body: JSON.stringify(addrPayload)
                                                    });
                                                } catch (addrErr) {
                                                    console.error('Address sync failed', addrErr);
                                                }
                                            }
                                            this.selectCustomer(data.customer);
                                        } else {
                                            this.error = data.message || 'System rejection. Contact core admin.';
                                        }
                                    } catch (e) {
                                        console.error(e);
                                        this.error = 'Network failure in ledger sync.';
                                    } finally {
                                        this.saving = false;
                                    }
                                },

                                async lookupAddress(field, value) {
                                    if (!value || value.toString().trim().length < 2) {
                                        this.addressSuggestions = [];
                                        return;
                                    }

                                    // Pincode specifically needs 6 digits
                                    if (field === 'pincode' && value.toString().length < 6) return;

                                    try {
                                        const encodedValue = encodeURIComponent(value);
                                        let res = await fetch(`{{ url('/api/village-lookup') }}?${field}=${encodedValue}`, {
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        });
                                        if (!res.ok) throw new Error('Lookup failed');
                                        let data = await res.json();

                                        if (data.found) {
                                            if (data.mode === 'single') {
                                                this.fillAddress(data.data);
                                                this.addressSuggestions = [];
                                            } else {
                                                this.addressSuggestions = data.list;
                                            }
                                        } else {
                                            this.addressSuggestions = [];
                                        }
                                    } catch (e) {
                                        console.error('Lookup error:', e);
                                        this.addressSuggestions = [];
                                    }
                                },

                                lookupAddressOnFocus(field) {
                                    // Only trigger if field is empty
                                    if (this.newCustomer[field] && this.newCustomer[field].toString().trim() !== '') return;

                                    // Use pincode or village as base for other fields
                                    const baseValue = this.newCustomer.pincode || this.newCustomer.village || '';
                                    if (baseValue.length >= 2) {
                                        this.lookupAddress(field, baseValue);
                                    }
                                },

                                fillAddress(data) {
                                    // Map backend data to newCustomer fields
                                    if (data.village) this.newCustomer.village = data.village;
                                    if (data.pincode) this.newCustomer.pincode = data.pincode;
                                    if (data.taluka) this.newCustomer.taluka = data.taluka;
                                    if (data.district) this.newCustomer.district = data.district;
                                    if (data.state) this.newCustomer.state = data.state;
                                    if (data.post_office) this.newCustomer.post_office = data.post_office;

                                    this.addressSuggestions = [];
                                }
                            }
                        }
                    </script>
                </div>
            @endif
        @endcan

        <!-- Premium Action Group -->
        <div
            class="flex items-center gap-1.5 p-1 bg-secondary/20 dark:bg-white/5 border border-white/10 rounded-2xl shadow-inner backdrop-blur-md">
            <!-- Theme Toggle -->
            <x-layout.theme-toggle />

            <!-- Premium Notifications -->
            <div class="relative" x-data="{
                open: false,
                notifications: [],
                unreadCount: 0,
                loading: false,
                async fetchNotifications() {
                    this.loading = true;
                    try {
                        const url = `{{ tenant() ? route('tenant.notifications.index') : route('central.notifications.index') }}`;
                        let res = await fetch(url);
                        if (res.ok) {
                            this.notifications = await res.json();
                            this.unreadCount = this.notifications.filter(n => !n.read_at).length;
                        }
                    } catch (e) { console.error(e); }
                    finally { this.loading = false; }
                },
                async markAllRead() {
                    try {
                        const url = `{{ tenant() ? route('tenant.notifications.read-all') : route('central.notifications.read-all') }}`;
                        await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                            }
                        });
                        this.notifications.forEach(n => n.read_at = new Date().toISOString());
                        this.unreadCount = 0;
                    } catch (e) { console.error(e); }
                }
            }" x-init="fetchNotifications()" @click.away="open = false" @keydown.escape.window="open = false">

                <button @click="open = !open"
                    class="group relative inline-flex items-center justify-center rounded-xl size-10 text-muted-foreground hover:bg-white/50 dark:hover:bg-white/10 hover:text-primary transition-all duration-300 active:scale-90">
                    <span x-show="unreadCount > 0" x-transition
                        class="absolute -top-1 -right-1 min-w-[20px] h-5 flex items-center justify-center px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-[0_0_8px_rgba(244,63,94,0.6)] animate-pulse border-2 border-white dark:border-zinc-950"
                        x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5 relative z-10 transition-all group-hover:rotate-[15deg] group-hover:scale-110 active:scale-95">
                        <path d="M6 8a6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                    </svg>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="absolute right-0 mt-3 w-80 sm:w-96 md:w-[450px] rounded-3xl border border-white/20 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] z-50 overflow-hidden ring-1 ring-black/5">

                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-border/50 bg-white/5 dark:bg-white/2">
                        <h3 class="text-xs font-black uppercase tracking-widest">Feed</h3>
                        <button @click="markAllRead()" x-show="unreadCount > 0"
                            class="text-[10px] font-bold text-primary hover:text-primary/70 underline underline-offset-4 decoration-primary/30 transition-all">
                            Mark All Read
                        </button>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto p-2 space-y-1 no-scrollbar">
                        <template x-if="notifications.length === 0">
                            <div
                                class="flex flex-col items-center justify-center py-10 text-center space-y-4 opacity-50 italic">
                                <div
                                    class="size-16 rounded-full bg-muted/50 flex items-center justify-center shadow-inner">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                                        stroke-linejoin="round" class="size-8 text-muted-foreground/50">
                                        <path d="M6 8a6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-bold uppercase tracking-widest">Digital Silence</p>
                                    <p class="text-[10px] opacity-70">The system ledger is currently quiet.</p>
                                </div>
                            </div>
                        </template>

                        <template x-for="note in notifications" :key="note.id">
                            <div class="group relative flex gap-4 p-4 rounded-2xl hover:bg-secondary/20 dark:hover:bg-white/5 transition-all duration-300 border border-transparent hover:border-black/5 dark:hover:border-white/5 select-text"
                                :class="{ 'bg-primary/5': !note.read_at }">

                                <div class="shrink-0 pt-1"
                                    x-html="note.data.icon || '<svg class=\'size-5 text-primary\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\' /></svg>'">
                                </div>

                                <div class="flex-1 space-y-1.5">
                                    <div class="flex items-start justify-between gap-4">
                                        <h4 class="text-sm font-bold text-foreground leading-tight"
                                            x-text="note.data.title"></h4>
                                        <span
                                            class="text-[10px] font-medium text-muted-foreground whitespace-nowrap shrink-0"
                                            x-text="note.created_at"></span>
                                    </div>
                                    <p class="text-xs text-muted-foreground leading-relaxed font-medium"
                                        x-text="note.data.message"></p>
                                </div>

                                <div x-show="!note.read_at"
                                    class="absolute top-4 right-2 size-2 rounded-full bg-rose-500 shadow-sm ring-2 ring-white dark:ring-zinc-900">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Premeum Settings -->
            @can('settings view')
                <a href="/settings"
                    class="group relative inline-flex items-center justify-center rounded-xl size-10 text-muted-foreground hover:bg-white/50 dark:hover:bg-white/10 hover:text-primary transition-all duration-300 active:scale-90 {{ request()->is('settings*') ? 'text-primary bg-primary/10' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5 relative z-10 transition-transform duration-700 group-hover:rotate-180">
                        <path
                            d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </a>
            @endcan
        </div>

        <div class="h-8 w-px bg-gradient-to-b from-transparent via-border/50 to-transparent mx-2"></div>

        <x-layout.user-dropdown />
    </div>
</header>