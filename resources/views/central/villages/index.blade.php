@extends('layouts.app')

@section('content')
    <div x-data="villageModal()"
        class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8 animate-in fade-in zoom-in-95 duration-700">

        <!-- Page Header & Stats -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative">
            <!-- Decorative blur blob -->
            <div class="absolute -top-10 -left-10 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-1.5 relative z-10">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/20 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="text-primary">
                            <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7" />
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                            <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4" />
                            <path d="M2 7h20" />
                            <path
                                d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7" />
                        </svg>
                    </div>
                    <h1
                        class="text-3xl font-extrabold tracking-tight bg-gradient-to-br from-foreground via-foreground/90 to-foreground/60 bg-clip-text text-transparent">
                        Villages Directory
                    </h1>
                </div>
                <p class="text-muted-foreground text-sm font-medium ml-13">Manage administrative village data and pincodes
                    with precision.</p>
            </div>

            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none relative z-10">
                <!-- Premium Add Action -->
                <button @click="openCreateModal()" type="button"
                    class="group relative inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-primary to-primary/90 px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-[0_0_20px_-3px_rgba(var(--primary),0.4)] hover:shadow-[0_0_25px_-1px_rgba(var(--primary),0.5)] hover:scale-[1.03] active:scale-95 transition-all duration-300 overflow-hidden border border-primary/20">
                    <span
                        class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-[150%] skew-x-[-20deg] group-hover:animate-[shine_1.5s_ease-out]"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                        class="relative z-10 transition-transform group-hover:rotate-90 duration-300">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span class="relative z-10 tracking-wide font-bold">Add Village</span>
                </button>
            </div>
        </div>

        <!-- Toolbar Section -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-4 mt-8 mb-4 relative z-10">
            <!-- Premium Search Bar -->
            <form id="search-form" method="GET" action="{{ route('central.villages.index') }}"
                class="flex items-center gap-2 group w-full sm:w-auto">
                <!-- State Filter -->
                <div class="relative w-full sm:w-auto">
                    <select name="state_name"
                        class="block w-full sm:w-40 appearance-none rounded-2xl border border-border/40 py-2.5 pl-4 pr-9 text-foreground bg-background/40 backdrop-blur-xl shadow-sm focus:bg-background/80 focus:border-primary/50 focus:ring-4 focus:ring-primary/10 sm:text-sm transition-all duration-300 outline-none hover:bg-background/60 cursor-pointer font-medium">
                        <option value="">All States</option>
                        @foreach($states as $state)
                            <option value="{{ $state }}" {{ request('state_name') == $state ? 'selected' : '' }}>{{ $state }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-muted-foreground/60">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>

                <!-- District Filter -->
                <div class="relative w-full sm:w-auto">
                    <select name="district_name"
                        class="block w-full sm:w-44 appearance-none rounded-2xl border border-border/40 py-2.5 pl-4 pr-9 text-foreground bg-background/40 backdrop-blur-xl shadow-sm focus:bg-background/80 focus:border-primary/50 focus:ring-4 focus:ring-primary/10 sm:text-sm transition-all duration-300 outline-none hover:bg-background/60 cursor-pointer font-medium">
                        <option value="">All Districts</option>
                        @foreach($districts as $district)
                            <option value="{{ $district }}" {{ request('district_name') == $district ? 'selected' : '' }}>
                                {{ $district }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-muted-foreground/60">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Text Search -->
                <div class="relative transition-all duration-500 sm:w-64 group-focus-within:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            class="text-muted-foreground/60 group-focus-within:text-primary transition-colors duration-300">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search records..."
                        class="block w-full rounded-2xl border border-border/40 py-2.5 pl-11 pr-4 text-foreground bg-background/40 backdrop-blur-xl shadow-sm placeholder:text-muted-foreground/60 focus:bg-background/80 focus:border-primary/50 focus:ring-4 focus:ring-primary/10 sm:text-sm sm:leading-6 transition-all duration-300 outline-none hover:bg-background/60 font-medium">
                    <div
                        class="absolute inset-0 -z-10 bg-primary/5 blur-xl rounded-2xl opacity-0 group-focus-within:opacity-100 transition-opacity duration-500">
                    </div>
                </div>
                @if(request('search'))
                    <a href="{{ route('central.villages.index') }}"
                        class="inline-flex items-center justify-center p-2.5 rounded-xl bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground transition-all duration-300 shadow-sm"
                        title="Clear Search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <!-- Premium Data Table Card -->
        <div id="villages-table-container"
            class="rounded-3xl border border-border/40 bg-card/40 backdrop-blur-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden relative z-10">

            <!-- Loading Overlay -->
            <div id="table-loading"
                class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent shadow-lg"></div>
            </div>

            <!-- Subtle gradient overlay -->
            <div
                class="absolute inset-0 bg-gradient-to-br from-white/40 to-white/0 dark:from-white/5 dark:to-transparent pointer-events-none">
            </div>

            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm border-collapse">
                    <thead>
                        <tr
                            class="border-b border-border/30 text-[10px] uppercase tracking-[0.2em] font-bold text-muted-foreground/70 bg-muted/10">
                            <th class="h-14 px-6 text-left align-middle w-[25%]">Village Info</th>
                            <th class="h-14 px-6 text-left align-middle">Region Matrix</th>
                            <th class="h-14 px-6 text-left align-middle">State Data</th>
                            <th class="h-14 px-6 text-right align-middle">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0 text-sm">
                        @forelse($villages as $village)
                            <tr class="group border-b border-border/20 transition-all duration-300 hover:bg-muted/30">

                                <td class="p-4 px-6 align-middle">
                                    <div class="flex flex-col space-y-1">
                                        <span class="font-bold text-foreground tracking-tight flex items-center gap-2">
                                            {{ $village->village_name }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center rounded-md bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary border border-primary/20">
                                                {{ $village->pincode }}
                                            </span>
                                            <span class="text-xs text-muted-foreground truncate max-w-[150px]"
                                                title="Post Office">
                                                {{ $village->post_so_name ?? 'No PO Data' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4 px-6 align-middle">
                                    <div class="flex flex-col">
                                        <span class="text-foreground font-medium">{{ $village->taluka_name ?? '-' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ $village->district_name ?? '-' }}</span>
                                    </div>
                                </td>

                                <td class="p-4 px-6 align-middle">
                                    <span class="inline-flex items-center gap-1.5">
                                        @if($village->state_name === 'Gujarat')
                                            <span
                                                class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span>
                                        @else
                                            <span
                                                class="h-2 w-2 rounded-full bg-blue-500 shadow-[0_0_5px_rgba(59,130,246,0.5)]"></span>
                                        @endif
                                        <span class="text-muted-foreground font-medium">{{ $village->state_name ?? '-' }}</span>
                                    </span>
                                </td>

                                <td class="p-4 px-6 align-middle text-right">
                                    <div class="relative flex justify-end" x-data="{ open: false }" @click.away="open = false">
                                        <button @click="open = !open"
                                            class="group/btn inline-flex h-9 w-9 items-center justify-center rounded-xl bg-background border border-border/50 text-muted-foreground/70 transition-all duration-300 hover:text-foreground hover:bg-muted hover:border-border hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/20 active:scale-90">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round" class="transition-transform group-hover/btn:rotate-90">
                                                <circle cx="12" cy="12" r="1.5" />
                                                <circle cx="12" cy="5" r="1.5" />
                                                <circle cx="12" cy="19" r="1.5" />
                                            </svg>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95 translate-y-2 translate-x-4"
                                            x-transition:enter-end="opacity-100 scale-100 translate-y-0 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 scale-100 translate-y-0 translate-x-0"
                                            x-transition:leave-end="opacity-0 scale-95 translate-y-2 translate-x-4"
                                            class="absolute right-0 top-11 z-50 min-w-[180px] overflow-hidden rounded-2xl border border-border/60 bg-popover/95 p-1.5 text-popover-foreground shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] backdrop-blur-2xl"
                                            style="display: none;">

                                            <div
                                                class="px-2 py-1.5 text-[10px] uppercase font-bold text-muted-foreground/50 tracking-wider">
                                                Quick Actions</div>

                                            <!-- Edit Trigger -->
                                            <button @click="openEditModal({{ $village->toJson() }}); open = false" type="button"
                                                class="flex w-full cursor-pointer select-none items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium outline-none transition-colors hover:bg-accent/80 hover:text-accent-foreground">
                                                <div class="p-1 rounded-md bg-muted/50 text-muted-foreground">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                                        <path d="m15 5 4 4" />
                                                    </svg>
                                                </div>
                                                Edit Details
                                            </button>

                                            <!-- Delete Trigger -->
                                            <form action="{{ route('central.villages.destroy', $village) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to permanently delete {{ $village->village_name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="mt-1 w-full flex cursor-pointer select-none items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium outline-none transition-colors hover:bg-destructive/10 hover:text-destructive text-destructive/80">
                                                    <div class="p-1 rounded-md bg-destructive/10 text-destructive/80">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M3 6h18" />
                                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                                        </svg>
                                                    </div>
                                                    Permanently Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-muted-foreground/60">
                                        <div class="relative flex items-center justify-center mb-6">
                                            <div class="absolute inset-0 bg-primary/10 blur-2xl rounded-full"></div>
                                            <div
                                                class="relative h-20 w-20 rounded-3xl bg-gradient-to-br from-muted/50 to-muted/10 border border-border/50 flex items-center justify-center shadow-inner">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="text-muted-foreground/50">
                                                    <path
                                                        d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-xl font-bold text-foreground tracking-tight">No villages found</p>
                                        <p class="text-sm mt-2 max-w-[280px] mx-auto leading-relaxed">It looks empty here. Try
                                            adjusting your search criteria or add a new village record.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Premium Pagination -->
            @if($villages->hasPages() || $villages->total() > 15)
                <div
                    class="border-t border-border/30 p-4 bg-muted/5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div
                        class="text-xs text-muted-foreground font-medium px-2 bg-muted/20 py-1.5 rounded-lg border border-border/30">
                        Showing <span class="font-bold text-foreground">{{ $villages->firstItem() ?? 0 }}</span> to <span
                            class="font-bold text-foreground">{{ $villages->lastItem() ?? 0 }}</span> of <span
                            class="font-bold border-l border-border/50 pl-2 ml-1">{{ $villages->total() }}</span> records
                    </div>
                    <div>{{ $villages->onEachSide(1)->links() }}</div>
                </div>
            @endif
        </div>

        <!-- Ultra-Premium Detached Slide-over Modal -->
        <div x-cloak x-show="isModalOpen" class="relative z-[100]" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <!-- Backdrop -->
            <div x-show="isModalOpen" x-transition:enter="ease-out duration-500"
                x-transition:enter-start="opacity-0 backdrop-blur-none"
                x-transition:enter-end="opacity-100 backdrop-blur-sm" x-transition:leave="ease-in duration-400"
                x-transition:leave-start="opacity-100 backdrop-blur-sm"
                x-transition:leave-end="opacity-0 backdrop-blur-none"
                class="fixed inset-0 bg-background/60 transition-all pointer-events-auto"></div>

            <div class="fixed inset-0 overflow-hidden pointer-events-none">
                <div class="absolute inset-0 overflow-hidden">
                    <div
                        class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16 pr-4 sm:pr-6 py-4 sm:py-6">
                        <div x-show="isModalOpen"
                            x-transition:enter="transform transition ease-[cubic-bezier(0.2,0.8,0.2,1.1)] duration-700"
                            x-transition:enter-start="translate-x-full opacity-50 scale-95"
                            x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                            x-transition:leave="transform transition ease-in duration-400"
                            x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                            x-transition:leave-end="translate-x-full opacity-0 scale-95" @click.away="isModalOpen = false"
                            class="pointer-events-auto w-screen max-w-lg h-full">

                            <div
                                class="flex h-full flex-col overflow-hidden bg-card/90 backdrop-blur-3xl shadow-[0_0_50px_-10px_rgba(0,0,0,0.2)] border border-border/50 rounded-3xl relative">

                                <!-- Decorative modal gradient -->
                                <div
                                    class="absolute top-0 left-0 right-0 h-32 bg-gradient-to-b from-primary/10 to-transparent pointer-events-none">
                                </div>

                                <!-- Modal Header -->
                                <div
                                    class="px-6 py-6 border-b border-border/30 flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/20 shadow-inner">
                                            <svg x-show="!isEditMode" xmlns="http://www.w3.org/2000/svg" width="22"
                                                height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="text-primary">
                                                <path d="M5 12h14" />
                                                <path d="M12 5v14" />
                                            </svg>
                                            <svg x-show="isEditMode" style="display: none;"
                                                xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                                <path d="m15 5 4 4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-bold tracking-tight text-foreground"
                                                x-text="isEditMode ? 'Edit Village Details' : 'Create New Village'"></h2>
                                            <p class="text-xs text-muted-foreground font-medium mt-0.5"
                                                x-text="isEditMode ? 'Update existing master data' : 'Add a new record to the directory'">
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="isModalOpen = false"
                                        class="rounded-xl bg-background/50 border border-border/50 text-muted-foreground hover:text-foreground hover:bg-accent p-2.5 transition-all shadow-sm active:scale-95">
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Modal Body (Form) -->
                                <form :action="formUrl" method="POST" class="flex flex-col flex-1 overflow-hidden">
                                    @csrf
                                    <template x-if="isEditMode">
                                        <input type="hidden" name="_method" value="PUT">
                                    </template>

                                    <div
                                        class="px-6 py-6 space-y-6 flex-1 overflow-y-auto min-h-0 custom-scrollbar relative z-10">
                                        @if($errors->any())
                                            <div
                                                class="rounded-2xl bg-destructive/10 p-4 border border-destructive/20 shadow-sm animate-in slide-in-from-top-2">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0 mt-0.5">
                                                        <svg class="h-5 w-5 text-destructive" viewBox="0 0 20 20"
                                                            fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h3 class="text-sm font-semibold text-destructive">Validation Error</h3>
                                                        <div class="mt-1 text-xs text-destructive/80">
                                                            Please correct the highlighted fields below.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-2 gap-5">
                                            <!-- Full Width: Village Name -->
                                            <div class="col-span-2 space-y-1.5 group/input">
                                                <label for="village_name"
                                                    class="block text-xs font-bold uppercase tracking-wider text-muted-foreground group-focus-within/input:text-primary transition-colors">Village
                                                    Name <span class="text-destructive">*</span></label>
                                                <div class="relative">
                                                    <input type="text" name="village_name" id="village_name"
                                                        x-model="formData.village_name" required
                                                        class="block w-full rounded-xl border border-input bg-background/50 px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 focus:bg-background transition-all shadow-sm font-medium text-foreground">
                                                </div>
                                                @error('village_name') <p
                                                    class="text-[0.75rem] font-bold text-destructive mt-1">{{ $message }}
                                                </p> @enderror
                                            </div>

                                            <!-- Half Width: Pincode -->
                                            <div class="col-span-1 space-y-1.5 group/input">
                                                <label for="pincode"
                                                    class="block text-xs font-bold uppercase tracking-wider text-muted-foreground group-focus-within/input:text-primary transition-colors">Pincode
                                                    <span class="text-destructive">*</span></label>
                                                <div class="relative">
                                                    <input type="text" name="pincode" id="pincode"
                                                        x-model="formData.pincode" required maxlength="6"
                                                        class="block w-full rounded-xl border border-input bg-background/50 px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 focus:bg-background transition-all shadow-sm font-mono tracking-widest text-primary font-bold">
                                                </div>
                                                @error('pincode') <p class="text-[0.75rem] font-bold text-destructive mt-1">
                                                    {{ $message }}
                                                </p> @enderror
                                            </div>

                                            <!-- Half Width: Post Office -->
                                            <div class="col-span-1 space-y-1.5 group/input">
                                                <label for="post_so_name"
                                                    class="block text-xs font-bold uppercase tracking-wider text-muted-foreground group-focus-within/input:text-primary transition-colors">Post
                                                    Office (SO)</label>
                                                <input type="text" name="post_so_name" id="post_so_name"
                                                    x-model="formData.post_so_name"
                                                    class="block w-full rounded-xl border border-input bg-background/50 px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 focus:bg-background transition-all shadow-sm font-medium">
                                            </div>

                                            <!-- Group: Geography -->
                                            <div class="col-span-2 pt-4 pb-2 border-t border-border/30">
                                                <h4
                                                    class="text-[10px] uppercase font-bold tracking-[0.2em] text-muted-foreground/60 mb-4">
                                                    Geographical Data</h4>
                                                <div class="grid grid-cols-2 gap-4">

                                                    <!-- Taluka -->
                                                    <div class="col-span-2 sm:col-span-1 space-y-1.5 group/input">
                                                        <label for="taluka_name"
                                                            class="block text-xs font-semibold text-muted-foreground group-focus-within/input:text-primary transition-colors">Taluka</label>
                                                        <input type="text" name="taluka_name" id="taluka_name"
                                                            x-model="formData.taluka_name"
                                                            class="block w-full rounded-xl border border-input bg-background/50 px-4 py-2.5 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all shadow-sm">
                                                    </div>

                                                    <!-- District -->
                                                    <div class="col-span-2 sm:col-span-1 space-y-1.5 group/input">
                                                        <label for="district_name"
                                                            class="block text-xs font-semibold text-muted-foreground group-focus-within/input:text-primary transition-colors">District</label>
                                                        <input type="text" name="district_name" id="district_name"
                                                            x-model="formData.district_name"
                                                            class="block w-full rounded-xl border border-input bg-background/50 px-4 py-2.5 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all shadow-sm">
                                                    </div>

                                                    <!-- State -->
                                                    <div class="col-span-2 space-y-1.5 group/input">
                                                        <label for="state_name"
                                                            class="block text-xs font-semibold text-muted-foreground group-focus-within/input:text-primary transition-colors">State</label>
                                                        <div class="relative">
                                                            <select name="state_name" id="state_name"
                                                                x-model="formData.state_name"
                                                                class="appearance-none block w-full rounded-xl border border-input bg-background/50 px-4 py-3 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 transition-all shadow-sm font-medium cursor-pointer">
                                                                <option value="Gujarat">Gujarat</option>
                                                                <option value="Maharashtra">Maharashtra</option>
                                                                <option value="Rajasthan">Rajasthan</option>
                                                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                                                <option value="Delhi">Delhi</option>
                                                                <option value="Other">Other</option>
                                                            </select>
                                                            <div
                                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-muted-foreground">
                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Form Footer -->
                                    <div
                                        class="px-6 py-5 border-t border-border/40 bg-muted/20 flex items-center justify-between sm:justify-end gap-3 z-10">
                                        <button type="button" @click="isModalOpen = false"
                                            class="inline-flex items-center justify-center rounded-xl bg-background px-5 py-2.5 text-sm font-semibold text-foreground border border-border transition-all hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring sm:w-auto w-full active:scale-95 shadow-sm">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-primary to-primary/90 px-6 py-2.5 text-sm font-bold text-primary-foreground shadow-[0_4px_14px_0_rgba(var(--primary),0.39)] hover:shadow-[0_6px_20px_rgba(var(--primary),0.23)] hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-primary/50 sm:w-auto w-full transition-all active:scale-95">
                                            <span x-text="isEditMode ? 'Save Changes' : 'Create Record'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom scrollbar for modal */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgb(var(--border) / 0.5);
            border-radius: 10px;
        }

        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background-color: rgb(var(--border));
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('villageModal', () => ({
                isModalOpen: false,
                isEditMode: false,
                formUrl: '',
                formData: {
                    village_name: '',
                    pincode: '',
                    post_so_name: '',
                    taluka_name: '',
                    district_name: '',
                    state_name: 'Gujarat'
                },

                openCreateModal() {
                    this.isEditMode = false;
                    this.formUrl = "{{ route('central.villages.store') }}";
                    this.formData = {
                        village_name: '',
                        pincode: '',
                        post_so_name: '',
                        taluka_name: '',
                        district_name: '',
                        state_name: 'Gujarat' // Default
                    };
                    this.isModalOpen = true;
                },

                openEditModal(village) {
                    this.isEditMode = true;
                    const baseUrl = "{{ route('central.villages.index') }}";
                    this.formUrl = baseUrl + '/' + village.id;

                    this.formData = {
                        village_name: village.village_name || '',
                        pincode: village.pincode || '',
                        post_so_name: village.post_so_name || '',
                        taluka_name: village.taluka_name || '',
                        district_name: village.district_name || '',
                        state_name: village.state_name || 'Gujarat'
                    };
                    this.isModalOpen = true;
                },

                init() {
                    @if($errors->any())
                        this.formData = {
                            village_name: "{{ old('village_name') }}",
                            pincode: "{{ old('pincode') }}",
                            post_so_name: "{{ old('post_so_name') }}",
                            taluka_name: "{{ old('taluka_name') }}",
                            district_name: "{{ old('district_name') }}",
                            state_name: "{{ old('state_name', 'Gujarat') }}"
                        };

                        this.isEditMode = false;
                        this.formUrl = "{{ route('central.villages.store') }}";
                        this.isModalOpen = true;
                    @endif
                                }
            }));
        });

        // AJAX Data Table Logic for Search & Pagination
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('villages-table-container');
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
                    const newContent = doc.getElementById('villages-table-container');

                    if (newContent) {
                        container.innerHTML = newContent.innerHTML;
                        if (pushState) window.history.pushState({}, '', url);

                        // Re-initialize Alpine.js for dropdowns within the new table
                        if (typeof Alpine !== 'undefined') {
                            Alpine.initTree(container);
                        }
                    } else {
                        window.location.href = url;
                    }
                } catch (err) {
                    console.error('AJAX Error:', err);
                    window.location.href = url;
                } finally {
                    if (loading) loading.style.opacity = '0';
                }
            }

            // Browser Back/Forward
            window.addEventListener('popstate', () => loadContent(window.location.href, false));

            // Pagination Clicks
            container.addEventListener('click', (e) => {
                const link = e.target.closest('a.page-link') ||
                    e.target.closest('nav[role="navigation"] a') ||
                    e.target.closest('.pagination a');

                if (link && container.contains(link) && link.href) {
                    e.preventDefault();
                    loadContent(link.href);
                }
            });

            // Debounced Form Input & Change
            const handleSearchChange = (e) => {
                const form = e.target.closest('form');
                if (form && form.id === 'search-form') {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const url = new URL(form.action);
                        const params = new URLSearchParams(new FormData(form));

                        // Clean up empty params for a cleaner URL
                        for (const [key, value] of Array.from(params.entries())) {
                            if (!value) params.delete(key);
                        }

                        loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                    }, 400); // 400ms delay for typing, instant enough for selects
                }
            };

            document.addEventListener('input', handleSearchChange);
            document.addEventListener('change', handleSearchChange);

            // Prevent Form Submission (Enter key)
            document.addEventListener('submit', (e) => {
                if (e.target.id === 'search-form') {
                    e.preventDefault();
                    const form = e.target;
                    const url = new URL(form.action);
                    const params = new URLSearchParams(new FormData(form));
                    loadContent(`${url.origin}${url.pathname}?${params.toString()}`);
                }
            });
        });
    </script>
@endsection