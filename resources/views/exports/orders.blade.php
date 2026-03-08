<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page {
            size: landscape;
            margin: 10px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Crucial for forcing columns to fit */
            word-wrap: break-word;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 4px;
            vertical-align: top;
            overflow-wrap: break-word;
            word-break: break-all;
        }

        th {
            background-color: #f9fafb;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 7px;
            color: #6b7280;
        }

        /* Strict Width Distributions */
        .w-sm {
            width: 5%;
        }

        .w-md {
            width: 8%;
        }

        .w-lg {
            width: 12%;
        }

        .w-xl {
            width: 22%;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="w-sm">Order #</th>
                <th class="w-sm">Date</th>
                <th class="w-sm">Status</th>
                <th class="w-sm">Payment</th>
                <th class="w-sm">Total</th>

                <!-- Customer Details Stacked -->
                <th class="w-lg">Customer Info</th>
                <th class="w-md">Contact</th>

                <!-- Products -->
                <th class="w-xl">Ordered Products</th>

                <!-- Addresses Stacked -->
                <th class="w-lg">Billing Address</th>
                <th class="w-lg">Shipping Address</th>

                <!-- Logistics -->
                <th class="w-md">Logistics</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                @php
                    // Shipments
                    $couriers = $order->shipments->pluck('carrier')->filter()->unique()->implode(', ');
                    $trackingNumbers = $order->shipments->pluck('tracking_number')->filter()->unique()->implode(', ');

                    // Customer Strings
                    $customerName = $order->customer?->name ?? ($order->customer?->first_name . ' ' . $order->customer?->last_name) ?? 'N/A';
                    $customerType = $order->customer ? ucfirst($order->customer->type) : 'N/A';
                    $customerCategory = $order->customer ? ucfirst($order->customer->category) : 'N/A';
                    $companyDetails = $order->customer?->company_name ?? 'N/A';
                    if ($order->customer && $order->customer->gst_number) {
                        $companyDetails .= ' (GST: ' . $order->customer->gst_number . ')';
                    }

                    // Products String
                    $orderedProducts = $order->items->map(function ($item) {
                        return $item->product_name . ' (' . floatval($item->quantity) . 'x)';
                    })->implode('<br>');

                    // Billing Address
                    $billLines = array_filter([$order->billingAddress?->address_line1, $order->billingAddress?->address_line2]);
                    $billCityState = array_filter([$order->billingAddress?->city, $order->billingAddress?->state]);

                    // Shipping Address
                    $shipLines = array_filter([$order->shippingAddress?->address_line1, $order->shippingAddress?->address_line2]);
                    $shipCityState = array_filter([$order->shippingAddress?->city, $order->shippingAddress?->state]);
                @endphp
                <tr>
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ ucfirst($order->payment_status) }}</td>
                    <td>{{ number_format($order->grand_total, 2) }}</td>

                    <!-- Customer Stacked -->
                    <td>
                        <strong>{{ $customerName }}</strong><br>
                        Company: {{ $companyDetails }}<br>
                        Type: {{ $customerType }} ({{ $customerCategory }})
                    </td>
                    <td>
                        M: {{ $order->customer?->mobile ?? 'N/A' }}<br>
                        E: {{ $order->customer?->email ?? 'N/A' }}
                    </td>

                    <!-- Products -->
                    <td>{!! $orderedProducts !!}</td>

                    <!-- Billing -->
                    <td>
                        <strong>{{ $order->billingAddress?->name ?? 'N/A' }}</strong><br>
                        {{ implode(', ', $billLines) }}<br>
                        {{ implode(', ', $billCityState) }} - {{ $order->billingAddress?->postal_code ?? '' }}
                    </td>

                    <!-- Shipping -->
                    <td>
                        <strong>{{ $order->shippingAddress?->name ?? 'N/A' }}</strong><br>
                        {{ implode(', ', $shipLines) }}<br>
                        {{ implode(', ', $shipCityState) }} - {{ $order->shippingAddress?->postal_code ?? '' }}
                    </td>

                    <!-- Logistics -->
                    <td>
                        Courier: {{ $couriers ?: 'N/A' }}<br>
                        Tracking: {{ $trackingNumbers ?: 'N/A' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>