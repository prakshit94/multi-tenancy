@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.categories.index') }}"
                class="p-2 rounded-lg hover:bg-muted text-muted-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold font-heading tracking-tight text-foreground">Edit Category</h1>
                <p class="text-muted-foreground text-sm">Update details for {{ $category->name }}.</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="rounded-xl border border-border/50 bg-card/50 backdrop-blur-sm shadow-sm overflow-hidden p-6">
            <form action="{{ route('tenant.categories.update', $category) }}" method="POST" enctype="multipart/form-data"
                class="space-y-6" x-data="{ name: '{{ addslashes($category->name) }}', slug: '{{ $category->slug }}' }">
                @csrf
                @method('PUT')

                <!-- Name & Slug -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Category
                            Name</label>
                        <input type="text" name="name" x-model="name"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            required>
                        @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Slug</label>
                        <div class="relative">
                            <input type="text" name="slug" x-model="slug"
                                class="flex h-10 w-full rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>
                        @error('slug') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-muted-foreground">Changing the slug may affect SEO links.</p>
                    </div>
                </div>

                <!-- Parent Category -->
                <div class="space-y-2">
                    <label
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Parent
                        Category</label>
                    <select name="parent_id"
                        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">None (Root Category)</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Description</label>
                    <textarea name="description" rows="3"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ $category->description }}</textarea>
                </div>

                <!-- Media & Display -->
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="space-y-2">
                        <label
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Category
                            Icon/Image</label>
                        <input type="file" name="image" accept="image/*"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <p class="text-[10px] text-muted-foreground">Square image (e.g. 400x400px)</p>
                        @if($category->image)
                            <div class="mt-2">
                                <span class="text-xs text-muted-foreground block mb-1">Current Image:</span>
                                <img src="{{ asset('storage/' . $category->image) }}" alt="Image"
                                    class="h-16 w-16 rounded border object-cover">
                            </div>
                        @endif
                        @error('image') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Banner
                            Image</label>
                        <input type="file" name="banner_image" accept="image/*"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <p class="text-[10px] text-muted-foreground">Recommended size: 1200x400px</p>
                        @if($category->banner_image)
                            <div class="mt-2">
                                <span class="text-xs text-muted-foreground block mb-1">Current Banner:</span>
                                <img src="{{ asset('storage/' . $category->banner_image) }}" alt="Banner"
                                    class="h-16 w-auto rounded border object-cover">
                            </div>
                        @endif
                        @error('banner_image') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Sort
                            Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}"
                            min="0"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            required>
                        <p class="text-[10px] text-muted-foreground">Lower numbers appear first.</p>
                        @error('sort_order') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- SEO Data -->
                <div class="grid gap-6 md:grid-cols-2 pt-2 border-t mt-4">
                    <div class="space-y-2">
                        <label
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Meta
                            Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="SEO Title">
                        @error('meta_title') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Meta
                            Description</label>
                        <textarea name="meta_description" rows="2"
                            class="flex min-h-[40px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="SEO Description...">{{ old('meta_description', $category->meta_description) }}</textarea>
                        @error('meta_description') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Status & Flags -->
                <div class="space-y-4 border rounded-lg p-4 bg-muted/20">
                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                            class="h-4 w-4 rounded border-primary text-primary shadow focus:ring-primary" {{ $category->is_active ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm font-medium leading-none cursor-pointer">Active (Visible in
                            Store)</label>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" id="is_featured"
                            class="h-4 w-4 rounded border-primary text-primary shadow focus:ring-primary" {{ $category->is_featured ? 'checked' : '' }}>
                        <label for="is_featured" class="text-sm font-medium leading-none cursor-pointer">Featured
                            Category</label>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_menu" value="0">
                        <input type="checkbox" name="is_menu" value="1" id="is_menu"
                            class="h-4 w-4 rounded border-primary text-primary shadow focus:ring-primary" {{ $category->is_menu ? 'checked' : '' }}>
                        <label for="is_menu" class="text-sm font-medium leading-none cursor-pointer">Show in Main
                            Menu</label>
                    </div>
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
                        href="{{ route('tenant.categories.index') }}">Cancel</x-ui.button>
                    <x-ui.button type="submit">Update Category</x-ui.button>
                </div>
            </form>
        </div>
    </div>
@endsection