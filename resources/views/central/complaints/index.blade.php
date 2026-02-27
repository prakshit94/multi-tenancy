@extends('layouts.app')

@section('content')
    <div x-data="{ 
            showModal: false,
            currentComplaint: null,
            actionUrl: '',
            status: '',
            resolution: '',

            edit(complaint) {
                this.currentComplaint = complaint;
                this.status = complaint.status;
                this.resolution = complaint.resolution || '';
                this.actionUrl = '{{ route('central.complaints.update', ':id') }}'.replace(':id', complaint.id);
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
                setTimeout(() => {
                    this.currentComplaint = null;
                    this.status = '';
                    this.resolution = '';
                }, 300);
            }
        }" class="flex flex-col space-y-8 p-8 max-w-[1600px] mx-auto w-full animate-in fade-in duration-500">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <h1
                    class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                    Order Complaints
                </h1>
                <p class="text-muted-foreground text-sm font-medium">Manage and resolve customer complaints regarding their
                    orders.</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gray-50 text-gray-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <path d="M14 2v6h6" />
                            <path d="M16 13H8" />
                            <path d="M16 17H8" />
                            <path d="M10 9H8" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Total Cases</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['all'] }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v4" />
                            <path d="M12 16h.01" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Open</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['open'] }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">In Progress</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['in_progress'] }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Resolved</span>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $stats['resolved'] }}</div>
            </div>
        </div>

        <!-- Filters & Toolbar -->
        <div
            class="flex flex-col md:flex-row items-center justify-between gap-4 sticky top-0 z-10 bg-gray-50/95 backdrop-blur-xl p-2 rounded-2xl border border-gray-200/60 shadow-sm">
            <div class="flex items-center gap-1 overflow-x-auto no-scrollbar w-full md:w-auto">
                @php
                    $filters = [
                        ['label' => 'All', 'value' => null],
                        ['label' => 'Open', 'value' => 'open'],
                        ['label' => 'In Progress', 'value' => 'in_progress'],
                        ['label' => 'Resolved', 'value' => 'resolved'],
                        ['label' => 'Closed', 'value' => 'closed'],
                    ];
                @endphp
                @foreach($filters as $filter)
                    <a href="{{ route('central.complaints.index', ['status' => $filter['value']]) }}"
                        class="px-4 py-2 rounded-xl text-sm font-medium transition-all whitespace-nowrap {{ request('status') == $filter['value'] ? 'bg-white text-gray-900 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                        {{ $filter['label'] }}
                    </a>
                @endforeach
            </div>

            <form action="{{ url()->current() }}" method="GET" class="relative group w-full md:w-72">
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search Ref #, Order, Subject..."
                    class="w-full h-10 pl-10 pr-4 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm outline-none">
            </form>
        </div>

        <!-- Content Area -->
        <div class="space-y-4">
            @forelse($complaints as $complaint)
                <div
                    class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-primary/20 transition-all duration-300 overflow-hidden relative">
                    <!-- Status Accent Line -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 
                                {{ $complaint->status === 'open' ? 'bg-amber-400' :
                ($complaint->status === 'in_progress' ? 'bg-blue-500' :
                    ($complaint->status === 'resolved' ? 'bg-emerald-500' :
                        ($complaint->status === 'closed' ? 'bg-gray-500' : 'bg-gray-300'))) }}"></div>

                    <!-- Priority indicator top right corner -->
                    <div class="absolute top-0 right-0 overflow-hidden w-16 h-16 pointer-events-none opacity-80"
                        x-show="'{{ $complaint->priority }}' === 'urgent' || '{{ $complaint->priority }}' === 'high'">
                        <div class="absolute top-4 -right-6 origin-center rotate-45 py-1 px-8 text-[9px] font-bold uppercase tracking-wider text-white shadow-sm flex items-center justify-center
                                    {{ $complaint->priority === 'urgent' ? 'bg-red-500' : 'bg-orange-500' }}">
                            {{ $complaint->priority }}
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- Left: Info -->
                            <div class="flex-1 space-y-4">
                                <!-- Header -->
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-lg font-bold text-gray-900 tracking-tight">{{ $complaint->subject }}
                                            </h3>
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest
                                                        {{ $complaint->status === 'open' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-500/20' :
                ($complaint->status === 'in_progress' ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-500/20' :
                    ($complaint->status === 'resolved' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-500/20' :
                        ($complaint->status === 'closed' ? 'bg-gray-100 text-gray-700 ring-1 ring-gray-400/30' : 'bg-gray-50 text-gray-700'))) }}">
                                                {{ str_replace('_', ' ', $complaint->status) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 font-medium flex items-center gap-2">
                                            <span>Ref: <span
                                                    class="text-gray-900">{{ $complaint->reference_number }}</span></span>
                                            <span>•</span>
                                            <span>Order: <a href="{{ route('central.orders.show', $complaint->order_id) }}"
                                                    class="text-primary hover:underline font-bold">#{{ $complaint->order->order_number ?? 'N/A' }}</a></span>
                                            <span>•</span>
                                            <span>Logged {{ $complaint->created_at->diffForHumans() }}</span>
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if($complaint->status !== 'closed')
                                            <button @click="edit(@js($complaint))"
                                                class="px-4 py-2 text-xs font-semibold bg-gray-900 text-white rounded-xl hover:bg-black transition-all shadow-sm flex items-center gap-1.5">
                                                Update Case
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="m18 15-6-6-6 6" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Customer details inside a compact badge -->
                                <div
                                    class="flex items-center gap-2 inline-flex bg-gray-50 rounded-lg p-1.5 border border-gray-100">
                                    <div
                                        class="h-6 w-6 rounded border border-gray-200 bg-white flex items-center justify-center text-[10px] font-bold text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                    </div>
                                    <span
                                        class="text-xs font-medium text-gray-700 pr-2">{{ $complaint->customer->first_name ?? 'Guest' }}
                                        {{ $complaint->customer->last_name ?? '' }}</span>
                                </div>
                                <div
                                    class="flex items-center gap-2 inline-flex bg-gray-50 rounded-lg p-1.5 border border-gray-100">
                                    <div
                                        class="h-6 w-6 rounded border border-gray-200 bg-white flex items-center justify-center text-[10px] font-bold text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 pr-2 uppercase">{{ $complaint->type }}</span>
                                </div>

                                <!-- Description -->
                                <div
                                    class="bg-gray-50/50 p-4 rounded-xl border border-dashed border-gray-200 text-sm text-gray-700 leading-relaxed italic">
                                    "{{ $complaint->description }}"
                                </div>

                                <!-- Resolution if resolved -->
                                @if($complaint->resolution)
                                    <div
                                        class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100 text-sm text-emerald-800 leading-relaxed mt-4 relative">
                                        <div
                                            class="absolute -top-2 -left-2 bg-emerald-100 text-emerald-600 rounded-full p-1 border border-emerald-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                        </div>
                                        <span class="font-bold text-emerald-900 block mb-1">Resolution Provided:</span>
                                        <span class="opacity-90">{{ $complaint->resolution }}</span>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border border-dashed border-gray-300">
                    <div
                        class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mb-6 text-emerald-500 border-8 border-emerald-50/50">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900">Zero Complaints!</h3>
                    <p class="text-gray-500 text-sm mt-1">Excellent job, all orders are running perfectly.</p>
                </div>
            @endforelse

            <div class="mt-8">
                {{ $complaints->links() }}
            </div>
        </div>

        <!-- Action / Edit Modal -->
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
            x-show="showModal" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            <div x-show="showModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden" @click.away="closeModal()">

                <form :action="actionUrl" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Form content in one clean column -->
                    <div class="p-8 space-y-6">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 tracking-tight">Update Case</h3>
                                <p class="text-sm text-gray-500 font-medium mt-1" x-text="currentComplaint?.subject"></p>
                            </div>
                            <div class="h-10 w-10 flex items-center justify-center rounded-2xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-900 cursor-pointer transition-colors"
                                @click="closeModal()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M18 6 6 18M6 6l12 12" />
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-2">Case
                                    Status</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" x-model="status" name="status" value="open"
                                            class="peer sr-only">
                                        <div
                                            class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold hover:bg-gray-50 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 peer-focus:ring-2 peer-focus:ring-amber-500/20 transition-all text-center">
                                            Open
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" x-model="status" name="status" value="in_progress"
                                            class="peer sr-only">
                                        <div
                                            class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-focus:ring-2 peer-focus:ring-blue-500/20 transition-all text-center">
                                            In Progress
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" x-model="status" name="status" value="resolved"
                                            class="peer sr-only">
                                        <div
                                            class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold hover:bg-gray-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-focus:ring-2 peer-focus:ring-emerald-500/20 transition-all text-center">
                                            Resolved
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" x-model="status" name="status" value="closed"
                                            class="peer sr-only">
                                        <div
                                            class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold hover:bg-gray-50 peer-checked:border-gray-500 peer-checked:bg-gray-100 peer-checked:text-gray-700 peer-focus:ring-2 peer-focus:ring-gray-500/20 transition-all text-center">
                                            Closed
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div x-show="status === 'resolved' || status === 'closed'" x-transition class="space-y-2">
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-widest text-gray-500">Resolution
                                    Summary (Required)</label>
                                <textarea name="resolution" x-model="resolution" rows="3"
                                    :required="status === 'resolved' || status === 'closed'"
                                    placeholder="Explain how this issue was resolved..."
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all resize-none shadow-sm"></textarea>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex gap-3">
                            <button type="button" @click="closeModal()"
                                class="flex-1 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex-[2] py-3 bg-gray-900 text-white font-black rounded-xl hover:bg-black hover:-translate-y-0.5 shadow-xl shadow-gray-900/10 transition-all text-center">
                                Save Updates
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection