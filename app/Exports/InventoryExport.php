<?php

namespace App\Exports;

use App\Models\InventoryStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        return InventoryStock::with(['product', 'warehouse'])->get();
    }

    public function headings(): array
    {
        return [
            // Identification
            'Product',
            'SKU',
            'Warehouse',
            
            // Physical Stock Status
            'Physical Stock in Warehouse',
            'Reserved for Customer Orders',
            'Available to Sell Now',
            'Stock Shortage (Need to Buy)',
            'Inventory Status',
            
            // Order Activity (Based on Date Filter)
            'Number of Orders Placed',
            'Total Items Ordered',
            'Items Shipped Out',
            'Items Waiting to Ship',
            
            // Financials
            'Cost Per Item',
            'Selling Price Per Item',
            'Total Value of Physical Stock',
            'Potential Revenue of Available Stock'
        ];
    }

    public function map($stock): array
    {
        $startDate = !empty($this->filters['start_date']) ? $this->filters['start_date'] : null;
        $endDate = !empty($this->filters['end_date']) ? $this->filters['end_date'] : null;

        $baseOrderQuery = \App\Models\OrderItem::where('product_id', $stock->product_id)
            ->whereHas('order', function ($q) use ($stock) {
                $q->whereNotIn('status', ['cancelled', 'returned'])
                  ->where('warehouse_id', $stock->warehouse_id);
            });

        if ($startDate) {
            $baseOrderQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $baseOrderQuery->whereDate('created_at', '<=', $endDate);
        }

        // Enterprise Granular Breakdown
        $orderCount = (clone $baseOrderQuery)->distinct('order_id')->count('order_id');
        $qtyOrdered = (float) (clone $baseOrderQuery)->sum('quantity');
        
        $qtyDispatched = (float) (clone $baseOrderQuery)
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['shipped', 'delivered', 'completed', 'in_transit']);
            })->sum('quantity');

        $qtyPending = (float) (clone $baseOrderQuery)
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_to_ship', 'scheduled']);
            })->sum('quantity');


        $allTimePendingQty = (float) \App\Models\OrderItem::where('product_id', $stock->product_id)
            ->whereHas('order', function ($q) use ($stock) {
                // Matched EXACTLY with InventoryController logic
                $q->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_to_ship'])
                  ->where('warehouse_id', $stock->warehouse_id);
            })->sum('quantity');

        $physicalQty = (float) $stock->quantity;
        
        // Dynamically calculate actual reserved stock based on all live unfulfilled orders,
        // ignoring date filters so the true available stock is perfectly accurate.
        $reservedQty = $allTimePendingQty; 
        
        $availableToSell = max(0, $physicalQty - $reservedQty);
        $shortage = max(0, $reservedQty - $physicalQty);
        
        $costPrice = (float) ($stock->product?->cost_price ?? 0);
        $sellingPrice = (float) ($stock->product?->price ?? 0);
        
        $totalPhysicalValue = $physicalQty * $costPrice;
        $totalExpectedRevenue = $availableToSell * $sellingPrice;

        // Status calculation aligned EXACTLY with InventoryController special filters
        $status = 'In Stock';
        if ($physicalQty <= 0) {
            $status = 'Out of Stock';
        } elseif ($physicalQty <= ($stock->product?->reorder_level ?? 5)) {
            $status = 'Low Stock';
        }

        return [
            // Identification
            $stock->product?->name ?? 'N/A',
            $stock->product?->sku ?? 'N/A',
            $stock->warehouse?->name ?? 'N/A',
            
            // Physical Stock Status
            $physicalQty,
            $reservedQty,
            $availableToSell,
            $shortage,
            $status,
            
            // Order Activity
            $orderCount,
            $qtyOrdered,
            $qtyDispatched,
            $qtyPending,
            
            // Financials
            $costPrice,
            $sellingPrice,
            $totalPhysicalValue,
            $totalExpectedRevenue
        ];
    }
}
