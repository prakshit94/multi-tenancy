<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class ProductTypeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('products view');

        $query = ProductType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $perPage = (int) $request->input('per_page', 10);
        $productTypes = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('central.product_types.index', compact('productTypes'));
    }

    public function create(): View
    {
        $this->authorize('products create');
        return view('central.product_types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('products create');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_types,name',
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                ProductType::create($validated);
            });

            return redirect()->route('central.product_types.index')
                ->with('success', 'Product Type created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create product type: ' . $e->getMessage());
        }
    }

    public function edit(ProductType $productType): View
    {
        $this->authorize('products edit');
        return view('central.product_types.edit', compact('productType'));
    }

    public function update(Request $request, ProductType $productType): RedirectResponse
    {
        $this->authorize('products edit');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_types')->ignore($productType->id),
            ],
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $productType) {
                $productType->update($validated);
            });

            return redirect()->route('central.product_types.index')
                ->with('success', 'Product Type updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update product type: ' . $e->getMessage());
        }
    }

    public function destroy(ProductType $productType): RedirectResponse
    {
        $this->authorize('products delete');

        // Check for products using this product type
        if (DB::table('products')->where('type', $productType->name)->exists()) {
            return back()->with('error', 'Cannot delete product type associated with existing products.');
        }

        try {
            $productType->delete();
            return redirect()->route('central.product_types.index')
                ->with('success', 'Product Type deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete product type: ' . $e->getMessage());
        }
    }
}
