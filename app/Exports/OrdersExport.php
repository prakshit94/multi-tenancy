<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        $query = Order::with(['customer', 'items', 'billingAddress', 'shippingAddress', 'creator', 'updater', 'shipments'])->latest();

        // Apply Status Filter
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }
        // Default Logic for Tenant/Central Index if status is missing/null (Active Orders)
        // However, export might be expected to export ALL if nothing is selected? 
        // The user request says "insted of only active tabs order details fix this issue" -> "while export from this index it will export all... instead of only active... fix this"
        // So user WANTS it to filter.
        // If status is not set, the Index controller defaults to ['confirmed', 'processing', 'ready_to_ship'].
        // We should replicate that default if no status is passed, OR rely on the controller to pass 'all' or specific statuses.
        // Let's assume the controller will pass the exact filter state. 
        elseif (!isset($this->filters['status'])) {
            // Replicating OrderProcessingController default behavior
            $query->whereIn('status', ['confirmed', 'processing', 'ready_to_ship']);
        }


        // Apply Search Filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply Date Filter
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        // Apply IDs Filter (Selected Records)
        if (!empty($this->filters['ids']) && is_array($this->filters['ids'])) {
            $query->whereIn('id', $this->filters['ids']);
        }

        return view('exports.orders', [
            'orders' => $query->get()
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Bold headings
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Formatting applied explicitly for PDF via PhpSpreadsheet
                $sheet = $event->sheet->getDelegate();

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setRight(0.2);
                $sheet->getPageMargins()->setLeft(0.2);
                $sheet->getPageMargins()->setBottom(0.5);
            },
        ];
    }
}
