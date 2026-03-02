<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Invoice::with(['order.customer', 'order.items', 'order.shipments'])->latest();

        // Apply Status Filter
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        // Apply Search Filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($c) use ($search) {
                                $c->search($search);
                            });
                    });
            });
        }

        // Apply specific IDs if selected
        if (!empty($this->filters['ids'])) {
            $ids = explode(',', $this->filters['ids']);
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Invoice #',
            'Order #',
            'Status',
            'Issue Date',
            'Due Date',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Total Amount',
            'Paid Amount',
            'Balance Due',
            'Payment Method',
            'Total Items',
            'Tracking Info',
        ];
    }

    public function map($invoice): array
    {
        $order = $invoice->order;
        $customer = $order ? $order->customer : null;

        $balanceDue = max(0, $invoice->total_amount - $invoice->paid_amount);

        // Fetch tracking info if available
        $trackingInfo = 'N/A';
        if ($order && $order->shipments && $order->shipments->isNotEmpty()) {
            $trackingInfo = $order->shipments->map(function ($shipment) {
                return ($shipment->carrier ?? 'Courier') . ': ' . $shipment->tracking_number;
            })->implode(' | ');
        }

        return [
            $invoice->invoice_number,
            $order ? $order->order_number : 'Manual',
            ucfirst($invoice->status),
            $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : '-',
            $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-',
            $customer ? $customer->name : 'N/A',
            $customer ? $customer->email : 'N/A',
            $customer ? $customer->mobile : 'N/A',
            number_format($invoice->total_amount, 2, '.', ''),
            number_format($invoice->paid_amount, 2, '.', ''),
            number_format($balanceDue, 2, '.', ''),
            $order ? ucfirst($order->payment_method ?? 'N/A') : 'N/A',
            $order && $order->items ? $order->items->count() : 0,
            $trackingInfo,
        ];
    }
}
