@extends('layouts.app')

@section('content')
<div class="p-6 lg:p-8 max-w-7xl mx-auto space-y-6" x-data="{ showClearModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-heading tracking-tight">Activity Logs</h1>
            <p class="text-muted-foreground text-sm">Audit trail of all system activities and events.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasRole('admin') || auth()->user()->can('activity-logs delete'))
            <button @click="showClearModal = true" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Clear All Logs
            </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-muted/50 border-b border-border">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-muted-foreground">Date & Time</th>
                        <th class="px-6 py-4 font-semibold text-muted-foreground">User / Causer</th>
                        <th class="px-6 py-4 font-semibold text-muted-foreground">Event</th>
                        <th class="px-6 py-4 font-semibold text-muted-foreground">Subject</th>
                        <th class="px-6 py-4 font-semibold text-muted-foreground">Description</th>
                        <th class="px-6 py-4 font-semibold text-muted-foreground">Properties / Changes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($activities as $activity)
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-foreground">{{ $activity->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-muted-foreground">{{ $activity->created_at->format('h:i:s A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($activity->causer)
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">
                                        {{ substr($activity->causer->name, 0, 2) }}
                                    </div>
                                    <span class="font-semibold">{{ $activity->causer->name }}</span>
                                </div>
                            @else
                                <span class="font-semibold italic text-muted-foreground">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold uppercase tracking-wider border
                                {{ $activity->event === 'created' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                                  ($activity->event === 'updated' ? 'bg-blue-50 text-blue-700 border-blue-200' : 
                                  ($activity->event === 'deleted' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-gray-50 text-gray-700 border-gray-200')) }}">
                                {{ $activity->event }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($activity->subject_type)
                                <div class="font-medium">{{ class_basename($activity->subject_type) }}</div>
                                <div class="text-xs text-muted-foreground">ID: {{ $activity->subject_id }}</div>
                            @else
                                <span class="text-muted-foreground">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-foreground">
                            {{ $activity->description }}
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            @if($activity->properties && $activity->properties->count() > 0)
                                <div x-data="{ expanded: false }" class="relative">
                                    <button @click="expanded = !expanded" class="text-xs font-medium text-primary hover:underline focus:outline-none">
                                        <span x-show="!expanded">View Changes</span>
                                        <span x-show="expanded" x-cloak>Hide Changes</span>
                                    </button>
                                    <div x-show="expanded" x-transition x-cloak class="mt-2 text-[10px] sm:text-xs font-mono bg-muted/30 p-2 rounded border border-border/50 overflow-x-auto">
                                        <pre>{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted-foreground text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-muted-foreground italic">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <svg class="w-10 h-10 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <span>No activity logs found.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-border bg-muted/20">
            {{ $activities->links() }}
        </div>
    </div>

    <!-- Clear Modal -->
    <div x-show="showClearModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm" x-transition.opacity>
        <div @click.outside="showClearModal = false" class="bg-card w-full max-w-md p-6 rounded-2xl shadow-xl border border-border" x-transition.scale.origin.bottom>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-foreground">Clear All Logs?</h3>
                    <p class="text-sm text-muted-foreground mt-1">This action cannot be undone. All activity tracking history will be permanently deleted from the database.</p>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 mt-6">
                <button @click="showClearModal = false" type="button" class="px-4 py-2 text-sm font-semibold text-foreground bg-muted hover:bg-muted/80 rounded-lg transition-colors">
                    Cancel
                </button>
                <form action="{{ route('central.activity-logs.clear') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm">
                        Yes, Delete All
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    window.addEventListener('per-page-change', (e) => {
        if (e.detail && e.detail.value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', e.detail.value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
    });
</script>
@endpush
@endsection
