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

        // 1. Priority: If explicit IDs are provided, ignore other filters
        if (!empty($this->filters['ids']) && is_array($this->filters['ids'])) {
            $query->whereIn('id', $this->filters['ids']);
        } else {
            // 2. Apply Status Filter
            if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
                $query->where('status', $this->filters['status']);
            }
            // Default Logic for Order Processing Index if status is missing/null (Active Orders)
            elseif (!isset($this->filters['status'])) {
                $query->whereIn('status', ['confirmed', 'processing', 'ready_to_ship']);
            }

            // 3. Apply Search Filter
            if (!empty($this->filters['search'])) {
                $search = $this->filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->search($search); // Use standard scope for consistency
                        });
                });
            }

            // 4. Apply Date Filter
            if (!empty($this->filters['start_date'])) {
                $query->whereDate('created_at', '>=', $this->filters['start_date']);
            }
            if (!empty($this->filters['end_date'])) {
                $query->whereDate('created_at', '<=', $this->filters['end_date']);
            }

            // 5. Apply Regional Filters (Matching OrderProcessingController)
            if (!empty($this->filters['district'])) {
                $district = trim($this->filters['district']);
                $query->where(function ($q) use ($district) {
                    $q->whereHas('shippingAddress', fn($sub) => $sub->where('district', 'like', "%{$district}%"))
                      ->orWhereHas('billingAddress', fn($sub) => $sub->where('district', 'like', "%{$district}%"));
                });
            }

            if (!empty($this->filters['taluka'])) {
                $taluka = trim($this->filters['taluka']);
                $query->where(function ($q) use ($taluka) {
                    $q->whereHas('shippingAddress', fn($sub) => $sub->where('taluka', 'like', "%{$taluka}%"))
                      ->orWhereHas('billingAddress', fn($sub) => $sub->where('taluka', 'like', "%{$taluka}%"));
                });
            }

            if (!empty($this->filters['village'])) {
                $village = trim($this->filters['village']);
                $query->where(function ($q) use ($village) {
                    $q->whereHas('shippingAddress', fn($sub) => $sub->where('village', 'like', "%{$village}%"))
                      ->orWhereHas('billingAddress', fn($sub) => $sub->where('village', 'like', "%{$village}%"));
                });
            }
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
