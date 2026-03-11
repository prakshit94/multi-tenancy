<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderVerification;
use App\Services\OrderService;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderVerificationController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): View
    {
        $this->authorize('orders view');

        $states = Village::distinct()->pluck('state_name')->filter()->sort()->values();
        
        $districts = Village::when($request->filled('state'), function ($q) use ($request) {
            return $q->where('state_name', $request->state);
        })->distinct()->pluck('district_name')->filter()->sort()->values();

        $talukas = Village::when($request->filled('district'), function ($q) use ($request) {
            return $q->where('district_name', $request->district);
        })->distinct()->pluck('taluka_name')->filter()->sort()->values();

        $status = $request->input('status', 'unverified');

        $sortDirection = $request->input('sort_direction', 'desc');

        $query = Order::with([
            'customer',
            'items',
            'verifications.user',
            'billingAddress',
            'shippingAddress'
        ])->orderBy('placed_at', $sortDirection);

        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER (existing logic)
        |--------------------------------------------------------------------------
        */

        if ($status === 'scheduled') {
            $query->where('is_future_order', true);
        } else {
            $query->where('is_future_order', false);

            if ($status === 'unverified') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('verification_status', 'unverified')
                            ->orWhereNull('verification_status');
                    })->where('status', 'pending');
                });
            } elseif ($status === 'pending_followup') {
                $query->where('verification_status', 'pending_followup');
            } elseif ($status === 'verified') {
                $query->where('verification_status', 'verified')
                    ->where('status', 'confirmed');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('grand_total', 'like', "%{$search}%")

                    ->orWhereHas('customer', function ($c) use ($search) {

                        $c->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");

                    })

                    ->orWhereHas('items', function ($i) use ($search) {

                        $i->where('product_name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");

                    });

            });

        }

        /*
        |--------------------------------------------------------------------------
        | DATE FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        /*
        |--------------------------------------------------------------------------
        | DISTRICT COUNTS (Insight)
        |--------------------------------------------------------------------------
        */

        $districtCounts = (clone $query)
            ->join('customer_addresses', 'orders.shipping_address_id', '=', 'customer_addresses.id')
            ->select('customer_addresses.district', DB::raw('count(orders.id) as total'))
            ->groupBy('customer_addresses.district')
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STATE FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->filled('state')) {
            $state = trim($request->state);
            $query->where(function ($q) use ($state) {
                $q->whereHas('shippingAddress', function ($sub) use ($state) {
                    $sub->where('state', 'like', "%{$state}%");
                })
                    ->orWhereHas('billingAddress', function ($sub) use ($state) {
                        $sub->where('state', 'like', "%{$state}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ADDITIONAL REGIONAL FILTERS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('district')) {
            $district = trim($request->district);
            $query->where(function ($q) use ($district) {
                $q->whereHas('shippingAddress', function ($sub) use ($district) {
                    $sub->where('district', 'like', "%{$district}%");
                })
                    ->orWhereHas('billingAddress', function ($sub) use ($district) {
                        $sub->where('district', 'like', "%{$district}%");
                    });
            });
        }

        if ($request->filled('taluka')) {
            $taluka = trim($request->taluka);
            $query->where(function ($q) use ($taluka) {
                $q->whereHas('shippingAddress', function ($sub) use ($taluka) {
                    $sub->where('taluka', 'like', "%{$taluka}%");
                })
                    ->orWhereHas('billingAddress', function ($sub) use ($taluka) {
                        $sub->where('taluka', 'like', "%{$taluka}%");
                    });
            });
        }

        if ($request->filled('village')) {
            $village = trim($request->village);
            $query->where(function ($q) use ($village) {
                $q->whereHas('shippingAddress', function ($sub) use ($village) {
                    $sub->where('village', 'like', "%{$village}%");
                })
                    ->orWhereHas('billingAddress', function ($sub) use ($village) {
                        $sub->where('village', 'like', "%{$village}%");
                    });
            });
        }

        $orders = $query->paginate($request->get('per_page', 10))
            ->withQueryString();

        return view('central.orders.verification.index', compact('orders', 'states', 'districts', 'talukas', 'districtCounts'));
    }

    public function store(Request $request, Order $order)
    {
        $this->authorize('orders edit');

        $validated = $request->validate([
            'status' => 'required|in:verified,pending_followup,rejected',
            'remarks' => 'required|string',
            'next_followup_at' => 'nullable|date|after:now',
        ]);

        try {
            DB::transaction(function () use ($validated, $order) {
                // Create verification entry
                OrderVerification::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'status' => $validated['status'],
                    'remarks' => $validated['remarks'],
                    'next_followup_at' => $validated['next_followup_at'] ?? null,
                ]);

                // Update order verification status
                $order->update([
                    'verification_status' => $validated['status']
                ]);

                // If verified, we might want to automatically confirm the order if it's pending
                if ($validated['status'] === 'verified' && $order->status === 'pending') {
                    $this->orderService->confirmOrder($order);
                }

                // If rejected, we might want to cancel the order
                if ($validated['status'] === 'rejected' && $order->status !== 'cancelled') {
                    $this->orderService->cancelOrder($order);
                }
            });

            return redirect()->route('central.orders.verification.index')
                ->with('success', 'Order verification updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating verification: ' . $e->getMessage());
        }
    }
}
