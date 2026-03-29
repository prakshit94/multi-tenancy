<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Shipment;
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

        $baseQuery = Order::query();

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $baseQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shipments', function ($s) use ($search) {
                        $s->where('tracking_number', 'like', "%{$search}%");
                    });
            });
        }
        // Date Filters
        if ($request->filled('start_date')) {
            $baseQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('created_at', '<=', $request->end_date);
        }

        // Courier Filter
        if ($request->filled('courier')) {
            $baseQuery->whereHas('shipments', function ($q) use ($request) {
                $q->where('carrier', $request->courier);
            });
        }

        // Tab Counts
        $counts = [
            'shipped' => (clone $baseQuery)->where('status', 'shipped')->where('shipping_status', 'shipped')
                ->whereDoesntHave('trackings', function ($q) {
                    $q->where('status', 'attempt_failed')
                        ->whereRaw('id = (select max(id) from order_trackings where order_id = orders.id)');
                })->count(),
            'attempt_failed' => (clone $baseQuery)->where('status', 'shipped')
                ->whereHas('trackings', function ($q) {
                    $q->where('status', 'attempt_failed')
                        ->whereRaw('id = (select max(id) from order_trackings where order_id = orders.id)');
                })->count(),
            'delivered' => (clone $baseQuery)->where(function ($q) {
                $q->where('shipping_status', 'delivered')->orWhere('status', 'completed');
            })->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'returned')->count(),
            'all' => (clone $baseQuery)->where(function ($q) {
                $q->whereIn('shipping_status', ['shipped', 'partially_shipped', 'delivered'])
                    ->orWhere('status', 'completed');
            })->count(),
        ];

        $query = (clone $baseQuery)->with(['customer', 'items', 'trackings.user', 'shippingAddress', 'billingAddress', 'shipments'])
            ->latest();

        // Apply Shipping Status Filter
        if ($status === 'shipped') {
            $query->where('status', 'shipped')->where('shipping_status', 'shipped')
                ->whereDoesntHave('trackings', function ($q) {
                    $q->where('status', 'attempt_failed')
                        ->whereRaw('id = (select max(id) from order_trackings where order_id = orders.id)');
                });
        } elseif ($status === 'delivered') {
            // Orders that have successfully reached the customer
            $query->where('shipping_status', 'delivered')
                ->orWhere('status', 'completed');
        } elseif ($status === 'attempt_failed') {
            // Orders where the MOST RECENT delivery attempt failed
            $query->where('status', 'shipped')
                ->whereHas('trackings', function ($q) {
                    $q->where('status', 'attempt_failed')
                        ->whereRaw('id = (select max(id) from order_trackings where order_id = orders.id)');
                });
        } elseif ($status === 'cancelled') {
            $query->where('status', 'returned');
        } elseif ($status === 'all') {
            // All orders that have at least started shipping (or are already completed)
            $query->whereIn('shipping_status', ['shipped', 'partially_shipped', 'delivered'])
                ->orWhere('status', 'completed');
        }

        $orders = $query->paginate($request->get('per_page', 10))->withQueryString();

        $couriers = Shipment::distinct()->whereNotNull('carrier')->pluck('carrier')->sort()->values();

        return view('central.orders.tracking.index', compact('orders', 'couriers', 'counts'));
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

            return back()->with('success', 'Order tracking status updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating tracking status: ' . $e->getMessage());
        }
    }
}
