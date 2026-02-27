<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): View
    {
        $this->authorize('orders view');

        // Default to 'shipped' if no status is provided
        $status = $request->input('status', 'shipped');

        $query = Order::with(['customer', 'items', 'trackings.user', 'shippingAddress'])
            ->latest();

        // Apply Shipping Status Filter
        if ($status === 'shipped') {
            // Orders that have left the warehouse but are not yet delivered
            $query->whereIn('shipping_status', ['shipped', 'partially_shipped'])
                ->where('status', '!=', 'completed');
        } elseif ($status === 'delivered') {
            // Orders that have successfully reached the customer
            $query->where('shipping_status', 'delivered')
                ->orWhere('status', 'completed');
        } elseif ($status === 'attempt_failed') {
            // Orders where a delivery was attempted but failed
            $query->whereHas('trackings', function ($q) {
                $q->where('status', 'attempt_failed');
            })->where('status', '!=', 'completed');
        } elseif ($status === 'all') {
            // All orders that have at least started shipping (or are already completed)
            $query->whereIn('shipping_status', ['shipped', 'partially_shipped', 'delivered'])
                ->orWhere('status', 'completed');
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('grand_total', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate($request->get('per_page', 10))->withQueryString();

        return view('central.orders.tracking.index', compact('orders'));
    }

    public function store(Request $request, Order $order)
    {
        $this->authorize('orders edit');

        $validated = $request->validate([
            'status' => 'required|in:delivered,en_route,attempt_failed',
            'remarks' => 'required|string',
            'next_followup_at' => 'nullable|date|after:now',
        ]);

        try {
            DB::transaction(function () use ($validated, $order) {
                // 1. Create tracking interaction log
                OrderTracking::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'status' => $validated['status'],
                    'remarks' => $validated['remarks'],
                    'next_followup_at' => $validated['next_followup_at'] ?? null,
                ]);

                // 2. If the status is truly delivered, conclude the order lifecycle
                if ($validated['status'] === 'delivered' && $order->status === 'shipped') {
                    $this->orderService->deliverOrder($order);
                }
            });

            return redirect()->route('central.orders.tracking.index')
                ->with('success', 'Order tracking status updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating tracking status: ' . $e->getMessage());
        }
    }
}
