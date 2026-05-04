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
    $query = Order::with([
        'customer',
        'items.product',
        'warehouse',
        'shipments',
        'invoices'
    ])->latest();

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */
    if ($request->filled('search')) {
        $search = trim($request->input('search'));

        $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
                ->orWhere('id', $search)
                ->orWhereHas('customer', fn($c) => $c->search($search));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT FILTER
    |--------------------------------------------------------------------------
    */
    if ($request->filled('product')) {
        $products = array_filter(array_map('trim', explode(',', $request->product)));

        $query->whereHas('items.product', function ($q) use ($products) {
            $q->whereIn('name', $products);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | DATE FILTER
    |--------------------------------------------------------------------------
    */
    if ($request->filled('date_filter')) {
        $dateFilter = $request->input('date_filter');

        match ($dateFilter) {
            'today' => $query->whereDate('created_at', now()),
            'yesterday' => $query->whereDate('created_at', now()->subDay()),
            'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year),
            'custom' => $query
                ->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
                ->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->end_date)),
            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | REGION FILTER (Reusable)
    |--------------------------------------------------------------------------
    */
    $applyAddressFilter = function ($field, $values) use ($query) {
        $query->where(function ($q) use ($field, $values) {
            $q->whereHas('shippingAddress', fn($sub) => $sub->whereIn($field, $values))
              ->orWhereHas('billingAddress', fn($sub) => $sub->whereIn($field, $values));
        });
    };

    if ($request->filled('state')) {
        $applyAddressFilter('state', array_map('trim', explode(',', $request->state)));
    }

    if ($request->filled('district')) {
        $applyAddressFilter('district', array_map('trim', explode(',', $request->district)));
    }

    if ($request->filled('taluka')) {
        $applyAddressFilter('taluka', array_map('trim', explode(',', $request->taluka)));
    }

    if ($request->filled('village')) {
        $village = trim($request->village);

        $query->where(function ($q) use ($village) {
            $q->whereHas('shippingAddress', fn($sub) => $sub->where('village', 'like', "%{$village}%"))
              ->orWhereHas('billingAddress', fn($sub) => $sub->where('village', 'like', "%{$village}%"));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | DISPATCH FILTER
    |--------------------------------------------------------------------------
    */
    if ($request->filled('courier')) {
        $couriers = array_filter(array_map('trim', explode(',', $request->courier)));

        $query->whereHas('shipments', fn($q) => $q->whereIn('carrier', $couriers));
    }

    if ($request->filled('tracking_number')) {
        $tracking = trim($request->tracking_number);

        $query->whereHas('shipments', fn($q) => $q->where('tracking_number', 'like', "%{$tracking}%"));
    }

    /*
    |--------------------------------------------------------------------------
    | STATS (CLONE SAFE)
    |--------------------------------------------------------------------------
    */
    $stats = (clone $query)
        ->reorder()
        ->select(
            'status',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(grand_total) as amount')
        )
        ->groupBy('status')
        ->get();

    $counts = $stats->pluck('total', 'status')->toArray();
    $amounts = $stats->pluck('amount', 'status')->toArray();

    $counts['active'] = ($counts['confirmed'] ?? 0) + ($counts['processing'] ?? 0);
    $counts['all'] = array_sum($counts);

    $amounts['active'] = ($amounts['confirmed'] ?? 0) + ($amounts['processing'] ?? 0);
    $amounts['all'] = array_sum($amounts);

    /*
    |--------------------------------------------------------------------------
    | STATUS FILTER
    |--------------------------------------------------------------------------
    */
    if (!$request->has('status')) {
        $query->where('status', 'confirmed');
    } elseif ($request->status !== 'all') {
        $query->where('status', $request->status);
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */
    $orders = $query->paginate((int) $request->input('per_page', 15))->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | DISTRICT COUNTS
    |--------------------------------------------------------------------------
    */
    $districtCounts = (clone $query)
    ->reorder()
    ->leftJoin('customer_addresses as sa', 'orders.shipping_address_id', '=', 'sa.id')
    ->leftJoin('customer_addresses as ba', 'orders.billing_address_id', '=', 'ba.id')
    ->selectRaw('COALESCE(sa.district, ba.district) as district, COUNT(orders.id) as total')
    ->groupBy('district')
    ->orderByDesc('total')
    ->get();

    /*
    |--------------------------------------------------------------------------
    | FILTER DROPDOWNS
    |--------------------------------------------------------------------------
    */
    $states = Village::distinct()->pluck('state_name')->filter()->sort()->values();

    $districts = Village::when($request->filled('state'), fn($q) =>
        $q->whereIn('state_name', array_map('trim', explode(',', $request->state)))
    )->distinct()->pluck('district_name')->filter()->sort()->values();

    $talukas = Village::when($request->filled('district'), fn($q) =>
        $q->whereIn('district_name', array_map('trim', explode(',', $request->district)))
    )->distinct()->pluck('taluka_name')->filter()->sort()->values();

    $couriers = \App\Models\Shipment::distinct()
        ->whereNotNull('carrier')
        ->pluck('carrier')
        ->sort()
        ->values();

    $products = \App\Models\Product::distinct()
        ->pluck('name')
        ->filter()
        ->sort()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | AJAX RESPONSE
    |--------------------------------------------------------------------------
    */
    if ($request->ajax() || $request->has('ajax')) {
        return view(
            'central.processing.orders.partials.orders-content',
            compact(
                'orders',
                'counts',
                'amounts',
                'districtCounts',
                'districts',
                'talukas',
                'couriers',
                'states',
                'products'
            )
        );
    }

    return view(
        'central.processing.orders.index',
        compact(
            'orders',
            'counts',
            'amounts',
            'states',
            'districts',
            'talukas',
            'districtCounts',
            'couriers',
            'products'
        )
    );
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
    public function indexReturns(Request $request): View|JsonResponse
    {
        $query = OrderReturn::with(['order.customer', 'order.shipments', 'items.product'])
            ->whereIn('status', ['approved', 'received']);

        // Stats Calculation (Before filtering, but respecting Courier if selected)
        $statsQuery = OrderReturn::whereIn('status', ['approved', 'received']);
        if ($request->filled('courier') && $request->courier !== 'all') {
            $courier = $request->input('courier');
            $statsQuery->whereIn('order_id', function ($q) use ($courier) {
                $q->select('order_id')->from('shipments')->where('carrier', $courier);
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'received' => (clone $statsQuery)->where('status', 'received')->count(),
        ];

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('rma_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($o) use ($search) {
                        $o->where('order_number', 'like', "%{$search}%")
                          ->orWhereHas('shipments', function ($s) use ($search) {
                              $s->where('tracking_number', 'like', "%{$search}%");
                          });
                    });
            });
        }

        // Filter by Courier (Subquery for maximum reliability)
        if ($request->filled('courier') && $request->courier !== 'all') {
            $courier = $request->input('courier');
            $query->whereIn('order_id', function ($q) use ($courier) {
                $q->select('order_id')
                  ->from('shipments')
                  ->where('carrier', $courier);
            });
        }

        $perPage = $request->input('per_page', 10);
        $returns = $query->latest()->paginate((int)$perPage)->withQueryString();

        $couriers = \App\Models\Shipment::whereHas('order.returns', function($q) {
            $q->whereIn('status', ['approved', 'received']);
        })->distinct()->whereNotNull('carrier')->pluck('carrier')->sort()->values();

        if ($request->ajax() || $request->has('ajax')) {
            return response()->json([
                'html' => view('central.processing.returns.partials.returns-content', compact('returns'))->render(),
                'stats' => $stats
            ]);
        }

        return view('central.processing.returns.index', compact('returns', 'stats', 'couriers'));
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


// Bulk status update via CSV

public function downloadBulkActionTemplate()
{
    $filename = 'bulk_shipped_template.csv';

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate",
        "Expires" => "0"
    ];

    $columns = ['order_number', 'action', 'courier', 'tracking_number'];

    $callback = function () use ($columns) {
        $file = fopen('php://output', 'w');

        fputcsv($file, $columns);

        // Sample rows
        fputcsv($file, ['ORD001', 'shipped', 'Delhivery', 'DL123456789']);
        fputcsv($file, ['ORD002', 'deliver', '', '']);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}


public function bulkProcess(Request $request): JsonResponse
{
    $rows = $request->input('rows', []);

    DB::beginTransaction();

    try {

        $processedOrders = [];
        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($rows as $row) {

            if (($row['status'] ?? '') === 'error') {
                $failCount++;
                continue;
            }

            $orderNumber = trim($row['order_number'] ?? '');

            if (!$orderNumber || in_array($orderNumber, $processedOrders)) {
                continue;
            }

            $processedOrders[] = $orderNumber;

            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                $failCount++;
                $errors[] = "Order {$orderNumber} not found";
                continue;
            }

            $action = strtolower(trim($row['action'] ?? ''));

            try {

                /*
                |--------------------------------------------------------------------------
                | 🚫 STRICT FLOW CONTROL
                |--------------------------------------------------------------------------
                */
                $validTransitions = [
                    'processing'    => ['shipped'],
                    'ready_to_ship' => ['shipped'],
                    'shipped'       => ['deliver'],
                ];

                $currentStatus = $order->status;

                if (!isset($validTransitions[$currentStatus]) ||
                    !in_array($action, $validTransitions[$currentStatus])) {

                    throw new Exception("Invalid transition: {$currentStatus} → {$action}");
                }

                /*
                |--------------------------------------------------------------------------
                | SHIPPED
                |--------------------------------------------------------------------------
                */
                if ($action === 'shipped') {

                    $tracking = trim($row['tracking_number'] ?? '');
                    $courier  = trim($row['courier'] ?? '');

                    if (!$tracking || !$courier) {
                        throw new Exception("Tracking & courier required");
                    }

                    $existing = $order->shipments()->latest()->first();

                    if ($existing && $existing->tracking_number) {

                        if (!($row['confirm_overwrite'] ?? false)) {
                            throw new Exception("Tracking exists → confirm overwrite");
                        }

                        $existing->update([
                            'tracking_number' => $tracking,
                            'carrier' => $courier,
                        ]);
                    }

                    // auto move processing → ready_to_ship
                    if ($order->status === 'processing') {
                        $order->update(['status' => 'ready_to_ship']);
                    }

                    $this->orderService->shipOrder($order, $tracking, $courier);
                }

                /*
                |--------------------------------------------------------------------------
                | DELIVER
                |--------------------------------------------------------------------------
                */
                if ($action === 'deliver') {

                    $order->update([
                        'status' => 'delivered',
                        'updated_by' => auth()->id()
                    ]);
                }

                $successCount++;

            } catch (Exception $e) {
                $failCount++;
                $errors[] = "Order {$orderNumber}: " . $e->getMessage();
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Processed: {$successCount}, Failed: {$failCount}",
            'errors'  => array_slice($errors, 0, 5)
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}



public function bulkPreview(Request $request): JsonResponse
{
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt',
    ]);

    $rows = [];
    $handle = fopen($request->file('csv_file')->getPathname(), 'r');

    if (!$handle) {
        return response()->json(['rows' => [], 'error' => 'Unable to read file'], 422);
    }

    $header = fgetcsv($handle);

    if (!$header) {
        fclose($handle);
        return response()->json(['rows' => [], 'error' => 'CSV is empty'], 422);
    }

    $header = array_map(fn($h) => strtolower(trim($h)), $header);

    $required = ['order_number', 'action'];
    if ($missing = array_diff($required, $header)) {
        fclose($handle);
        return response()->json([
            'rows' => [],
            'error' => 'Missing columns: ' . implode(', ', $missing)
        ], 422);
    }

    $map = array_flip($header);

    while (($row = fgetcsv($handle)) !== false) {

        if (empty(array_filter($row))) continue;

        $orderNumber = trim($row[$map['order_number']] ?? '');
        $action      = strtolower(trim($row[$map['action']] ?? ''));
        $courier     = trim($row[$map['courier']] ?? '');
        $tracking    = trim($row[$map['tracking_number']] ?? '');

        $order = $orderNumber ? Order::where('order_number', $orderNumber)->first() : null;

        $status = 'valid';
        $message = 'Ready';
        $needsConfirmation = false;

        if (!$order) {
            $status = 'error';
            $message = 'Order not found';
        } else {

            /*
            |--------------------------------------------------------------------------
            | 🚫 STRICT FLOW CONTROL
            |--------------------------------------------------------------------------
            */
            $validTransitions = [
                'processing'    => ['shipped'],
                'ready_to_ship' => ['shipped'],
                'shipped'       => ['deliver'],
            ];

            $currentStatus = $order->status;

            if (!isset($validTransitions[$currentStatus]) ||
                !in_array($action, $validTransitions[$currentStatus])) {

                $status = 'error';
                $message = "Invalid transition: {$currentStatus} → {$action}";
            }

            /*
            |--------------------------------------------------------------------------
            | SHIPPED VALIDATION
            |--------------------------------------------------------------------------
            */
            if ($status !== 'error' && $action === 'shipped') {

                if (!$courier || !$tracking) {
                    $status = 'error';
                    $message = 'Tracking & courier required';
                }

                $existing = $order->shipments()->latest()->first();

                if ($existing && $existing->tracking_number) {
                    $status = 'warning';
                    $message = 'Tracking exists → confirm overwrite';
                    $needsConfirmation = true;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | DELIVER VALIDATION
            |--------------------------------------------------------------------------
            */
            if ($status !== 'error' && $action === 'deliver') {

                if ($order->status !== 'shipped') {
                    $status = 'error';
                    $message = 'Order must be SHIPPED';
                }
            }
        }

        $rows[] = [
            'order_number'       => $orderNumber,
            'action'             => $action,
            'courier'            => $courier,
            'tracking_number'    => $tracking,
            'current_status'     => $order->status ?? null,
            'status'             => $status,
            'message'            => $message,
            'needs_confirmation' => $needsConfirmation,
        ];
    }

    fclose($handle);

    return response()->json(['rows' => $rows]);
}




}
