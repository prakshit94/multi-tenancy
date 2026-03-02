<?php

namespace App\Imports;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InvoicesPaymentPreview implements ToCollection, WithHeadingRow
{
    public $validRows = [];
    public $invalidRows = [];

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            $invoiceNumber = $row['invoice'] ?? $row['invoice_number'] ?? $row['invoice_no'] ?? null;
            $amountRaw = $row['payment_amount'] ?? $row['amount'] ?? null;
            $method = $row['payment_method'] ?? $row['method'] ?? 'bank_transfer';
            $transactionId = $row['transaction_id_reference'] ?? $row['transaction_id___reference'] ?? $row['transaction_id'] ?? $row['reference'] ?? $row['ref'] ?? null;
            $notes = $row['notes'] ?? $row['remarks'] ?? 'Bulk CSV Upload';

            $amount = str_replace(',', '', (string) $amountRaw);

            $method = strtolower(trim((string) $method));
            if (!in_array($method, ['cash', 'bank_transfer', 'online', 'cheque'])) {
                $method = 'bank_transfer';
            }

            // Validation Failures
            if (empty($invoiceNumber) || !is_numeric($amount) || $amount <= 0) {
                $this->invalidRows[] = [
                    'row' => $rowNum,
                    'invoice_number' => $invoiceNumber ?? 'N/A',
                    'amount' => $amountRaw ?? 'N/A',
                    'error' => "Missing or invalid Invoice Number / Amount.",
                ];
                continue;
            }

            $invoice = Invoice::where(function ($q) use ($invoiceNumber) {
                $q->where('invoice_number', trim((string) $invoiceNumber))
                    ->orWhere('id', trim((string) $invoiceNumber));
            })->first();

            if (!$invoice) {
                $this->invalidRows[] = [
                    'row' => $rowNum,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $amount,
                    'error' => "Invoice not found in the database.",
                ];
                continue;
            }

            $remainingAmount = round($invoice->total_amount - $invoice->paid_amount, 2);

            if ($remainingAmount <= 0) {
                $this->invalidRows[] = [
                    'row' => $rowNum,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $amount,
                    'error' => "Invoice is already fully paid.",
                ];
                continue;
            }

            $amountToApply = min((float) $amount, $remainingAmount);
            $newStatus = round((float) ($invoice->paid_amount + $amountToApply), 2) >= round((float) $invoice->total_amount, 2)
                ? 'Paid'
                : 'Partial';

            $this->validRows[] = [
                'row' => $rowNum,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'current_balance' => $remainingAmount,
                'amount_to_apply' => $amountToApply,
                'method' => ucfirst(str_replace('_', ' ', $method)),
                'transaction_id' => $transactionId ?? '-',
                'notes' => $notes ?? '-',
                'new_status' => $newStatus,
            ];
        }
    }
}
