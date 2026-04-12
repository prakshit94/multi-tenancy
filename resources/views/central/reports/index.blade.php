@extends('layouts.app')

@section('title', 'System Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6">
        
        <h1 class="text-2xl font-bold mb-6 text-gray-800">System Reports</h1>

        {{-- Error Message --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('central.reports.export') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Report Type -->
            <div>
                <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Report Type
                </label>

                <select name="report_type" id="report_type" required
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">

                    <option value="" disabled {{ old('report_type') ? '' : 'selected' }}>
                        -- Choose a Report --
                    </option>

                    <option value="orders" {{ old('report_type') == 'orders' ? 'selected' : '' }}>
                        Orders Report
                    </option>

                    <option value="inventory" {{ old('report_type') == 'inventory' ? 'selected' : '' }}>
                        Inventory / Stock Report
                    </option>

                    <option value="customers" {{ old('report_type') == 'customers' ? 'selected' : '' }}>
                        Customers Report
                    </option>

                    <option value="interactions" {{ old('report_type') == 'interactions' ? 'selected' : '' }}>
                        Customer Interactions Report
                    </option>
                </select>

                <p class="mt-1 text-sm text-gray-500">
                    Choose the type of data you wish to export.
                </p>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Start Date (Optional)
                    </label>

                    <input type="date" name="start_date" id="start_date"
                        value="{{ old('start_date') }}"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        End Date (Optional)
                    </label>

                    <input type="date" name="end_date" id="end_date"
                        value="{{ old('end_date') }}"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                </div>

            </div>

            <!-- Format -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Export Format
                </label>

                <div class="flex items-center space-x-4">
                    
                    <label class="inline-flex items-center">
                        <input type="radio" name="format" value="csv"
                            class="form-radio text-indigo-600"
                            {{ old('format', 'csv') == 'csv' ? 'checked' : '' }}>
                        <span class="ml-2">CSV</span>
                    </label>

                    <label class="inline-flex items-center">
                        <input type="radio" name="format" value="xlsx"
                            class="form-radio text-indigo-600"
                            {{ old('format') == 'xlsx' ? 'checked' : '' }}>
                        <span class="ml-2">Excel (.xlsx)</span>
                    </label>

                    <label class="inline-flex items-center">
                        <input type="radio" name="format" value="pdf"
                            class="form-radio text-indigo-600"
                            {{ old('format') == 'pdf' ? 'checked' : '' }}>
                        <span class="ml-2">PDF</span>
                    </label>

                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-gray-200">
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Generate & Download Report
                </button>
            </div>

        </form>
    </div>
</div>
@endsection