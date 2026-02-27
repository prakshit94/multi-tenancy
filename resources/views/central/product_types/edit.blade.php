@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('central.product_types.index') }}"
                class="p-2 rounded-lg hover:bg-muted text-muted-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold font-heading tracking-tight text-foreground">Edit Product Type</h1>
                <p class="text-muted-foreground text-sm">Update details for {{ $productType->name }}.</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="rounded-xl border border-border/50 bg-card/50 backdrop-blur-sm shadow-sm overflow-hidden p-6">
            <form action="{{ route('central.product_types.update', $productType) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="space-y-2">
                    <label
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Product
                        Type
                        Name</label>
                    <input type="text" name="name" value="{{ old('name', $productType->name) }}"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        required>
                    @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Status -->
                <div class="flex items-center space-x-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                        class="h-4 w-4 rounded border-primary text-primary shadow focus:ring-primary" {{ $productType->is_active ? 'checked' : '' }}>
                    <label for="is_active"
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">Active</label>
                </div>

                <!-- Global Errors -->
                @if ($errors->any())
                    <div class="p-3 rounded-lg bg-red-500/10 text-red-600 border border-red-500/20 text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="pt-4 flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline"
                        href="{{ route('central.product_types.index') }}">Cancel</x-ui.button>
                    <x-ui.button type="submit">Update Product Type</x-ui.button>
                </div>
            </form>
        </div>
    </div>
@endsection