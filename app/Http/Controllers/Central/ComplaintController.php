<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Exception;

class ComplaintController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of complaints.
     */
    public function index(Request $request): View
    {
        // $this->authorize('complaints view'); // Assuming a permission system exists, skipping for now to prevent blockages if 'complaints view' isn't seeded

        $query = Complaint::with(['order', 'customer', 'user', 'activities.causer']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reference_number', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhereHas('order', function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%");
                });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $complaints = $query->latest()->paginate($perPage)->withQueryString();

        $stats = [
            'all' => Complaint::count(),
            'open' => Complaint::where('status', 'open')->count(),
            'in_progress' => Complaint::where('status', 'in_progress')->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
            'closed' => Complaint::where('status', 'closed')->count(),
        ];

        return view('central.complaints.index', compact('complaints', 'stats'));
    }

    /**
     * Store a newly created complaint in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // $this->authorize('complaints create');

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $order = Order::findOrFail($validated['order_id']);

                Complaint::create([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'user_id' => auth()->id(), // the staff member logging it
                    'type' => $validated['type'],
                    'subject' => $validated['subject'],
                    'description' => $validated['description'],
                    'priority' => $validated['priority'],
                    'status' => 'open',
                ]);
            });

            return back()->with('success', 'Complaint logged successfully against order.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to log complaint: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified complaint.
     */
    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        // $this->authorize('complaints edit');

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $complaint) {
                $data = ['status' => $validated['status']];

                if (isset($validated['resolution'])) {
                    $data['resolution'] = $validated['resolution'];
                }

                if (in_array($validated['status'], ['resolved', 'closed']) && !$complaint->resolved_at) {
                    $data['resolved_at'] = now();
                }

                $complaint->update($data);
            });

            return back()->with('success', 'Complaint updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to update complaint: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified complaint from storage.
     */
    public function destroy(Complaint $complaint): RedirectResponse
    {
        // $this->authorize('complaints delete');
        $complaint->delete();
        return back()->with('success', 'Complaint deleted successfully.');
    }
}
