<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the categories.
     */
    public function index(): View
    {
        $this->authorize('products view');

        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(10);

        return view('tenant.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $this->authorize('products create');

        $parents = Category::where('parent_id', null)->orderBy('name')->get();
        return view('tenant.categories.create', compact('parents'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('products create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:2048',
            'sort_order' => 'integer|min:0',
            'is_featured' => 'boolean',
            'is_menu' => 'boolean',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('categories', 'public');
        }

        try {
            DB::transaction(function () use ($validated) {
                Category::create($validated);
            });

            return redirect()->route('tenant.categories.index')
                ->with('success', 'Category created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create category.');
        }
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $this->authorize('products edit');

        $parents = Category::where('parent_id', null)
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('tenant.categories.edit', compact('category', 'parents'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('products edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($category->id),
            ],
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:2048',
            'sort_order' => 'integer|min:0',
            'is_featured' => 'boolean',
            'is_menu' => 'boolean',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            $validated['parent_id'] = null;
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        if ($request->hasFile('banner_image')) {
            if ($category->banner_image) {
                Storage::disk('public')->delete($category->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('categories', 'public');
        }

        try {
            DB::transaction(function () use ($category, $validated) {
                $category->update($validated);
            });

            return redirect()->route('tenant.categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update category.');
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('products delete');

        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete category with associated products.');
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete category that has sub-categories.');
        }

        try {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            if ($category->banner_image) {
                Storage::disk('public')->delete($category->banner_image);
            }

            $category->delete();

            return redirect()->route('tenant.categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }
}
