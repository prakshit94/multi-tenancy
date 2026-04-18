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
            'Product Name',
            'SKU',
            'Warehouse Location',
            
            // Physical Stock Status
            'Total Physical Stock',
            'Stock Reserved For Orders',
            'Stock Available To Sell',
            'Shortage (Need to buy)',
            'Stock Health Status',
            
            // Order Activity (Based on Date Filter)
            'Orders Placed (Selected Date)',
            'Orders Dispatched (Selected Date)',
            'Orders Pending (Selected Date)',
            
            // Financials
            'Unit Cost Price',
            'Unit Selling Price',
            'Total Physical Stock Value',
            'Expected Revenue (Available Stock)'
        ];
    }

    public function map($stock): array
    {
        $startDate = !empty($this->filters['start_date']) ? $this->filters['start_date'] : now()->startOfDay()->toDateString();
        $endDate = !empty($this->filters['end_date']) ? $this->filters['end_date'] : now()->endOfDay()->toDateString();

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
        $totalOrdered = (float) (clone $baseOrderQuery)->sum('quantity');
        
        $qtyDispatched = (float) (clone $baseOrderQuery)
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['shipped', 'delivered', 'completed', 'in_transit']);
            })->sum('quantity');

        $qtyPending = (float) (clone $baseOrderQuery)
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_to_ship', 'scheduled']);
            })->sum('quantity');


        $physicalQty = (float) $stock->quantity;
        $reservedQty = (float) $stock->reserve_quantity;
        
        $availableToSell = max(0, $physicalQty - $reservedQty);
        $shortage = max(0, $reservedQty - $physicalQty);
        
        $costPrice = (float) ($stock->product?->cost_price ?? 0);
        $sellingPrice = (float) ($stock->product?->price ?? 0);
        
        $totalPhysicalValue = $physicalQty * $costPrice;
        $totalExpectedRevenue = $availableToSell * $sellingPrice;

        $status = 'In Stock';
        if ($shortage > 0) {
            $status = 'Deficit / Unfulfillable';
        } elseif ($availableToSell <= 0) {
            $status = 'Out of Stock';
        } elseif ($availableToSell <= ($stock->product?->reorder_level ?? 5)) {
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
            $totalOrdered,
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
