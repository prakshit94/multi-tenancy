<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Village;
use Illuminate\Http\Request;

class VillageManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Village::query();

        // 1. Text Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('village_name', 'like', "%{$search}%")
                    ->orWhere('pincode', 'like', "%{$search}%")
                    ->orWhere('taluka_name', 'like', "%{$search}%")
                    ->orWhere('district_name', 'like', "%{$search}%")
                    ->orWhere('state_name', 'like', "%{$search}%");
            });
        }

        // 2. Dropdown Filters
        if ($request->filled('state_name')) {
            $query->where('state_name', $request->state_name);
        }
        if ($request->filled('district_name')) {
            $query->where('district_name', $request->district_name);
        }

        $villages = $query->orderBy('village_name')->paginate(15)->withQueryString();

        // Distinct lists for the filter dropdowns
        $states = Village::select('state_name')->whereNotNull('state_name')->where('state_name', '!=', '')->distinct()->orderBy('state_name')->pluck('state_name');

        // Only load districts relevant to the selected state (if any) or all distinct districts
        $districtQuery = Village::select('district_name')->whereNotNull('district_name')->where('district_name', '!=', '')->distinct()->orderBy('district_name');
        if ($request->filled('state_name')) {
            $districtQuery->where('state_name', $request->state_name);
        }
        $districts = $districtQuery->pluck('district_name');

        return view('central.villages.index', compact('villages', 'states', 'districts'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'village_name' => 'required|string|max:255',
            'pincode' => 'required|string|max:6',
            'post_so_name' => 'nullable|string|max:255',
            'taluka_name' => 'nullable|string|max:255',
            'district_name' => 'nullable|string|max:255',
            'state_name' => 'nullable|string|max:255',
        ]);

        Village::create($validated);

        return redirect()->route('central.villages.index')->with('success', 'Village created successfully.');
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Village $village)
    {
        $validated = $request->validate([
            'village_name' => 'required|string|max:255',
            'pincode' => 'required|string|max:6',
            'post_so_name' => 'nullable|string|max:255',
            'taluka_name' => 'nullable|string|max:255',
            'district_name' => 'nullable|string|max:255',
            'state_name' => 'nullable|string|max:255',
        ]);

        $village->update($validated);

        return redirect()->route('central.villages.index')->with('success', 'Village updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Village $village)
    {
        $village->delete();

        return redirect()->route('central.villages.index')->with('success', 'Village deleted successfully.');
    }
}
