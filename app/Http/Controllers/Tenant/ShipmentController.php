<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the shipments.
     */
    public function index(): View
    {
        $this->authorize('orders view');
        
        $shipments = Shipment::with(['order.customer', 'warehouse'])->latest()->paginate(10);
        return view('tenant.shipments.index', compact('shipments'));
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
        
        return view('tenant.shipments.create', compact('orders', 'warehouses'));
    }

    /**
     * Store a newly created shipment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'carrier' => 'required|string',
            'tracking_number' => 'nullable|string',
            'weight' => 'nullable|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $order = Order::findOrFail($request->order_id);

                if ($order->warehouse_id && (int) $order->warehouse_id !== (int) $request->warehouse_id) {
                    throw new \Exception('Shipment warehouse must match the order warehouse.');
                }

                if ($order->status === 'processing') {
                    $order->update([
                        'status' => 'ready_to_ship',
                        'shipping_status' => 'pending',
                        'updated_by' => auth()->id(),
                    ]);
                }

                if ($order->status !== 'ready_to_ship') {
                    throw new \Exception('Only ready to ship orders can be dispatched.');
                }

                app(\App\Services\OrderService::class)->shipOrder(
                    $order,
                    $request->tracking_number,
                    $request->carrier
                );
            });

            return redirect()->route('tenant.shipments.index')->with('success', 'Shipment created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create shipment.');
        }
    }

    /**
     * Display the specified shipment.
     */
    public function show(Shipment $shipment): View
    {
        $this->authorize('orders view');
        
        $shipment->load(['order.items', 'order.customer', 'warehouse']);
        return view('tenant.shipments.show', compact('shipment'));
    }

    /**
     * Update the status of the shipment.
     */
    public function updateStatus(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate(['status' => 'required|string']);
        
        try {
            DB::transaction(function () use ($request, $shipment) {
                $shipment->update(['status' => $request->status]);
                
                if ($request->status === 'delivered') {
                    $shipment->update(['delivered_at' => now()]);
                    $shipment->order->update([
                        'shipping_status' => 'delivered',
                        'status' => 'delivered'
                    ]);
                }
            });

            return back()->with('success', 'Shipment status updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update shipment status.');
        }
    }
}
