<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Models\Order;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class OrderReturnController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of central order returns.
     */
    public function index(Request $request): View
    {
        $this->authorize('returns view');

        $query = OrderReturn::with(['order.customer', 'items.product']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('rma_number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        } else {
            $query->where('status', 'requested');
        }

        $perPage = (int) $request->input('per_page', 10);
        $returns = $query->latest()->paginate($perPage)->withQueryString();

        // Stats for the tabs
        $stats = [
            'all' => OrderReturn::count(),
            'requested' => OrderReturn::where('status', 'requested')->count(),
            'approved' => OrderReturn::where('status', 'approved')->count(),
            'received' => OrderReturn::where('status', 'received')->count(),
            'refunded' => OrderReturn::where('status', 'refunded')->count(),
            'rejected' => OrderReturn::where('status', 'rejected')->count(),
            'completed' => OrderReturn::where('status', 'completed')->count(),
            'eligible_orders' => Order::whereIn('status', ['shipped', 'delivered'])->count(),
        ];

        return view('central.returns.index', compact('returns', 'stats'));
    }

    /**
     * Show the form for creating a new return.
     */
    public function create(Request $request): View
    {
        $this->authorize('returns create');

        $preSelectedOrderId = $request->query('order_id');
        $orders = Order::whereIn('status', ['shipped', 'delivered'])->latest()->limit(50)->get();

        $preSelectedOrder = null;
        if ($preSelectedOrderId) {
            $preSelectedOrder = Order::with(['items.product', 'returns.items'])->find($preSelectedOrderId);
            if ($preSelectedOrder) {
                if (!in_array($preSelectedOrder->status, ['shipped', 'delivered'])) {
                    $status = $preSelectedOrder->status;
                    $preSelectedOrder = null;
                    session()->flash('error', 'Selected order is not eligible for return (Status: ' . ucfirst($status ?? 'Unknown') . ')');
                } else {
                    // Calculate available quantities
                    $returnedQuantities = [];
                    foreach ($preSelectedOrder->returns as $rma) {
                        if ($rma->status === 'rejected')
                            continue;
                        foreach ($rma->items as $rmaItem) {
                            if (!isset($returnedQuantities[$rmaItem->product_id])) {
                                $returnedQuantities[$rmaItem->product_id] = 0;
                            }
                            $returnedQuantities[$rmaItem->product_id] += $rmaItem->quantity;
                        }
                    }

                    collect($preSelectedOrder->items)->transform(function ($item) use ($returnedQuantities) {
                        $qtyReturned = $returnedQuantities[$item->product_id] ?? 0;
                        $item->available_quantity = max(0, $item->quantity - $qtyReturned);
                        return $item;
                    });
                }
            }
        }

        return view('central.returns.create', compact('orders', 'preSelectedOrderId', 'preSelectedOrder'));
    }

    /**
     * Store a newly created return in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('returns create');

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.condition' => 'required|in:sellable,damaged',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $order = Order::with('items.product')->findOrFail($validated['order_id']);

                if (!in_array($order->status, ['shipped', 'delivered'])) {
                    throw new Exception("Returns are only allowed for Shipped or Delivered orders. Current status: " . ucfirst($order->status));
                }

                // Calculate already returned quantities for this order (excluding rejected returns)
                $existingReturns = OrderReturn::with('items')
                    ->where('order_id', $order->id)
                    ->where('status', '!=', 'rejected')
                    ->get();

                $returnedQuantities = [];
                foreach ($existingReturns as $existingReturn) {
                    foreach ($existingReturn->items as $item) {
                        if (!isset($returnedQuantities[$item->product_id])) {
                            $returnedQuantities[$item->product_id] = 0;
                        }
                        $returnedQuantities[$item->product_id] += $item->quantity;
                    }
                }

                // Validate requested quantities against available quantities
                foreach ($validated['items'] as $requestedItem) {
                    // Get ALL line items for this product to calculate total purchaesd qty
                    $orderItems = collect($order->items)->where('product_id', $requestedItem['product_id']);

                    if ($orderItems->isEmpty()) {
                        throw new Exception("Product ID {$requestedItem['product_id']} does not belong to this order.");
                    }

                    $totalPurchasedQty = (float) $orderItems->sum('quantity');
                    $productName = $orderItems->first()->product->name ?? 'Unknown Product';

                    $previouslyReturned = (float) ($returnedQuantities[$requestedItem['product_id']] ?? 0);

                    // Collect RMA numbers for this product
                    $blockingRmas = $existingReturns->filter(function ($rma) use ($requestedItem) {
                        return $rma->items->where('product_id', $requestedItem['product_id'])->isNotEmpty();
                    })->pluck('rma_number')->implode(', ');

                    // Rounding to avoid floating point issues
                    $availableQty = round($totalPurchasedQty - $previouslyReturned, 3);
                    $requestedQty = (float) $requestedItem['quantity'];

                    if ($requestedQty > $availableQty) {
                        $errorMsg = "Cannot return " . (int) $requestedQty . " of {$productName}. Only " . (int) $availableQty . " available. (Purchased: " . (int) $totalPurchasedQty . ", Previously Returned/Requested: " . (int) $previouslyReturned . ")";
                        if (!empty($blockingRmas)) {
                            $errorMsg .= " [Blocking RMAs: {$blockingRmas}]";
                        }
                        throw new Exception($errorMsg);
                    }
                }

                $rma = OrderReturn::create([
                    'rma_number' => 'RMA-' . strtoupper(Str::random(8)),
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'status' => 'requested',
                    'reason' => $validated['reason'],
                    'refund_method' => 'credit',
                ]);

                foreach ($validated['items'] as $item) {
                    $rma->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'condition' => $item['condition'],
                    ]);
                }

                // Sync Order Status
                app(\App\Services\OrderService::class)->returnOrder($order);
            });

            return redirect()->route('central.returns.index')->with('success', 'RMA Requested.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to request RMA: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified return.
     */
    public function show(OrderReturn $return): View
    {
        $this->authorize('returns view');
        $return->load(['items.product', 'order.customer']);
        return view('central.returns.show', ['orderReturn' => $return]);
    }

    /**
     * Update the status of the return request.
     */
    public function updateStatus(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        $this->authorize('returns manage');
        $validated = $request->validate(['status' => 'required|in:approved,received,refunded,rejected,completed']);

        try {
            DB::transaction(function () use ($validated, $orderReturn) {
                $orderReturn->update(['status' => $validated['status']]);

                if ($validated['status'] === 'received') {
                    // Logic already handled in receiveReturn if using processing controller, 
                    // otherwise if manually updated here without processing:
                }

                // Sync Order Status if terminal state
                if (in_array($validated['status'], ['approved', 'received', 'refunded'])) {
                    app(\App\Services\OrderService::class)->returnOrder($orderReturn->order);
                }
            });

            return back()->with('success', 'RMA Status Updated.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to update RMA status: ' . $e->getMessage());
        }
    }
    /**
     * Show the form for editing the specified return.
     */
    public function edit(OrderReturn $return): View|RedirectResponse
    {
        $this->authorize('returns edit');

        if ($return->status !== 'requested') {
            return redirect()->route('central.returns.show', $return)
                ->with('error', 'Only "Requested" returns can be edited.');
        }

        $return->load(['items.product', 'order.items.product']);

        return view('central.returns.edit', ['orderReturn' => $return]);
    }

    /**
     * Update the specified return in storage.
     */
    public function update(Request $request, OrderReturn $return): RedirectResponse
    {
        $this->authorize('returns edit');

        if ($return->status !== 'requested') {
            return back()->with('error', 'Only "Requested" returns can be edited.');
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.condition' => 'required|in:sellable,damaged',
        ]);

        try {
            DB::transaction(function () use ($validated, $return) {
                // Update basic details
                $return->update([
                    'reason' => $validated['reason'],
                ]);

                // Sync items: 
                // 1. Delete items not in the new list
                // 2. Update/Create items from the new list

                // Get current items
                $currentItems = $return->items->keyBy('product_id');
                $newProductIds = collect($validated['items'])->pluck('product_id')->toArray();

                // Delete removed items
                $return->items()->whereNotIn('product_id', $newProductIds)->delete();

                // Update or Create
                foreach ($validated['items'] as $itemData) {
                    $return->items()->updateOrCreate(
                        ['product_id' => $itemData['product_id']],
                        [
                            'quantity' => $itemData['quantity'],
                            'condition' => $itemData['condition']
                        ]
                    );
                }
            });

            return redirect()->route('central.returns.index')->with('success', 'RMA Updated Successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update RMA: ' . $e->getMessage());
        }
    }
    /**
     * Show the form for inspecting the return items.
     */
    public function inspect(OrderReturn $return): View|RedirectResponse
    {
        $this->authorize('returns inspect');

        if ($return->status !== 'approved') {
            return redirect()->route('central.returns.show', $return)
                ->with('error', 'Only "Approved" returns can be inspected.');
        }

        $return->load(['items.product', 'order']);

        return view('central.returns.inspect', ['orderReturn' => $return]);
    }

    /**
     * Store the inspection results and update stock.
     */
    public function storeInspection(Request $request, OrderReturn $return): RedirectResponse
    {
        $this->authorize('returns inspect');

        if ($return->status !== 'approved') {
            return back()->with('error', 'Only "Approved" returns can be inspected.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:return_items,id',
            'items.*.condition' => 'required|in:sellable,damaged',
            'items.*.verified' => 'required|accepted',
        ]);

        try {
            DB::transaction(function () use ($validated, $return) {
                foreach ($validated['items'] as $inspectedItem) {
                    $item = $return->items()->findOrFail($inspectedItem['item_id']);

                    // Update Item Record with inspected data
                    $item->update([
                        'condition_received' => $inspectedItem['condition'],
                        'quantity_received' => $item->quantity, // For now, assume full quantity is received if verified
                    ]);

                    // Restock Logic (Only for Sellable Received Items)
                    if ($inspectedItem['condition'] === 'sellable') {
                        $warehouseId = $return->order->warehouse_id;
                        if ($warehouseId) {
                            $stock = InventoryStock::firstOrCreate(
                                ['warehouse_id' => $warehouseId, 'product_id' => $item->product_id],
                                ['quantity' => 0]
                            );

                            // Cast to int for safety
                            $stock->increment('quantity', (int) $item->quantity);

                            // Refresh Product Denormalized Stock
                            $item->product->refreshStockOnHand();

                            InventoryMovement::create([
                                'stock_id' => $stock->id,
                                'type' => 'return',
                                'quantity' => $item->quantity,
                                'reference_id' => $return->id,
                                'reason' => 'RMA Received & Inspected: ' . $return->rma_number,
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }
                }

                $return->update([
                    'status' => 'received',
                    'inspected_by' => auth()->id(),
                    'inspected_at' => now(),
                ]);
            });

            return redirect()->route('central.returns.index')->with('success', 'RMA Processed & Stock Updated.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to process inspection: ' . $e->getMessage());
        }
    }
    /**
     * Show the form for processing the refund.
     */
    public function refund(OrderReturn $return): View|RedirectResponse
    {
        $this->authorize('returns manage');

        if ($return->status !== 'received') {
            return redirect()->route('central.returns.show', $return)
                ->with('error', 'Only "Received" returns can be refunded.');
        }

        $return->load(['items.product', 'order']);

        return view('central.returns.refund', ['orderReturn' => $return]);
    }

    /**
     * Store the refund details.
     */
    public function storeRefund(Request $request, OrderReturn $return): RedirectResponse
    {
        $this->authorize('returns manage');

        if ($return->status !== 'received') {
            return back()->with('error', 'Only "Received" returns can be refunded.');
        }

        $validated = $request->validate([
            'refunded_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $return) {
                // Record the refund in the return record
                $return->update([
                    'status' => 'refunded',
                    'refunded_amount' => $validated['refunded_amount'],
                ]);

                // Sync Customer Balance
                $customer = $return->order->customer;
                if ($customer) {
                    $customer->increment('outstanding_balance', $validated['refunded_amount']);
                }

                // Create a Payment Record (as a refund payment for tracking)
                \App\Models\Payment::create([
                    'order_id' => $return->order_id,
                    'amount' => $validated['refunded_amount'], // Stored as positive as requested
                    'method' => 'refund',
                    'paid_at' => now(),
                    'notes' => $validated['notes'] ?? 'Order Return Refund: ' . $return->rma_number,
                ]);
            });

            return redirect()->route('central.returns.show', $return)->with('success', 'Refund Processed Successfully and Customer Balance Updated.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }
}
