<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Village;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderProcessingController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * List orders that need processing (Confirmed, Processing, Ready to Ship)
     */
    /**
     * List orders with filtering capabilities
     */
    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'items', 'warehouse', 'shipments', 'invoices'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->search($search);
                    });
            });
        }

        // Date Filtering
        if ($request->filled('date_filter')) {
            $dateFilter = $request->input('date_filter');
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', now());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', now()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->input('start_date'));
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->input('end_date'));
                    }
                    break;
            }
        }

        // Regional Filtering
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

        $countsQuery = clone $query;
        $counts = $countsQuery->reorder()->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $activeCount = ($counts['confirmed'] ?? 0) + ($counts['processing'] ?? 0);
        $counts['active'] = $activeCount;
        $counts['all'] = array_sum(array_diff_key($counts, ['active' => 0, 'all' => 0]));

        // Default: Show confirmed orders if no status is selected
        if (!$request->has('status')) {
            $query->where('status', 'confirmed');
        }
        // If status is provided
        elseif ($request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(50)->withQueryString();


        $districtCounts = Order::query()
    ->join('customer_addresses', 'orders.shipping_address_id', '=', 'customer_addresses.id')
    ->select('customer_addresses.district', DB::raw('count(orders.id) as total'))
    ->groupBy('customer_addresses.district')
    ->orderByDesc('total')
    ->get();

        $states = Village::distinct()->pluck('state_name')->filter()->sort()->values();

        $districts = Village::when($request->filled('state'), function ($q) use ($request) {
            return $q->where('state_name', $request->state);
        })->distinct()->pluck('district_name')->filter()->sort()->values();

        $talukas = Village::when($request->filled('district'), function ($q) use ($request) {
            return $q->where('district_name', $request->district);
        })->distinct()->pluck('taluka_name')->filter()->sort()->values();

        if ($request->ajax()) {
            return view('central.processing.orders.partials.orders-content', compact('orders', 'counts', 'districtCounts', 'districts', 'talukas'));
        }

        return view('central.processing.orders.index', compact('orders', 'counts', 'states', 'districts', 'talukas', 'districtCounts'));
    }

    /**
     * Mark order as Processing (Confirmed -> Processing)
     */
    public function process(Order $order): RedirectResponse|JsonResponse
    {
        try {
            if ($order->status !== 'confirmed') {
                throw new Exception('Order must be Confirmed before Processing.');
            }

            $this->orderService->validateStockForProcessing($order);

            $order->update([
                'status' => 'processing',
                'shipping_status' => 'pending',
                'updated_by' => auth()->id(),
            ]);

            // Generate Invoice and COD on Confirmed to Processing transition
            if ($order->invoices()->doesntExist()) {
                Invoice::create([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                    'issue_date' => now(),
                    'due_date' => now(),
                    'total_amount' => $order->grand_total,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                ]);
            }

            // Eager load relationships for the modal
            $order->refresh()->load(['customer', 'items.product', 'shippingAddress']);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order marked as Processing.',
                    'order' => $order,
                ]);
            }

            return redirect()->route('central.processing.orders.index')
                ->with('success', 'Order marked as Processing.')
                ->with('processed_order', $order);
        } catch (Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * Mark order as Ready to Ship (Processing -> Ready to Ship)
     * Also generates invoice.
     */
    public function readyToShip(Request $request, Order $order): RedirectResponse
{
    $validated = $request->validate([
        'courier' => 'required|string|max:255',
        'tracking_number' => 'required|string|max:255',
    ]);

    try {

        DB::transaction(function () use ($order, $validated) {

            if ($order->status !== 'processing') {
                throw new Exception('Order must be Processing before Ready to Ship.');
            }

            // Create shipment record only (NO stock deduction)
            $order->shipments()->create([
                'warehouse_id' => $order->warehouse_id,
                'tracking_number' => $validated['tracking_number'],
                'carrier' => $validated['courier'],
                'status' => 'pending',
            ]);

            $order->update([
                'status' => 'ready_to_ship',
                'shipping_status' => 'pending',
                'updated_by' => auth()->id(),
            ]);

        });

        return back()->with('success', 'Order marked as Ready to Ship.');

    } catch (Exception $e) {

        return back()->with('error', 'Error updating order: ' . $e->getMessage());
    }
}

    /**
     * Bulk Dispatch via CSV
     */
    public function bulkDispatch(Request $request): RedirectResponse
{
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt',
    ]);

    $file = $request->file('csv_file');

    if (!$file->isValid()) {
        return back()->with('error', 'Invalid file uploaded.');
    }

    $handle = fopen($file->getPathname(), 'r');

    if (!$handle) {
        return back()->with('error', 'Unable to read CSV file.');
    }

    $header = fgetcsv($handle);

    if (!$header) {
        fclose($handle);
        return back()->with('error', 'CSV file is empty or invalid.');
    }

    // Normalize headers
    $header = array_map(fn($h) => strtolower(trim($h)), $header);

    $required = ['order_number']; // Only order_number is required, tracking is already populated in ready to ship
    $missing = array_diff($required, $header);

    if (!empty($missing)) {
        fclose($handle);
        return back()->with(
            'error',
            'CSV is missing required columns: ' . implode(', ', $missing)
        );
    }

    $indices = array_flip($header);

    $successCount = 0;
    $failCount = 0;
    $errors = [];

    while (($row = fgetcsv($handle)) !== false) {

        if (empty(array_filter($row))) {
            continue;
        }

        $orderNumber = $row[$indices['order_number']] ?? null;
        $courier = isset($indices['courier']) ? ($row[$indices['courier']] ?? null) : null;
        $tracking = isset($indices['tracking_number']) ? ($row[$indices['tracking_number']] ?? null) : null;

        if (!$orderNumber) {
            $failCount++;
            $errors[] = "Invalid row data for order. Missing order number.";
            continue;
        }

        try {

            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                $failCount++;
                $errors[] = "Order {$orderNumber} not found.";
                continue;
            }

            // Only allow dispatch from Ready to Ship
            if ($order->status !== 'ready_to_ship') {
                $failCount++;
                $errors[] = "Order {$orderNumber} must be Ready to Ship before dispatch.";
                continue;
            }

            DB::transaction(function () use ($order, $tracking, $courier) {
                $this->orderService->shipOrder(
                    $order,
                    $tracking,
                    $courier
                );
            });

            $successCount++;

        } catch (Exception $e) {

            $failCount++;
            $errors[] = "Error dispatching {$orderNumber}: " . $e->getMessage();
        }
    }

    fclose($handle);

    $message = "Bulk Dispatch Completed: {$successCount} successful, {$failCount} failed.";

    if ($failCount > 0) {

        $errorMsg = implode(' | ', array_slice($errors, 0, 3));

        if (count($errors) > 3) {
            $errorMsg .= '...';
        }

        return back()->with('warning', "{$message} Errors: {$errorMsg}");
    }

    return back()->with('success', $message);
}
    /**
     * Dispatch Order (Ready to Ship -> Shipped)
     * Requires courier details.
     */
    public function dispatch(Order $order): RedirectResponse
{
    try {

        if ($order->status !== 'ready_to_ship') {
            throw new Exception('Order must be Ready to Ship before Dispatching.');
        }

        // Dispatch order (shipment already contains courier + tracking)
        $this->orderService->shipOrder($order);

        return back()->with('success', 'Order Dispatched (Shipped) successfully.');

    } catch (Exception $e) {

        return back()->with('error', 'Error dispatching order: ' . $e->getMessage());
    }
}

    /**
     * List approved returns that need to be received
     */
    public function indexReturns(): View
    {
        // specific requirements: receved itemes and aonly and acoirdng to update the inventory
        // So we show 'approved' returns (waiting to be received)
        // and 'received' returns (for history)

        $returns = OrderReturn::with(['order.customer', 'items.product'])
            ->whereIn('status', ['approved', 'received'])
            ->latest()
            ->paginate(10);

        return view('central.processing.returns.index', compact('returns'));
    }

    /**
     * Receive Returned Items
     * Updates return status to 'received' and increments inventory.
     */
    public function receiveReturn(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        try {
            if ($orderReturn->status !== 'approved') {
                throw new Exception("Return must be Approved before receiving items.");
            }

            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|exists:return_items,id',
                'items.*.condition' => 'required|in:sellable,damaged',
            ]);

            DB::transaction(function () use ($orderReturn, $validated) {
                // Update Return Status
                $orderReturn->update(['status' => 'received']);

                // Process each item from the request
                foreach ($validated['items'] as $itemData) {
                    $item = $orderReturn->items()->find($itemData['id']);

                    if (!$item)
                        continue;

                    // Update condition in DB
                    $item->update(['condition' => $itemData['condition']]);

                    // Only add to inventory if sellable
                    if ($itemData['condition'] === 'sellable') {
                        $warehouseId = $orderReturn->order->warehouse_id;

                        // Find or Create Stock record
                        $stock = InventoryStock::firstOrCreate(
                            ['warehouse_id' => $warehouseId, 'product_id' => $item->product_id],
                            ['quantity' => 0, 'reserve_quantity' => 0]
                        );

                        // Increment Quantity
                        $stock->increment('quantity', $item->quantity);

                        // Refresh Product Denormalized Stock
                        $item->product->refreshStockOnHand();

                        // Log Movement
                        InventoryMovement::create([
                            'stock_id' => $stock->id,
                            'type' => 'return',
                            'quantity' => $item->quantity,
                            'reference_id' => $orderReturn->id,
                            'reason' => 'Return Received (RMA: ' . $orderReturn->rma_number . ') - Restocked',
                            'user_id' => auth()->id(),
                        ]);
                    } else {
                        // Log the scrap/damaged decision effectively by absence of movement or explicit log if needed
                        // For now, we just don't add stock.
                    }
                }

                // Sync Order Status
                app(\App\Services\OrderService::class)->returnOrder($orderReturn->order);
            });

            return back()->with('success', 'Return items received. Inventory updated based on condition.');

        } catch (Exception $e) {
            return back()->with('error', 'Error receiving return: ' . $e->getMessage());
        }
    }
    /**
     * Bulk Print Invoices or COD Receipts
     */
    public function bulkPrint(Request $request)
    {
        // $this->authorize('orders view'); // Ensure this permission exists or use appropriate one

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id',
            'type' => 'required|in:invoice,cod',
        ]);

        $orders = Order::whereIn('id', $validated['ids'])
            ->with(['items.product', 'customer', 'billingAddress', 'shippingAddress', 'invoices'])
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'No orders selected.');
        }

        if ($validated['type'] === 'invoice') {
            // Filter orders that have invoices
            $ordersWithInvoices = $orders->filter(function ($order) {
                return $order->invoices->isNotEmpty();
            });

            // Flatten to get all invoices
            $invoices = new \Illuminate\Database\Eloquent\Collection($orders->pluck('invoices')->flatten());

            if ($invoices->isEmpty()) {
                return back()->with('error', 'No invoices found for selected orders. Ensure orders are Ready to Ship.');
            }

            // Load relations for invoices
            $invoices->load(['order.customer', 'order.billingAddress', 'order.shippingAddress', 'order.items']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('central.invoices.bulk_invoice', compact('invoices'))->setPaper('a5', 'portrait');
            return $pdf->download('bulk-invoices-' . now()->format('YmdHis') . '.pdf');

        } elseif ($validated['type'] === 'cod') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('central.receipts.bulk_cod', compact('orders'))->setPaper('a5', 'portrait');
            return $pdf->download('bulk-cod-' . now()->format('YmdHis') . '.pdf');
        }
    }

    /**
     * Bulk Status Update
     */
    public function bulkStatusUpdate(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:orders,id',
        'status' => 'required|in:confirmed,processing,ready_to_ship,shipped,delivered,cancelled',
    ]);

    try {

        DB::beginTransaction();

        $orders = Order::whereIn('id', $validated['ids'])->get();
        $failedOrders = [];

        foreach ($orders as $order) {

            try {

                switch ($validated['status']) {

                    case 'processing':

                        if ($order->status !== 'confirmed') {
                            throw new Exception();
                        }

                        $this->orderService->validateStockForProcessing($order);

                        $order->update([
                            'status' => 'processing',
                            'updated_by' => auth()->id(),
                        ]);

                        if ($order->invoices()->doesntExist()) {

                            Invoice::create([
                                'order_id' => $order->id,
                                'customer_id' => $order->customer_id,
                                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                                'issue_date' => now(),
                                'due_date' => now(),
                                'total_amount' => $order->grand_total,
                                'paid_amount' => 0,
                                'status' => 'unpaid',
                            ]);
                        }

                        break;

                    case 'ready_to_ship':

                        if ($order->status !== 'processing') {
                            throw new Exception();
                        }

                        $order->update([
                            'status' => 'ready_to_ship',
                            'updated_by' => auth()->id(),
                        ]);

                        break;

                    case 'shipped':

                        if ($order->status !== 'ready_to_ship') {
                            throw new Exception();
                        }

                        $this->orderService->shipOrder($order);

                        break;

                    case 'delivered':

                        if ($order->status !== 'shipped') {
                            throw new Exception();
                        }

                        $order->update([
                            'status' => 'delivered',
                            'updated_by' => auth()->id(),
                        ]);

                        break;

                    case 'cancelled':

                        if (in_array($order->status, ['delivered', 'cancelled'])) {
                            throw new Exception();
                        }

                        $order->update([
                            'status' => 'cancelled',
                            'updated_by' => auth()->id(),
                        ]);

                        break;
                }

            } catch (Exception $e) {

                $failedOrders[] = $order->order_number;

            }

        }

        DB::commit();

        if (!empty($failedOrders)) {

            return back()->with(
                'error',
                'Some orders could not be updated: ' . implode(', ', $failedOrders)
            );
        }

        return back()->with('success', 'Bulk status update completed successfully.');

    } catch (Exception $e) {

        DB::rollBack();

        return back()->with('error', 'Error updating orders: ' . $e->getMessage());
    }
}
}
