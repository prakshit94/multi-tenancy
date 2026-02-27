<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class UnitController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('products view');

        $query = Unit::query();

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
        $units = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('central.units.index', compact('units'));
    }

    public function create(): View
    {
        $this->authorize('products create');
        return view('central.units.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('products create');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Unit::create($validated);
            });

            return redirect()->route('central.units.index')
                ->with('success', 'Unit created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create unit: ' . $e->getMessage());
        }
    }

    public function edit(Unit $unit): View
    {
        $this->authorize('products edit');
        return view('central.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorize('products edit');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->ignore($unit->id),
            ],
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $unit) {
                $unit->update($validated);
            });

            return redirect()->route('central.units.index')
                ->with('success', 'Unit updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update unit: ' . $e->getMessage());
        }
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $this->authorize('products delete');

        // Check for products using this unit
        if (DB::table('products')->where('unit_type', $unit->name)->exists()) {
            return back()->with('error', 'Cannot delete unit associated with existing products.');
        }

        try {
            $unit->delete();
            return redirect()->route('central.units.index')
                ->with('success', 'Unit deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete unit: ' . $e->getMessage());
        }
    }
}
