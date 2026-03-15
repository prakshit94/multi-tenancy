<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderService
{
    /**
     * Confirm order and reserve stock.
     * pending | draft | scheduled → confirmed
     */
    public function confirmOrder(Order $order): Order
    {
        if (!in_array($order->status, ['pending', 'draft', 'scheduled'], true)) {
            throw new Exception("Order cannot be confirmed from status: {$order->status}");
        }

        return DB::transaction(function () use ($order) {

            foreach ($order->items as $item) {

                $stock = InventoryStock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $order->warehouse_id,
                    ],
                    [
                        'quantity' => 0,
                        'reserve_quantity' => 0,
                    ]
                );

                $available = $stock->quantity - $stock->reserve_quantity;

                if ($available < $item->quantity && !$item->product->manage_stock) {
                    if (!$item->product->allow_oversell) {
                        throw new Exception(
                            "Insufficient stock for Product ID {$item->product_id}. 
                            Required: {$item->quantity}, Available: {$available}"
                        );
                    }
                    
                    $oversold_amount = abs($available - $item->quantity);
                    if ($item->product->oversell_limit !== null && $oversold_amount > $item->product->oversell_limit) {
                        throw new Exception(
                            "Oversell limit exceeded for Product ID {$item->product_id}. 
                            Allowed oversell: {$item->product->oversell_limit}, Requested oversell: {$oversold_amount}"
                        );
                    }
                }

                $stock->increment('reserve_quantity', $item->quantity);

                $item->product->refreshStockOnHand();
            }

            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'updated_by' => Auth::id(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Ship order and deduct physical stock.
     * processing | ready_to_ship → shipped
     */
    public function shipOrder(
        Order $order,
        ?string $trackingNumber = null,
        ?string $carrier = null
    ): Order {

        if (!in_array($order->status, ['processing', 'ready_to_ship'], true)) {
            throw new Exception("Order cannot be shipped from status: {$order->status}");
        }

        return DB::transaction(function () use ($order, $trackingNumber, $carrier) {

            foreach ($order->items as $item) {

                $stock = InventoryStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($stock->reserve_quantity < $item->quantity) {
                    throw new Exception(
                        "Reserved stock mismatch for Product ID {$item->product_id}. 
                        Reserved: {$stock->reserve_quantity}, Required: {$item->quantity}"
                    );
                }

                if ($stock->quantity < $item->quantity) {
                    throw new Exception(
                        "Stock mismatch for Product ID {$item->product_id}. 
                        Available: {$stock->quantity}, Required: {$item->quantity}"
                    );
                }

                $stock->decrement('quantity', $item->quantity);
                $stock->decrement('reserve_quantity', $item->quantity);

                InventoryMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'sale',
                    'quantity' => -$item->quantity,
                    'reference_id' => $order->id,
                    'reason' => "Order #{$order->order_number} shipped",
                    'user_id' => Auth::id(),
                ]);

                $item->product->refreshStockOnHand();
            }

            $order->update([
                'status' => 'shipped',
                'shipping_status' => 'shipped',
                'updated_by' => Auth::id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Shipment handling
            |--------------------------------------------------------------------------
            | Update existing shipment or create if missing
            */

            $order->shipments()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'warehouse_id' => $order->warehouse_id,
                    'tracking_number' => $trackingNumber,
                    'carrier' => $carrier,
                    'status' => 'shipped',
                    'shipped_at' => now(),
                ]
            );

            return $order->fresh();
        });
    }

    /**
     * Validate stock availability for processing an order.
     * Throws an exception if stock is insufficient.
     */
    public function validateStockForProcessing(Order $order): void
    {
        foreach ($order->items as $item) {
            $stock = InventoryStock::where('product_id', $item->product_id)
                ->where('warehouse_id', $order->warehouse_id)
                ->lockForUpdate()
                ->first();

            $quantity = $stock ? (float)$stock->quantity : 0.0;
            
            // Committed stock = Sum of quantities in orders already in 'processing' or 'ready_to_ship'
            $committed = \App\Models\OrderItem::where('product_id', $item->product_id)
                ->whereHas('order', function($q) use ($order) {
                    $q->where('warehouse_id', $order->warehouse_id)
                      ->whereIn('status', ['processing', 'ready_to_ship']);
                })->sum('quantity');

            $available = $quantity - (float)$committed;
            $required = (float)$item->quantity;

            if ($available < $required) {
                throw new Exception(
                    "Insufficient stock for Product ID {$item->product_id} after accounting for other orders already in progress. Physical Stock: " . number_format($quantity, 3) . ", Already Committed: " . number_format((float)$committed, 3) . ", Available for you: " . number_format($available, 3) . ", Required: " . number_format($required, 3)
                );
            }
        }
    }

    /**
     * Deliver order.
     * shipped → delivered
     */
    public function deliverOrder(Order $order): Order
    {
        if ($order->status !== 'shipped') {
            throw new Exception("Order must be shipped before delivery.");
        }

        return DB::transaction(function () use ($order) {

            $order->update([
                'status' => 'delivered',
                'shipping_status' => 'delivered',
                'completed_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $order->shipments()
                ->where('status', 'shipped')
                ->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);

            return $order->fresh();
        });
    }

    /**
     * Release reserved stock without changing status.
     */
    public function releaseReserves(Order $order): void
    {
        foreach ($order->items as $item) {

            $stock = InventoryStock::where('product_id', $item->product_id)
                ->where('warehouse_id', $order->warehouse_id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                continue;
            }

            $toRelease = min($item->quantity, $stock->reserve_quantity);

            if ($toRelease > 0) {
                $stock->decrement('reserve_quantity', $toRelease);
                $item->product->refreshStockOnHand();
            }
        }
    }

    /**
     * Apply reserved stock without changing status.
     */
    public function applyReserves(Order $order): void
    {
        foreach ($order->items as $item) {

            $stock = InventoryStock::lockForUpdate()->firstOrCreate(
                [
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->warehouse_id,
                ],
                [
                    'quantity' => 0,
                    'reserve_quantity' => 0,
                ]
            );

            $available = $stock->quantity - $stock->reserve_quantity;

            if ($available < $item->quantity && !$item->product->manage_stock) {
                 if (!$item->product->allow_oversell) {
                    throw new Exception(
                        "Insufficient stock for Product ID {$item->product_id}. 
                        Required: {$item->quantity}, Available: {$available}"
                    );
                }
                
                $oversold_amount = abs($available - $item->quantity);
                if ($item->product->oversell_limit !== null && $oversold_amount > $item->product->oversell_limit) {
                    throw new Exception(
                        "Oversell limit exceeded for Product ID {$item->product_id}. 
                        Allowed oversell: {$item->product->oversell_limit}, Requested oversell: {$oversold_amount}"
                    );
                }
            }

            $stock->increment('reserve_quantity', $item->quantity);

            $item->product->refreshStockOnHand();
        }
    }

    /**
     * Cancel order and release reserved stock.
     */
    public function cancelOrder(Order $order): Order
    {
        if (in_array($order->status, ['shipped', 'delivered', 'cancelled'], true)) {
            throw new Exception("Order cannot be cancelled from status: {$order->status}");
        }

        return DB::transaction(function () use ($order) {

            if (in_array($order->status, ['confirmed', 'processing', 'ready_to_ship'], true)) {
                $this->releaseReserves($order);
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            return $order->fresh();
        });
    }
}