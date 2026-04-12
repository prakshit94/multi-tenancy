@extends('layouts.app')

@section('content')
    <div x-data="{ activeTab: '{{ $activeTab ?? 'overview' }}' }"
        class="flex flex-1 flex-col transition-all duration-300 animate-in fade-in zoom-in-95 duration-500">

        <!-- Premium Header with Enhanced Gradient & Depth -->
        <div class="relative z-30 border-b border-border/40">
            <!-- Animated gradient background -->
            <div class="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            
            <div class="relative px-6 py-8 lg:px-8 backdrop-blur-xl bg-background/70 supports-[backdrop-filter]:bg-background/50">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <!-- Header Content -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-1 h-8 bg-gradient-to-b from-primary to-primary/50 rounded-full"></div>
                            <h1 class="text-4xl md:text-5xl font-black tracking-tight bg-gradient-to-br from-foreground via-foreground to-foreground/70 bg-clip-text text-transparent font-heading">
                                Dashboard
                            </h1>
                        </div>
                        <p class="text-sm text-muted-foreground font-medium pl-4">Welcome back, <span class="font-semibold text-foreground">{{ auth()->user()->name }}</span></p>
                    </div>

                    <!-- Status & Date -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-emerald-500/8 border border-emerald-500/20 backdrop-blur-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Live Store</span>
                        </div>
                        <div class="hidden sm:flex items-center gap-2 text-sm text-muted-foreground border-l border-border/30 pl-4">
                            <svg class="w-4 h-4 text-primary/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ now()->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Controls Bar -->
                <div class="flex flex-col sm:flex-row gap-4 mt-6 pt-6 border-t border-border/20">
                    <!-- Tab Navigation -->
                    <div class="flex items-center gap-2 p-1.5 bg-muted/40 rounded-2xl border border-border/30 backdrop-blur-sm">
                        <button @click="activeTab = 'overview'"
                            :class="activeTab === 'overview' ? 'bg-background/80 text-foreground shadow-lg shadow-black/10' : 'text-muted-foreground hover:text-foreground'"
                            class="px-5 py-2 text-xs font-bold uppercase tracking-widest rounded-xl transition-all duration-300 hover:bg-background/40">
                            Overview
                        </button>
                        @can('orders view')
                            <button @click="activeTab = 'orders'"
                                :class="activeTab === 'orders' ? 'bg-background/80 text-foreground shadow-lg shadow-black/10' : 'text-muted-foreground hover:text-foreground'"
                                class="px-5 py-2 text-xs font-bold uppercase tracking-widest rounded-xl transition-all duration-300 hover:bg-background/40">
                                Order History
                            </button>
                        @endcan
                    </div>

                    <div class="flex-1 flex items-center justify-end gap-3">
                        <!-- Premium Period Selector -->
                        @can('analytics view')
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-gradient-to-br from-background/80 to-background/60 border border-primary/20 px-4 py-2.5 text-sm font-semibold text-foreground shadow-sm hover:shadow-md hover:border-primary/40 transition-all duration-300 hover:scale-105 active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="text-primary/70">
                                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                        <line x1="16" x2="16" y1="2" y2="6" />
                                        <line x1="8" x2="8" y1="2" y2="6" />
                                        <line x1="3" x2="21" y1="10" y2="10" />
                                    </svg>
                                    <span class="text-xs font-bold">
                                        {{ [
                            'today' => 'Today',
                            'yesterday' => 'Yesterday',
                            'week' => 'This Week',
                            'month' => 'This Month',
                            'year' => 'This Year'
                        ][$period ?? 'today'] }}
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                        class="transition-transform duration-300" :class="open ? 'rotate-180' : ''">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    class="absolute right-0 mt-3 w-52 rounded-2xl border border-border/50 bg-background/95 backdrop-blur-xl shadow-2xl z-[100] overflow-hidden py-2 ring-1 ring-black/10"
                                    style="display: none;">
                                    @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $key => $label)
                                        <a href="{{ request()->fullUrlWithQuery(['period' => $key, 'active_tab' => $activeTab ?? 'overview']) }}"
                                            class="flex items-center justify-between px-5 py-3 text-xs font-bold uppercase tracking-wide hover:bg-primary/8 transition-colors {{ ($period ?? 'today') === $key ? 'bg-primary/10 text-primary border-l-2 border-primary' : 'text-muted-foreground hover:text-foreground' }}">
                                            <span>{{ $label }}</span>
                                            @if(($period ?? 'today') === $key)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                                    class="text-primary">
                                                    <polyline points="20 6 9 17 4 12" />
                                                </svg>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="p-6 lg:p-10 space-y-8 min-h-screen bg-gradient-to-b from-background to-background/50">

            <!-- Tab Content: Overview -->
            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-8">

                <!-- Premium KPI Grid -->
                @can('analytics view')
                    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        @foreach($stats as $stat)
                            <div class="group relative overflow-hidden rounded-3xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-1 hover:border-primary/50">
                                <!-- Premium Glow Effect -->
                                <div class="absolute -inset-px bg-gradient-to-br from-primary/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>
                                
                                <!-- Top Accent Line -->
                                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-primary/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                                <div class="relative p-6 space-y-4">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between">
                                        <div class="space-y-2">
                                            <span class="text-[11px] font-black uppercase tracking-[0.15em] text-muted-foreground/70 group-hover:text-primary/80 transition-colors">{{ $stat['title'] }}</span>
                                            <div class="text-4xl font-black font-heading tracking-tighter text-foreground">{{ $stat['value'] }}</div>
                                        </div>
                                        <div class="rounded-2xl bg-gradient-to-br from-primary/10 to-primary/5 p-4 text-muted-foreground group-hover:text-primary group-hover:bg-primary/15 transition-all duration-300 group-hover:scale-110 group-hover:rotate-12">
                                            @if($stat['icon'] == 'dollar-sign')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <line x1="12" x2="12" y1="2" y2="22" />
                                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                                </svg>
                                            @elseif($stat['icon'] == 'users')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                    <circle cx="9" cy="7" r="4" />
                                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                                </svg>
                                            @elseif($stat['icon'] == 'refresh-cw')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
                                                    <path d="M21 3v5h-5" />
                                                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
                                                    <path d="M3 21v-5h5" />
                                                </svg>
                                            @elseif($stat['icon'] == 'x-circle')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="15" y1="9" x2="9" y2="15" />
                                                    <line x1="9" y1="9" x2="15" y2="15" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <circle cx="8" cy="21" r="1" />
                                                    <circle cx="19" cy="21" r="1" />
                                                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="flex items-center justify-between pt-2 border-t border-border/20">
                                        <div class="space-y-1.5">
                                            @if($stat['trend'] === 'up')
                                                <div class="flex items-center gap-2 text-emerald-600">
                                                    <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                                            <polyline points="16 7 22 7 22 13" />
                                                        </svg>
                                                        <span class="text-[10px] font-bold uppercase tracking-wide">{{ $stat['change'] }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2 text-rose-600">
                                                    <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-500/10 border border-rose-500/20">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <polyline points="22 17 13.5 8.5 8.5 13.5 2 7" />
                                                            <polyline points="16 17 22 17 22 11" />
                                                        </svg>
                                                        <span class="text-[10px] font-bold uppercase tracking-wide">{{ $stat['change'] }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                            <p class="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-wider">{{ $stat['desc'] }}</p>
                                        </div>

                                        <!-- Sparkline -->
                                        <div class="h-12 w-28">
                                            <svg class="h-full w-full" viewBox="0 0 100 40">
                                                <path
                                                    d="M 0 35 Q 10 30, 20 {{ $stat['trend'] === 'up' ? 25 : 38 }} T 40 {{ $stat['trend'] === 'up' ? 15 : 30 }} T 60 {{ $stat['trend'] === 'up' ? 20 : 35 }} T 80 {{ $stat['trend'] === 'up' ? 5 : 25 }} T 100 {{ $stat['trend'] === 'up' ? 10 : 32 }}"
                                                    fill="none"
                                                    stroke="{{ $stat['trend'] === 'up' ? 'rgba(16,185,129,0.6)' : 'rgba(239,68,68,0.6)' }}"
                                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endcan

                <!-- Main Content Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-7">

                    <!-- Left Column (Recent Orders) -->
                    <div class="col-span-1 lg:col-span-5">
                        @can('orders view')
                            <div class="rounded-3xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-lg overflow-hidden h-full flex flex-col">
                                <!-- Header -->
                                <div class="p-6 border-b border-border/30 bg-gradient-to-r from-muted/40 to-transparent flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-bold text-foreground font-heading">Recent Activity</h3>
                                        <p class="text-xs text-muted-foreground mt-1">Latest orders and transactions</p>
                                    </div>
                                    <a href="{{ route(auth()->user()->hasRole('Super Admin') ? 'central.orders.index' : 'tenant.orders.index') }}"
                                        class="text-xs font-bold uppercase tracking-wider text-primary hover:text-primary/70 hover:underline transition-colors">View All →</a>
                                </div>

                                <!-- Orders List -->
                                <div class="divide-y divide-border/30 flex-1 overflow-y-auto">
                                    @foreach($recentOrders as $order)
                                        <div onclick="window.location.href='{{ tenant('id') ? route('tenant.orders.show', $order) : route('central.orders.show', $order) }}'"
                                            class="group p-5 px-6 flex items-center justify-between hover:bg-primary/5 active:bg-primary/10 transition-all duration-300 cursor-pointer border-l-4 border-transparent hover:border-primary/40">
                                            <div class="flex items-center gap-4 flex-1">
                                                <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center text-xs font-black text-primary border border-primary/20 group-hover:scale-110 transition-transform duration-300">
                                                    {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors leading-tight">
                                                        {{ $order->customer->name ?? 'Guest' }}
                                                    </p>
                                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mt-0.5">
                                                        Ref: #{{ $order->order_number }}</p>
                                                    @if($order->creator)
                                                        <div class="flex items-center gap-1.5 mt-1.5 text-[9px]">
                                                            <svg class="w-3 h-3 text-muted-foreground/50" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                            </svg>
                                                            <span class="font-medium text-muted-foreground">{{ $order->creator->name }}</span>
                                                            <span class="text-muted-foreground/50">({{ $order->creator->location ?? 'N/A' }})</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right space-y-1.5">
                                                <p class="text-sm font-bold text-foreground tracking-tight group-hover:text-primary transition-colors">Rs {{ number_format($order->grand_total, 2) }}</p>
                                                @php
                                                    $statusMap = [
                                                        'completed' => 'bg-emerald-500/15 text-emerald-600 border-emerald-500/30',
                                                        'processing' => 'bg-blue-500/15 text-blue-600 border-blue-500/30',
                                                        'cancelled' => 'bg-rose-500/15 text-rose-600 border-rose-500/30',
                                                        'pending' => 'bg-amber-500/15 text-amber-600 border-amber-500/30',
                                                    ];
                                                    $statusClass = $statusMap[$order->status] ?? 'bg-muted text-muted-foreground border-border';
                                                 @endphp
                                                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide border {{ $statusClass }}">
                                                    {{ $order->status }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endcan
                    </div>

                    <!-- Right Column (Team Activity) -->
<div class="col-span-1 lg:col-span-2">
@can('dashboard view')

<div x-data="{ 
    searchQuery: '',
    sortDir: 'desc',
    sortItems() {
        let container = this.$refs.userList;
        if (!container) return;
        let items = Array.from(container.children).filter(el => el.hasAttribute('data-revenue'));
        if(items.length === 0) return;
        items.sort((a, b) => {
            let revA = parseFloat(a.getAttribute('data-revenue'));
            let revB = parseFloat(b.getAttribute('data-revenue'));
            return this.sortDir === 'desc' ? revB - revA : revA - revB;
        });
        items.forEach(item => container.appendChild(item));
    }
}" x-init="$nextTick(() => sortItems())"
class="rounded-3xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-lg overflow-hidden h-full flex flex-col">

<!-- Header -->
<div class="p-4 px-6 border-b border-border/30 bg-gradient-to-r from-muted/40 to-transparent space-y-3">
    
    <div class="flex justify-between items-center">
        <h3 class="text-sm font-bold uppercase tracking-wider flex items-center gap-2">
            🏆 Team Activity
        </h3>

        <div class="flex items-center gap-2">
            <button @click="sortDir = sortDir === 'desc' ? 'asc' : 'desc'; sortItems()"
                class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-lg hover:bg-muted/30">
                <span x-text="sortDir === 'desc' ? 'High' : 'Low'"></span>
            </button>

            @can('users export')
            <a href="{{ route('central.team.export') }}"
               class="text-[10px] font-bold text-primary px-2.5 py-1 rounded-lg hover:bg-primary/5">
               Export
            </a>
            @endcan
        </div>
    </div>

    <!-- Search -->
    <div class="relative">
        <input type="text" x-model="searchQuery" placeholder="Search team..."
            class="w-full bg-background/50 border border-border/40 rounded-xl py-2.5 px-3 text-xs focus:ring-2 focus:ring-primary/30 outline-none">
    </div>
</div>

<!-- DATA -->
@php
$allOnline = collect($onlineUsers ?? [])->filter(fn($u) => $u && $u->isOnline())
    ->sortByDesc(fn($u) => $u->total_revenue ?? 0)->values();

$displayUsers = $allOnline;

if (auth()->check() && !auth()->user()->hasRole('Super Admin')) {
    $top5 = $allOnline->take(5);
    $currentUser = $allOnline->firstWhere('id', auth()->id());

    if ($currentUser && !$top5->contains('id', auth()->id())) {
        $displayUsers = $top5->push($currentUser);
    } else {
        $displayUsers = $top5;
    }
}
@endphp

<!-- ⭐ TOP 3 TRIANGLE (FULL NAME) -->
@if($displayUsers->count() >= 1)
<div class="p-5">

    @php
        $topUsers = $allOnline->take(3);
        $second = $topUsers->get(1);
        $first = $topUsers->get(0);
        $third = $topUsers->get(2);
    @endphp

    <div class="flex justify-center items-end gap-8">

        <!-- 2 -->
        @if($second)
        <div class="flex flex-col items-center max-w-[80px]">
            <div class="relative">
                <div class="px-3 py-1 bg-gray-200 rounded-lg text-[10px] font-bold text-center truncate w-full">
                    {{ $second->name }}
                </div>
                <span class="absolute -top-2 -right-2 text-xs">⭐</span>
            </div>
            <div class="text-[10px] mt-1 font-semibold">2</div>
        </div>
        @endif

        <!-- 1 -->
        @if($first)
        <div class="flex flex-col items-center -mt-4 max-w-[100px]">
            <div class="relative">
                <div class="px-4 py-1.5 bg-yellow-400 rounded-lg text-[11px] font-bold text-center truncate w-full shadow">
                    {{ $first->name }}
                </div>
                <span class="absolute -top-2 -right-2 text-sm animate-pulse">⭐</span>
            </div>
            <div class="text-xs mt-1 font-bold text-yellow-600">1</div>
        </div>
        @endif

        <!-- 3 -->
        @if($third)
        <div class="flex flex-col items-center max-w-[80px]">
            <div class="relative">
                <div class="px-3 py-1 bg-orange-300 rounded-lg text-[10px] font-bold text-center truncate w-full">
                    {{ $third->name }}
                </div>
                <span class="absolute -top-2 -right-2 text-xs">⭐</span>
            </div>
            <div class="text-[10px] mt-1 font-semibold">3</div>
        </div>
        @endif

    </div>
</div>
@endif

<!-- LIST -->
<div x-ref="userList" class="divide-y divide-border/30 flex-1 overflow-y-auto">

@foreach($displayUsers as $onlineUser)

@php
$actualRank = $allOnline->search(fn($u) => $u->id === $onlineUser->id) + 1;
@endphp

<div data-revenue="{{ $onlineUser->total_revenue ?? 0 }}"
    x-show="!searchQuery || '{{ strtolower($onlineUser->name) }}'.includes(searchQuery.toLowerCase())"
    class="flex items-center justify-between p-3.5 px-4 hover:bg-primary/5 transition">

    <div class="flex items-center gap-3">

        <div class="w-5 h-5 rounded-md bg-muted flex items-center justify-center text-[9px] font-bold">
            {{ $actualRank }}
        </div>

        <div class="w-8 h-8 rounded-full bg-primary/15 flex items-center justify-center font-bold text-xs">
            {{ substr($onlineUser->name, 0, 2) }}
        </div>

        <div>
            <div class="text-xs font-bold">
                {{ $onlineUser->name }}
                @if($onlineUser->id === auth()->id())
                    <span class="text-[9px] text-primary">(You)</span>
                @endif
            </div>
            <div class="text-[10px] text-muted-foreground">
                {{ $onlineUser->location ?? 'Unknown' }}
            </div>
        </div>
    </div>

    <div class="text-right">
        <div class="text-[10px] font-bold">
            Rs {{ number_format($onlineUser->total_revenue ?? 0, 0) }}
        </div>
        <div class="text-[9px] text-muted-foreground">
            {{ $onlineUser->orders_count }} Orders
        </div>
    </div>

</div>

@endforeach

@if($displayUsers->isEmpty())
<div class="p-8 text-center text-muted-foreground">
    <p class="text-xs">No users online</p>
</div>
@endif

</div>

</div>
@endcan
</div>
                </div>
            </div>

            <!-- Tab Content: Order History -->
            @can('orders view')
                <div x-show="activeTab === 'orders'" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                    class="space-y-6">
                    
                    <!-- Desktop Table View -->
                    <div class="hidden md:block rounded-3xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-border/30 bg-gradient-to-r from-muted/40 to-transparent">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-bold font-heading tracking-tight text-foreground">Order History</h3>
                                    <p class="text-sm text-muted-foreground font-medium mt-1">{{ count($orderHistory) }} total orders</p>
                                </div>
                                <div class="px-4 py-2 bg-secondary/40 border border-border/30 rounded-xl">
                                    <span class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">
                                        Total: {{ count($orderHistory) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-border/30 bg-muted/20">
                                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70">Order ID</th>
                                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70">Customer</th>
                                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70">Date</th>
                                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70">Items</th>
                                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70 text-right">Amount</th>
                                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70 text-center">Status</th>
                                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70">Created By</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/30">
                                    @foreach($orderHistory as $order)
                                        <tr class="group hover:bg-primary/5 active:bg-primary/10 transition-all duration-300 cursor-pointer border-l-4 border-transparent hover:border-primary/40"
                                            @click="window.location.href='{{ tenant('id') ? route('tenant.orders.show', $order) : route('central.orders.show', $order) }}'">
                                            <td class="px-6 py-5">
                                                <span class="font-mono text-sm font-bold text-foreground group-hover:text-primary transition-colors">#{{ $order->order_number }}</span>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="flex items-center gap-3">
                                                    <div class="size-9 rounded-xl bg-gradient-to-br from-primary/20 to-primary/10 text-primary flex items-center justify-center font-bold text-xs border border-primary/20 group-hover:scale-110 transition-transform duration-300">
                                                        {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                                    </div>
                                                    <span class="text-sm font-bold text-foreground group-hover:text-primary transition-colors">{{ $order->customer->name ?? 'Guest' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="text-sm font-bold text-foreground">{{ ($order->placed_at ?? $order->created_at)->format('M d, Y') }}</span>
                                                    <span class="text-[9px] font-bold text-muted-foreground/70 uppercase tracking-wider">{{ ($order->placed_at ?? $order->created_at)->format('H:i') }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="flex flex-col gap-1 max-w-xs">
                                                    @foreach($order->items->take(2) as $item)
                                                        <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground truncate">
                                                            <span class="font-bold text-foreground">{{ $item->quantity }}x</span>
                                                            <span class="truncate">{{ Str::limit($item->product->name ?? 'Unknown', 25) }}</span>
                                                        </div>
                                                    @endforeach
                                                    @if($order->items->count() > 2)
                                                        <span class="text-[9px] font-bold text-muted-foreground/60 uppercase">+{{ $order->items->count() - 2 }} more</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 text-right">
                                                <span class="font-mono text-sm font-bold text-foreground group-hover:text-primary transition-colors">Rs {{ number_format($order->grand_total, 2) }}</span>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                @php
                                                    $statusClass = [
                                                        'completed' => 'bg-emerald-500/15 text-emerald-600 border-emerald-500/30',
                                                        'processing' => 'bg-blue-500/15 text-blue-600 border-blue-500/30',
                                                        'cancelled' => 'bg-rose-500/15 text-rose-600 border-rose-500/30',
                                                        'pending' => 'bg-amber-500/15 text-amber-600 border-amber-500/30',
                                                        'shipped' => 'bg-indigo-500/15 text-indigo-600 border-indigo-500/30',
                                                    ][$order->status] ?? 'bg-muted text-muted-foreground border-border';
                                                @endphp
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-wide border {{ $statusClass }}">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="flex items-center gap-2">
                                                    <div class="size-2 rounded-full bg-emerald-500/80"></div>
                                                    <span class="text-[10px] font-bold uppercase tracking-tight text-muted-foreground group-hover:text-foreground transition-colors">
                                                        {{ $order->creator->name ?? 'SYSTEM' }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4">
                        @foreach($orderHistory as $order)
                            <div @click="window.location.href='{{ tenant('id') ? route('tenant.orders.show', $order) : route('central.orders.show', $order) }}'"
                                class="group relative overflow-hidden rounded-3xl border border-border/40 bg-card/60 backdrop-blur-xl shadow-md active:scale-95 transition-all cursor-pointer hover:shadow-lg hover:border-primary/40">
                                
                                <div class="p-5 space-y-4">
                                    <div class="flex justify-between items-start gap-3">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div class="size-10 rounded-xl bg-gradient-to-br from-primary/20 to-primary/10 text-primary flex items-center justify-center font-bold text-xs border border-primary/20 flex-shrink-0">
                                                {{ substr($order->customer->name ?? 'G', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="text-sm font-bold text-foreground group-hover:text-primary transition-colors truncate">{{ $order->customer->name ?? 'Guest' }}</h4>
                                                <p class="text-[10px] font-bold text-muted-foreground/70 tracking-wider uppercase">#{{ $order->order_number }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors">Rs {{ number_format($order->grand_total, 2) }}</p>
                                            <p class="text-[9px] font-bold text-muted-foreground/70 mt-0.5">{{ ($order->placed_at ?? $order->created_at)->format('M d H:i') }}</p>
                                        </div>
                                    </div>

                                    <!-- Items Preview -->
                                    <div class="space-y-1.5 px-3.5 py-3 bg-muted/20 rounded-2xl border border-border/20">
                                        @foreach($order->items->take(2) as $item)
                                            <div class="flex items-center gap-2 text-[10px]">
                                                <span class="font-bold text-foreground">{{ $item->quantity }}×</span>
                                                <span class="text-muted-foreground truncate">{{ Str::limit($item->product->name ?? 'Unknown', 30) }}</span>
                                            </div>
                                        @endforeach
                                        @if($order->items->count() > 2)
                                            <div class="text-[9px] font-bold text-muted-foreground/60 pt-1 border-t border-border/20">+{{ $order->items->count() - 2 }} more items</div>
                                        @endif
                                    </div>

                                    <!-- Status & Creator -->
                                    <div class="flex items-center justify-between gap-2">
                                        @php
                                            $statusClass = [
                                                'completed' => 'bg-emerald-500/15 text-emerald-600 border-emerald-500/30',
                                                'processing' => 'bg-blue-500/15 text-blue-600 border-blue-500/30',
                                                'cancelled' => 'bg-rose-500/15 text-rose-600 border-rose-500/30',
                                                'pending' => 'bg-amber-500/15 text-amber-600 border-amber-500/30',
                                                'shipped' => 'bg-indigo-500/15 text-indigo-600 border-indigo-500/30',
                                            ][$order->status] ?? 'bg-muted text-muted-foreground border-border';
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-wide border {{ $statusClass }}">
                                            {{ $order->status }}
                                        </span>
                                        <span class="text-[9px] font-bold text-muted-foreground/70 uppercase tracking-wide">{{ $order->creator->name ?? 'SYSTEM' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection