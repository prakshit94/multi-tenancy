<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Exports\InvoicesExport;
use App\Imports\InvoicesPaymentImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the invoices.
     */
    public function index(Request $request): View
    {
        $this->authorize('orders view');

        $query = Invoice::with(['order.customer']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas(
                        'order',
                        fn($oq) =>
                        $oq->where('order_number', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', (string) $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 5);
        $perPage = ($perPage > 0 && $perPage <= 100) ? $perPage : 5;

        $invoices = $query->latest()->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('tenant.invoices.partials.invoices-list', compact('invoices'));
        }

        return view('tenant.invoices.index', compact('invoices'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $order = Order::findOrFail($request->order_id);

        try {
            $invoice = DB::transaction(function () use ($order, $request) {
                return Invoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                    'issue_date' => now(),
                    'due_date' => $request->due_date ?? now()->addDays(30),
                    'total_amount' => $order->total_amount,
                    'paid_amount' => 0,
                    'status' => 'sent',
                ]);
            });

            return redirect()->route('tenant.invoices.show', $invoice)->with('success', 'Invoice generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate invoice.');
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('orders view');

        $invoice->load(['order.items', 'payments', 'order.customer']);
        return view('tenant.invoices.show', compact('invoice'));
    }

    /**
     * Add a payment to the invoice.
     */
    public function addPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $invoice) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'order_id' => $invoice->order_id,
                    'amount' => $request->amount,
                    'method' => $request->method,
                    'transaction_id' => $request->transaction_id ?? null,
                    'notes' => $request->notes ?? null,
                    'paid_at' => now(),
                ]);

                $invoice->increment('paid_amount', (float) $request->amount);
                $invoice->refresh();

                // Update Status
                if (round((float) $invoice->paid_amount, 2) >= round((float) $invoice->total_amount, 2)) {
                    $invoice->update(['status' => 'paid']);
                    $order = $invoice->order;
                    $order->update(['payment_status' => 'paid']);
                } else {
                    $invoice->update(['status' => 'partial']);
                    $order = $invoice->order;
                    $order->update(['payment_status' => 'partial']);
                }
            });

            return back()->with('success', 'Payment recorded.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to record payment.');
        }
    }    /**
         * Record a bulk payment against multiple invoices.
         */
    public function bulkPayment(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Fetch invoices in order of ascending age (oldest first)
                $invoices = Invoice::whereIn('id', $request->invoice_ids)
                    ->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->lockForUpdate()
                    ->orderBy('issue_date', 'asc')
                    ->get();

                $remainingBulkAmount = (float) $request->amount;

                /** @var \App\Models\Invoice $invoice */
                foreach ($invoices as $invoice) {
                    if ($remainingBulkAmount <= 0)
                        break;

                    $invoiceRemaining = round($invoice->total_amount - $invoice->paid_amount, 2);
                    if ($invoiceRemaining <= 0)
                        continue;

                    $amountToApply = min($remainingBulkAmount, $invoiceRemaining);

                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'order_id' => $invoice->order_id,
                        'amount' => $amountToApply,
                        'method' => $request->method,
                        'transaction_id' => $request->transaction_id ?? null,
                        'notes' => $request->notes ? "Bulk Payment - " . $request->notes : "Bulk Payment",
                        'paid_at' => now(),
                    ]);

                    $invoice->increment('paid_amount', $amountToApply);
                    $invoice->refresh();

                    // Update Status
                    if (round((float) $invoice->paid_amount, 2) >= round((float) $invoice->total_amount, 2)) {
                        $invoice->update(['status' => 'paid']);
                        $order = $invoice->order;
                        $order->update(['payment_status' => 'paid']);
                    } else {
                        $invoice->update(['status' => 'partial']);
                        $order = $invoice->order;
                        $order->update(['payment_status' => 'partial']);
                    }

                    $remainingBulkAmount -= $amountToApply;
                }
            });

            return back()->with('success', 'Bulk payment processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process bulk payment.');
        }
    }

    /**
     * Export invoices as Excel/CSV
     */
    public function export(Request $request)
    {
        $this->authorize('orders view');
        $format = $request->query('format', 'xlsx');

        if (!in_array($format, ['xlsx', 'csv'])) {
            $format = 'xlsx';
        }

        $filename = 'invoices_' . now()->format('Ymd_His') . '.' . $format;
        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new InvoicesExport($request->all()), $filename, $writerType);
    }

    /**
     * Download the CSV/Excel template for bulk upload
     */
    public function downloadTemplate(Request $request)
    {
        $this->authorize('orders view');
        $format = $request->query('format', 'xlsx');

        if (!in_array($format, ['xlsx', 'csv'])) {
            $format = 'xlsx';
        }

        $filename = 'invoice_payment_template.' . $format;
        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new \App\Exports\InvoiceTemplateExport(), $filename, $writerType);
    }

    /**
     * Upload an Excel/CSV file for bulk invoice payments
     */
    /**
     * Upload an Excel/CSV file for bulk invoice payments (Preview Stage)
     */
    public function bulkUpload(Request $request)
    {
        $this->authorize('orders manage');

        $request->validate([
            'payment_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        try {
            $file = $request->file('payment_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/temp'), $filename);

            $path = 'temp/' . $filename;
            $fullPath = storage_path('app/temp/' . $filename);

            $preview = new \App\Imports\InvoicesPaymentPreview();
            \Maatwebsite\Excel\Facades\Excel::import($preview, $fullPath);

            return view('tenant.invoices.preview', [
                'validRows' => $preview->validRows,
                'invalidRows' => $preview->invalidRows,
                'tempFile' => $path,
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMsg = 'Validation Error: ';
            foreach ($failures as $failure) {
                $errorMsg .= "Row {$failure->row()}: {$failure->errors()[0]}. ";
            }
            return back()->with('error', $errorMsg);
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating preview: ' . $e->getMessage());
        }
    }

    /**
     * Process the confirmed bulk upload file.
     */
    public function bulkUploadProcess(Request $request): RedirectResponse
    {
        $this->authorize('orders manage');

        $request->validate([
            'temp_file' => 'required|string',
        ]);

        $path = $request->input('temp_file');
        // $path is now "temp/filename.xlsx"
        $fullPath = storage_path('app/' . $path);

        if (!file_exists($fullPath)) {
            return redirect()->route('tenant.invoices.index')->with('error', 'Temporary file expired or not found. Please upload again.');
        }

        try {
            $import = new InvoicesPaymentImport();
            Excel::import($import, $fullPath);

            // Cleanup temp file
            unlink($fullPath);

            $message = "File processed: {$import->successCount} successful rows, {$import->failCount} failed rows.";

            if ($import->failCount > 0) {
                $errorMsg = implode('<br>', array_slice($import->errors, 0, 3));
                if (count($import->errors) > 3) {
                    $errorMsg .= '<br>... and ' . (count($import->errors) - 3) . ' more errors.';
                }
                return redirect()->route('tenant.invoices.index')->with('warning', $message . '<br><br><b>Errors:</b><br>' . $errorMsg);
            }

            return redirect()->route('tenant.invoices.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('tenant.invoices.index')->with('error', 'Error importing confirmed file: ' . $e->getMessage());
        }
    }
}
