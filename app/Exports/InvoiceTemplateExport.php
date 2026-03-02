<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoiceTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Invoice #',
            'Amount',
            'Method',
            'Transaction ID / Reference',
            'Notes',
        ];
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return [
            ['INV-XXXXXXXX', '100.00', 'bank_transfer', 'TXN123456789', 'Bulk upload payment'],
        ];
    }
}
