<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Models\Order;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderReturnController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the order returns.
     */
    public function index(Request $request): View
    {
        $this->authorize('returns view');

        // Stats for the tabs
        $stats = [
            'all' => OrderReturn::count(),
            'requested' => OrderReturn::where('status', 'requested')->count(),
            'approved' => OrderReturn::where('status', 'approved')->count(),
            'received' => OrderReturn::where('status', 'received')->count(),
            'refunded' => OrderReturn::where('status', 'refunded')->count(),
            'rejected' => OrderReturn::where('status', 'rejected')->count(),
        ];

        $query = OrderReturn::with(['order.customer', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rma_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $returns = $query->latest()->paginate(10);

        return view('tenant.returns.index', compact('returns', 'stats'));
    }

    /**
     * Show the form for creating a new return.
     */
    public function create(Request $request): View
    {
        $this->authorize('returns create');

        $preSelectedOrderId = $request->query('order_id');

        // Helper closure to processing order items
        $processOrderItems = function ($order) {
            $returnedQuantities = [];
            foreach ($order->returns as $rma) {
                if ($rma->status === 'rejected')
                    continue;
                foreach ($rma->items as $rmaItem) {
                    if (!isset($returnedQuantities[$rmaItem->product_id])) {
                        $returnedQuantities[$rmaItem->product_id] = 0;
                    }
                    $returnedQuantities[$rmaItem->product_id] += $rmaItem->quantity;
                }
            }

            $order->items->transform(function ($item) use ($returnedQuantities) {
                $qtyReturned = $returnedQuantities[$item->product_id] ?? 0;
                $item->available_quantity = max(0, $item->quantity - $qtyReturned);
                return $item;
            });
            return $order;
        };

        $orders = Order::with(['items.product', 'returns.items'])
            ->whereIn('status', ['shipped', 'delivered'])
            ->latest()
            ->limit(50)
            ->get()
            ->map($processOrderItems);

        $preSelectedOrder = null;
        if ($preSelectedOrderId) {
            $preSelectedOrder = $orders->firstWhere('id', $preSelectedOrderId);

            if (!$preSelectedOrder) {
                // Fetch if not in the recent 50 list
                $preSelectedOrder = Order::with(['items.product', 'returns.items'])->find($preSelectedOrderId);
                if ($preSelectedOrder) {
                    $preSelectedOrder = $processOrderItems($preSelectedOrder);
                }
            }

            if ($preSelectedOrder && !in_array($preSelectedOrder->status, ['shipped', 'delivered'])) {
                $status = $preSelectedOrder->status;
                $preSelectedOrder = null;
                session()->flash('error', 'Selected order is not eligible for return (Status: ' . ucfirst($status ?? 'Unknown') . ')');
            }
        }

        return view('tenant.returns.create', compact('orders', 'preSelectedOrderId', 'preSelectedOrder'));
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
                    throw new \Exception("Returns are only allowed for Shipped or Delivered orders. Current status: " . ucfirst($order->status));
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
                    $orderItems = $order->items->where('product_id', $requestedItem['product_id']);

                    if ($orderItems->isEmpty()) {
                        throw new \Exception("Product ID {$requestedItem['product_id']} does not belong to this order.");
                    }

                    $totalPurchasedQty = (float) $orderItems->sum('quantity');
                    $productName = $orderItems->first()->product->name ?? 'Unknown Product'; // Safe because we checked isEmpty

                    $previouslyReturned = (float) ($returnedQuantities[$requestedItem['product_id']] ?? 0);

                    // Collect RMA numbers for this product
                    $blockingRmas = $existingReturns->filter(function ($rma) use ($requestedItem) {
                        // Check if this RMA contains the current product
                        $hasProduct = false;
                        foreach ($rma->items as $item) {
                            if ($item->product_id == $requestedItem['product_id']) {
                                $hasProduct = true;
                                break;
                            }
                        }
                        return $hasProduct;
                    })->pluck('rma_number')->implode(', ');

                    // Rounding to avoid floating point issues
                    $availableQty = round($totalPurchasedQty - $previouslyReturned, 3);
                    $requestedQty = (float) $requestedItem['quantity'];

                    if ($requestedQty > $availableQty) {
                        $errorMsg = "Cannot return " . (int) $requestedQty . " of {$productName}. Only " . (int) $availableQty . " available. (Purchased: " . (int) $totalPurchasedQty . ", Previously Returned/Requested: " . (int) $previouslyReturned . ")";
                        if (!empty($blockingRmas)) {
                            $errorMsg .= " [Blocking RMAs: {$blockingRmas}]";
                        }
                        throw new \Exception($errorMsg);
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
            });

            return redirect()->route('tenant.returns.index')->with('success', 'RMA Requested.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to request RMA: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified return.
     */
    public function show(OrderReturn $orderReturn): View
    {
        $this->authorize('returns view');

        $orderReturn->load(['items.product', 'order.customer']);
        return view('tenant.returns.show', compact('orderReturn'));
    }

    /**
     * Update the status of the return and handle restock logic.
     */
    public function updateStatus(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        $this->authorize('returns manage');

        $request->validate(['status' => 'required|in:approved,received,refunded,rejected']);

        try {
            DB::transaction(function () use ($request, $orderReturn) {
                $orderReturn->update(['status' => $request->status]);

                if ($request->status === 'received') {
                    foreach ($orderReturn->items as $item) {
                        if ($item->condition === 'sellable') {
                            $warehouseId = $orderReturn->order->warehouse_id;
                            if ($warehouseId) {
                                $stock = InventoryStock::firstOrCreate(
                                    ['warehouse_id' => $warehouseId, 'product_id' => $item->product_id],
                                    ['quantity' => 0]
                                );
                                $stock->increment('quantity', $item->quantity);

                                // Refresh Product Denormalized Stock
                                $item->product->refreshStockOnHand();

                                InventoryMovement::create([
                                    'stock_id' => $stock->id,
                                    'type' => 'return',
                                    'quantity' => $item->quantity,
                                    'reference_id' => $orderReturn->id,
                                    'reason' => 'RMA Received: ' . $orderReturn->rma_number,
                                    'user_id' => auth()->id(),
                                ]);
                            }
                        }
                    }
                }
            });

            return back()->with('success', 'RMA Status Updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status.');
        }
    }

    /**
     * Preview bulk returns from CSV.
     */
    public function bulkPreview(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('returns create');
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:4096']);

        try {
            $file = $request->file('csv_file');
            $content = file_get_contents($file->getRealPath());
            
            // Handle different line endings
            $lines = preg_split('/\r\n|\r|\n/', trim($content));
            if (empty($lines)) {
                throw new \Exception("Empty file");
            }

            $data = array_map('str_getcsv', $lines);
            $header = array_shift($data);
            
            // Normalize header (lowercase, remove spaces)
            $header = array_map(function($h) {
                return strtolower(trim($h));
            }, $header);

            $rows = [];
            
            foreach ($data as $index => $row) {
                if (count($header) !== count($row)) continue;
                
                $mappedRow = array_combine($header, $row);
                $status = 'valid';
                $message = 'Ready to process';
                
                // Find order by ID or Number
                $orderQuery = Order::with(['items.product', 'returns.items']);
                $orderIdentifier = $mappedRow['order_id'] ?? ($mappedRow['order_number'] ?? null);
                
                if (!empty($orderIdentifier)) {
                    $order = $orderQuery->where(function($q) use ($orderIdentifier) {
                        if (is_numeric($orderIdentifier)) {
                            $q->where('id', $orderIdentifier)->orWhere('order_number', $orderIdentifier);
                        } else {
                            $q->where('order_number', $orderIdentifier);
                        }
                    })->first();
                } else {
                    $status = 'error';
                    $message = "Order ID or Number missing";
                    $order = null;
                }

                if ($order && $status === 'valid') {
                    if (!in_array($order->status, ['shipped', 'delivered'])) {
                        $rows[] = array_merge($mappedRow, [
                            'preview_status' => 'error',
                            'preview_message' => "Ineligible status: " . ucfirst($order->status),
                            'order_id' => $order->id,
                        ]);
                    } else {
                        // Support full order fetch if no specific SKU was requested
                        $sku = $mappedRow['sku'] ?? null;
                        $itemsToProcess = current(array_filter([$sku])) ? $order->items->where('sku', $sku) : $order->items;

                        if ($itemsToProcess->isEmpty()) {
                            $rows[] = array_merge($mappedRow, [
                                'preview_status' => 'error',
                                'preview_message' => current(array_filter([$sku])) ? "SKU not in order" : "No items found in order",
                                'order_id' => $order->id,
                            ]);
                        } else {
                            $addedAny = false;
                            foreach ($itemsToProcess as $orderItem) {
                                // Calculate available qty
                                $returnedQty = 0;
                                foreach ($order->returns as $rma) {
                                    if ($rma->status === 'rejected') continue;
                                    $returnedQty += $rma->items->where('product_id', $orderItem->product_id)->sum('quantity');
                                }

                                $availableQty = max(0, $orderItem->quantity - $returnedQty);
                                
                                // Auto-fetch logic: Skip already returned items unless a specific SKU & Qty was requested explicitly
                                if (!current(array_filter([$sku])) && $availableQty <= 0) {
                                    continue;
                                }

                                $requestedQty = (isset($mappedRow['quantity']) && trim($mappedRow['quantity']) !== '') 
                                    ? (float)$mappedRow['quantity'] 
                                    : $availableQty;

                                $itemStatus = 'valid';
                                $itemMessage = 'Ready to process';

                                if ($requestedQty <= 0) {
                                    $itemStatus = 'error';
                                    $itemMessage = "Invalid quantity";
                                } elseif ($requestedQty > $availableQty) {
                                    $itemStatus = 'error';
                                    $itemMessage = "Only $availableQty available (Ordered: {$orderItem->quantity}, Returned: $returnedQty)";
                                }

                                $rows[] = array_merge($mappedRow, [
                                    'preview_status' => $itemStatus,
                                    'preview_message' => $itemMessage,
                                    'order_id' => $order->id,
                                    'order_number' => $order->order_number,
                                    'product_id' => $orderItem->product_id,
                                    'product_name' => $orderItem->product->name ?? $orderItem->sku,
                                    'sku' => $orderItem->sku,
                                    'quantity' => $requestedQty,
                                    'reason' => !empty($mappedRow['reason']) ? $mappedRow['reason'] : 'Bulk Auto Fetch',
                                    'condition' => !empty($mappedRow['condition']) ? $mappedRow['condition'] : 'sellable',
                                ]);
                                $addedAny = true;
                            }
                            
                            // If auto-fetch returned nothing (all items returned)
                            if (!$addedAny) {
                                $rows[] = array_merge($mappedRow, [
                                    'preview_status' => 'error',
                                    'preview_message' => "All items in this order have already been returned",
                                    'order_id' => $order->id,
                                    'order_number' => $order->order_number,
                                ]);
                            }
                        }
                    }
                } elseif (!$order && $status === 'valid') {
                    $rows[] = array_merge($mappedRow, [
                        'preview_status' => 'error',
                        'preview_message' => "Order not found",
                        'order_id' => null,
                        'order_number' => $mappedRow['order_id'] ?? ($mappedRow['order_number'] ?? null),
                        'product_id' => null,
                        'product_name' => null,
                    ]);
                } else {
                    $rows[] = array_merge($mappedRow, [
                        'preview_status' => $status,
                        'preview_message' => $message,
                        'order_id' => null,
                        'order_number' => null,
                        'product_id' => null,
                        'product_name' => null,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'rows' => $rows
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Process confirmed bulk returns.
     */
    public function bulkUpload(Request $request): RedirectResponse
    {
        $this->authorize('returns create');
        
        $validated = $request->validate([
            'rows' => 'required|array|min:1',
            'rows.*.order_id' => 'required|exists:orders,id',
            'rows.*.product_id' => 'required|exists:products,id',
            'rows.*.quantity' => 'required|numeric|min:0.001',
            'rows.*.condition' => 'nullable|string',
            'rows.*.reason' => 'nullable|string',
            'rows.*.preview_status' => 'required|in:valid',
        ]);

        try {
            $errors = [];
            $createdCount = 0;

            $createdRMAs = [];

            DB::transaction(function () use ($validated, &$errors, &$createdCount, &$createdRMAs) {
                foreach ($validated['rows'] as $row) {
                    $order = Order::with(['items', 'returns.items'])->findOrFail($row['order_id']);
                    $productId = $row['product_id'];

                    // Final validation check
                    $orderItem = $order->items->where('product_id', $productId)->first();
                    if (!$orderItem) {
                        $errors[] = "Order #{$order->order_number}: Product not found in order.";
                        continue;
                    }

                    $returnedQty = 0;
                    foreach ($order->returns as $rma) {
                        if ($rma->status === 'rejected') continue;
                        $returnedQty += $rma->items->where('product_id', $productId)->sum('quantity');
                    }

                    $availableQty = max(0, $orderItem->quantity - $returnedQty);
                    if ($row['quantity'] > $availableQty) {
                        $errors[] = "Order #{$order->order_number}: Insufficient quantity available.";
                        continue;
                    }

                    if (!isset($createdRMAs[$order->id])) {
                        $rma = OrderReturn::create([
                            'rma_number' => 'RMA-' . strtoupper(Str::random(8)),
                            'order_id' => $order->id,
                            'customer_id' => $order->customer_id,
                            'status' => 'requested',
                            'reason' => $row['reason'] ?? 'Bulk CSV Upload',
                            'refund_method' => 'credit',
                        ]);
                        $createdRMAs[$order->id] = $rma;
                        $createdCount++;
                    } else {
                        $rma = $createdRMAs[$order->id];
                    }

                    $rma->items()->create([
                        'product_id' => $productId,
                        'quantity' => $row['quantity'],
                        'condition' => strtolower($row['condition'] ?? 'sellable'),
                    ]);
                }
            });

            if (count($errors) > 0) {
                return redirect()->route('tenant.returns.index')->with('warning', "Created $createdCount RMAs. Some errors occurred: " . implode(', ', $errors));
            }

            return redirect()->route('tenant.returns.index')->with('success', "Successfully created $createdCount bulk RMA requests.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to store bulk returns: ' . $e->getMessage());
        }
    }
}
