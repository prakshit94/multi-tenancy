<?php

namespace App\Imports;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class InvoicesPaymentImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $failCount = 0;
    public $errors = [];

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Row number for reporting (accounting for header)
            $rowNum = $index + 2;

            try {
                // Ensure required columns exist (case-insensitive due to WithHeadingRow which slugifies them)
                // Maatwebsite\Excel by default converts "Invoice #" to "invoice" or "invoice_number" depending on config.
                // We'll check common variations just in case.
                $invoiceNumber = $row['invoice'] ?? $row['invoice_number'] ?? $row['invoice_no'] ?? null;
                $amount = $row['payment_amount'] ?? $row['amount'] ?? null;
                $method = $row['payment_method'] ?? $row['method'] ?? 'bank_transfer';
                $transactionId = $row['transaction_id_reference'] ?? $row['transaction_id___reference'] ?? $row['transaction_id'] ?? $row['reference'] ?? $row['ref'] ?? null;
                $notes = $row['notes'] ?? $row['remarks'] ?? 'Bulk CSV Upload';

                $amount = str_replace(',', '', (string) $amount);

                if (empty($invoiceNumber) || !is_numeric($amount) || $amount <= 0) {
                    $this->failCount++;
                    $this->errors[] = "Row {$rowNum}: Missing or invalid Invoice Number / Amount.";
                    continue;
                }

                $method = strtolower(trim($method));
                // Validate method enum loosely
                if (!in_array($method, ['cash', 'bank_transfer', 'online', 'cheque'])) {
                    $method = 'bank_transfer'; // Default fallback
                }

                DB::transaction(function () use ($invoiceNumber, $amount, $method, $transactionId, $notes, $rowNum) {
                    $invoice = Invoice::where(function ($q) use ($invoiceNumber) {
                        $q->where('invoice_number', trim($invoiceNumber))
                            ->orWhere('id', trim($invoiceNumber));
                    })
                        ->lockForUpdate()
                        ->first();

                    if (!$invoice) {
                        throw new Exception("Row {$rowNum}: Invoice '{$invoiceNumber}' not found.");
                    }

                    $remainingAmount = round($invoice->total_amount - $invoice->paid_amount, 2);

                    if ($remainingAmount <= 0) {
                        throw new Exception("Row {$rowNum}: Invoice '{$invoiceNumber}' is already fully paid.");
                    }

                    // Only take what we can actually apply without overpaying
                    $amountToApply = min((float) $amount, $remainingAmount);

                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'order_id' => $invoice->order_id,
                        'amount' => $amountToApply,
                        'method' => $method,
                        'transaction_id' => $transactionId,
                        'notes' => $notes,
                        'paid_at' => now(),
                    ]);

                    $invoice->increment('paid_amount', $amountToApply);

                    // Reload to get fresh DB value
                    $invoice->refresh();

                    $status = round((float) $invoice->paid_amount, 2) >= round((float) $invoice->total_amount, 2)
                        ? 'paid'
                        : 'partial';

                    $invoice->update(['status' => $status]);

                    if ($invoice->order) {
                        $invoice->order->update(['payment_status' => $status]);
                    }

                    $this->successCount++;
                });

            } catch (Exception $e) {
                $this->failCount++;
                $this->errors[] = $e->getMessage();
            }
        }
    }
}
