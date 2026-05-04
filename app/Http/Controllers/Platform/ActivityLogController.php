<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ActivityLogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Require specific permission or admin role
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('activity-logs view')) {
            abort(403);
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = ($perPage > 0 && $perPage <= 100) ? $perPage : 10;

        $activities = Activity::with(['causer', 'subject'])->latest()->paginate($perPage)->withQueryString();

        return view('tenant.activity.index', compact('activities'));
    }

    /**
     * Clear all activity logs.
     */
    public function clear(): RedirectResponse
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('activity-logs delete')) {
            abort(403);
        }

        Activity::truncate();

        return redirect()->back()->with('success', 'All activity logs have been cleared successfully.');
    }
}
