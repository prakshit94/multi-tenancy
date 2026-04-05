<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of central shipments.
     */
    public function index(Request $request): View
    {
        $this->authorize('orders view');

        $query = Shipment::with(['order.customer', 'warehouse']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function($q) use ($search) {
                      $q->where('order_number', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $shipments = $query->latest()->paginate($perPage)->withQueryString();

        return view('central.shipments.index', compact('shipments'));
    }

    /**
     * Show the form for creating a new shipment.
     */
    public function create(): View
    {
        $this->authorize('orders manage');

        $orders = Order::whereIn('status', ['processing', 'ready_to_ship'])
            ->where('shipping_status', '!=', 'shipped')
            ->latest()
            ->get();
            
        $warehouses = Warehouse::where('is_active', true)->get();
        
        return view('central.shipments.create', compact('orders', 'warehouses'));
    }

    /**
     * Store a newly created shipment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'carrier' => 'required|string',
            'tracking_number' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                /** @var Order $order */
                $order = Order::findOrFail($validated['order_id']);

                if ($order->warehouse_id && (int) $order->warehouse_id !== (int) $validated['warehouse_id']) {
                    throw new Exception('Shipment warehouse must match the order warehouse.');
                }

                if ($order->status === 'processing') {
                    $order->update([
                        'status' => 'ready_to_ship',
                        'shipping_status' => 'pending',
                        'updated_by' => auth()->id(),
                    ]);
                }

                if ($order->status !== 'ready_to_ship') {
                    throw new Exception('Only ready to ship orders can be dispatched.');
                }

                $this->orderService->shipOrder(
                    $order,
                    $validated['tracking_number'] ?? null,
                    $validated['carrier']
                );
            });

            return redirect()->route('central.shipments.index')->with('success', 'Shipment created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create shipment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified shipment.
     */
    public function show(Shipment $shipment): View
    {
        $this->authorize('orders view');
        $shipment->load(['order.items', 'order.customer', 'warehouse']);
        return view('central.shipments.show', compact('shipment'));
    }

    /**
     * Update the status of the shipment.
     */
    public function updateStatus(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('orders manage');
        $validated = $request->validate(['status' => 'required|string']);
        
        try {
            DB::transaction(function () use ($validated, $shipment) {
                $shipment->update(['status' => $validated['status']]);
                
                if ($validated['status'] === 'delivered') {
                    $shipment->update(['delivered_at' => now()]);
                    $this->orderService->deliverOrder($shipment->order);
                }
            });

            return back()->with('success', 'Shipment status updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to update shipment status: ' . $e->getMessage());
        }
    }

    /**
     * Update the carrier and tracking number of the shipment.
     */
    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('orders manage');
        
        $validated = $request->validate([
            'carrier' => 'required|string',
            'tracking_number' => 'nullable|string',
        ]);
        
        try {
            $shipment->update([
                'carrier' => $validated['carrier'],
                'tracking_number' => $validated['tracking_number'],
            ]);

            return back()->with('success', 'Shipment tracking details updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to update shipment tracking details: ' . $e->getMessage());
        }
    }
}
