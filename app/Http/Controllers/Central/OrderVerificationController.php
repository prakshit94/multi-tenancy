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

        // 🔥 Optimized dropdown queries (no repeated scans)
        $states = Village::query()
            ->select('state_name')
            ->distinct()
            ->whereNotNull('state_name')
            ->orderBy('state_name')
            ->pluck('state_name');

        $districts = Village::query()
            ->when($request->filled('state'), function($q) use ($request) {
                $states = is_array($request->state) ? $request->state : [$request->state];
                return $q->whereIn('state_name', $states);
            })
            ->select('district_name')
            ->distinct()
            ->whereNotNull('district_name')
            ->orderBy('district_name')
            ->pluck('district_name');

        if ($request->filled('district')) {
            $reqDistricts = is_array($request->district) ? $request->district : [$request->district];
            $validDistricts = array_values(array_intersect($reqDistricts, $districts->toArray()));
            $request->merge(['district' => empty($validDistricts) ? null : $validDistricts]);
        }

        $talukas = Village::query()
            ->when($request->filled('district'), function($q) use ($request) {
                $districts = is_array($request->district) ? $request->district : [$request->district];
                return $q->whereIn('district_name', $districts);
            })
            ->select('taluka_name')
            ->distinct()
            ->whereNotNull('taluka_name')
            ->orderBy('taluka_name')
            ->pluck('taluka_name');

        if ($request->filled('taluka')) {
            $reqTalukas = is_array($request->taluka) ? $request->taluka : [$request->taluka];
            $validTalukas = array_values(array_intersect($reqTalukas, $talukas->toArray()));
            $request->merge(['taluka' => empty($validTalukas) ? null : $validTalukas]);
        }

        $status = $request->input('status', 'unverified');
        $sortDirection = $request->input('sort_direction', 'desc');

        // 🔥 Base query optimized
        $query = Order::query()
            ->with([
                'customer',
                'items.product',
                'verifications.user',
                'billingAddress',
                'shippingAddress',
                'creator'
            ])
            ->orderBy('placed_at', $sortDirection);

        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER (UNCHANGED LOGIC)
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
                $query->where('verification_status', 'pending_followup')
                    ->where('status', 'pending');
            } elseif ($status === 'verified') {
                $query->where('status', 'confirmed')
                    ->where(function ($q) {
                        $q->where('verification_status', 'verified')
                            ->orWhere('verification_status', 'unverified')
                            ->orWhereNull('verification_status');
                    });
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SEARCH (UNCHANGED, JUST CLEANED)
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
        | DISTRICT COUNTS (OPTIMIZED)
        |--------------------------------------------------------------------------
        */

        $districtCounts = (clone $query)
    ->reorder() // 🔥 THIS LINE FIXES YOUR ERROR
    ->join('customer_addresses', 'orders.shipping_address_id', '=', 'customer_addresses.id')
    ->selectRaw('customer_addresses.district, COUNT(orders.id) as total')
    ->groupBy('customer_addresses.district')
    ->orderByDesc('total')
    ->get();

        /*
        |--------------------------------------------------------------------------
        | REGION FILTERS (UNCHANGED)
        |--------------------------------------------------------------------------
        */

        if ($request->filled('state')) {
            $statesParam = is_array($request->state) ? $request->state : [$request->state];
            $query->where(function ($block) use ($statesParam) {
                foreach ($statesParam as $st) {
                    $st = trim($st);
                    $block->orWhere(function ($q) use ($st) {
                        $q->whereHas('shippingAddress', fn($sub) =>
                            $sub->where('state', 'like', "%{$st}%")
                        )->orWhereHas('billingAddress', fn($sub) =>
                            $sub->where('state', 'like', "%{$st}%")
                        );
                    });
                }
            });
        }

        if ($request->filled('district')) {
            $districtsParam = is_array($request->district) ? $request->district : [$request->district];
            $query->where(function ($block) use ($districtsParam) {
                foreach ($districtsParam as $dist) {
                    $dist = trim($dist);
                    $block->orWhere(function ($q) use ($dist) {
                        $q->whereHas('shippingAddress', fn($sub) =>
                            $sub->where('district', 'like', "%{$dist}%")
                        )->orWhereHas('billingAddress', fn($sub) =>
                            $sub->where('district', 'like', "%{$dist}%")
                        );
                    });
                }
            });
        }

        if ($request->filled('taluka')) {
            $talukasParam = is_array($request->taluka) ? $request->taluka : [$request->taluka];
            $query->where(function ($block) use ($talukasParam) {
                foreach ($talukasParam as $tal) {
                    $tal = trim($tal);
                    $block->orWhere(function ($q) use ($tal) {
                        $q->whereHas('shippingAddress', fn($sub) =>
                            $sub->where('taluka', 'like', "%{$tal}%")
                        )->orWhereHas('billingAddress', fn($sub) =>
                            $sub->where('taluka', 'like', "%{$tal}%")
                        );
                    });
                }
            });
        }

        if ($request->filled('village')) {
            $village = trim($request->village);
            $query->where(function ($q) use ($village) {
                $q->whereHas('shippingAddress', fn($sub) =>
                    $sub->where('village', 'like', "%{$village}%")
                )->orWhereHas('billingAddress', fn($sub) =>
                    $sub->where('village', 'like', "%{$village}%")
                );
            });
        }

        // 🔥 Pagination optimized
        $orders = $query->paginate($request->get('per_page', 10))
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | INVENTORY AVL QTY CHECK — same logic as InventoryController
        | sellable_qty = stock_on_hand - pending_order_qty
        | Any product whose sellable_qty <= 0 blocks the Verify button.
        |--------------------------------------------------------------------------
        */
        $orderProductIds = $orders->flatMap(fn($o) => $o->items->pluck('product_id'))
            ->filter()
            ->unique()
            ->values();

        $zeroAvlProductIds = [];

        if ($orderProductIds->isNotEmpty()) {
            // Sum of quantities already reserved by active orders (same statuses as InventoryController)
            $pendingQtys = \App\Models\OrderItem::whereIn('product_id', $orderProductIds)
    ->whereHas('order', function ($q) {
        $q->whereIn('status', ['confirmed', 'processing', 'ready_to_ship']);
    })
                ->selectRaw('product_id, SUM(quantity) as total_pending')
                ->groupBy('product_id')
                ->pluck('total_pending', 'product_id');

            $zeroAvlProductIds = \App\Models\Product::whereIn('id', $orderProductIds)
                ->get(['id', 'stock_on_hand'])
                ->filter(function ($product) use ($pendingQtys) {
                    $pending  = (float) $pendingQtys->get($product->id, 0);
                    $sellable = (float) $product->stock_on_hand - $pending;
                    return $sellable <= 0;
                })
                ->pluck('id')
                ->toArray();
        }

        return view('central.orders.verification.index', compact(
            'orders',
            'states',
            'districts',
            'talukas',
            'districtCounts',
            'zeroAvlProductIds'
        ));
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

                OrderVerification::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'status' => $validated['status'],
                    'remarks' => $validated['remarks'],
                    'next_followup_at' => $validated['next_followup_at'] ?? null,
                ]);

                $order->update([
                    'verification_status' => $validated['status']
                ]);

                if ($validated['status'] === 'verified' && $order->status === 'pending') {
                    $this->orderService->confirmOrder($order);
                }

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