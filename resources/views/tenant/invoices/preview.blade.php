@extends('tenant.layouts.app')
@section('title', 'Preview Bulk Upload')

@section('content')
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex flex-col gap-8">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">Review Payment Upload</h1>
                    <p class="text-sm text-gray-500 mt-1">Please review the detected rows before committing changes to the
                        database.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('tenant.invoices.index') }}"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white hover:bg-gray-50 transition-colors text-gray-700">
                        Cancel Upload
                    </a>

                    @if(count($validRows) > 0)
                        <form action="{{ route('tenant.invoices.bulk-upload.process') }}" method="POST">
                            @csrf
                            <input type="hidden" name="temp_file" value="{{ $tempFile }}">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-indigo-600 hover:bg-indigo-700">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Confirm & Process
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex items-start gap-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ count($validRows) }} Valid Rows</h3>
                        <p class="text-sm text-gray-500 mt-1">Ready to be processed and applied to balances.</p>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex items-start gap-4">
                    <div class="p-3 bg-red-50 text-red-600 rounded-lg">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ count($invalidRows) }} Invalid Rows</h3>
                        <p class="text-sm text-gray-500 mt-1">These rows contain errors and will be skipped.</p>
                    </div>
                </div>
            </div>

            @if(count($invalidRows) > 0)
                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                    <div class="bg-red-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-red-800 flex items-center gap-2">
                            Errors Detected (Will be skipped)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3">Row</th>
                                    <th class="px-6 py-3">Invoice #</th>
                                    <th class="px-6 py-3">Amount</th>
                                    <th class="px-6 py-3">Error Reason</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($invalidRows as $row)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3 font-mono text-gray-500">{{ $row['row'] }}</td>
                                        <td class="px-6 py-3 font-medium text-gray-900">{{ $row['invoice_number'] }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ $row['amount'] }}</td>
                                        <td class="px-6 py-3 text-red-600 font-medium">{{ $row['error'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if(count($validRows) > 0)
                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            Valid Payments Overview
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-white text-gray-500 font-medium border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3">Row</th>
                                    <th class="px-6 py-3">Invoice #</th>
                                    <th class="px-6 py-3">Method</th>
                                    <th class="px-6 py-3">Txn Ref</th>
                                    <th class="px-6 py-3 text-right">To Apply</th>
                                    <th class="px-6 py-3 text-center">New Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($validRows as $row)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3 font-mono text-gray-500">{{ $row['row'] }}</td>
                                        <td class="px-6 py-3 font-semibold text-gray-900">{{ $row['invoice_number'] }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ $row['method'] }}</td>
                                        <td class="px-6 py-3 font-mono text-xs">{{ $row['transaction_id'] }}</td>
                                        <td class="px-6 py-3 text-right font-medium">₹
                                            {{ number_format($row['amount_to_apply'], 2) }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ $row['new_status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
                    <svg class="size-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">No valid rows found</h3>
                    <p class="text-sm text-gray-500 mt-1 mb-6">We couldn't detect any valid payment data in your spreadsheet.
                        Please fix the errors listed above and re-upload.</p>
                    <a href="{{ route('tenant.invoices.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-indigo-600 hover:bg-indigo-700">
                        Go Back
                    </a>
                </div>
            @endif

        </div>
    </div>
@endsection