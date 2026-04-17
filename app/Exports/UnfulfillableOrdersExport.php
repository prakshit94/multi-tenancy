<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UnfulfillableOrdersExport implements FromView, ShouldAutoSize, WithEvents
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
        $query = Order::with(['customer', 'items', 'billingAddress', 'shippingAddress', 'creator', 'updater', 'shipments'])
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'scheduled'])
            ->whereHas('items', function ($q) {
                $q->join('products', 'order_items.product_id', '=', 'products.id')
                  ->whereColumn('products.stock_on_hand', '<', 'order_items.quantity');
            })
            ->latest();

        // Apply Date Filter
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('orders.created_at', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('orders.created_at', '<=', $this->filters['end_date']);
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
