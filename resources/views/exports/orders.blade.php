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
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 4px;
            vertical-align: top;
            white-space: nowrap;
        }

        th {
            background-color: #f9fafb;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 7px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Total</th>

                <!-- Customer Details -->
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Last Name</th>
                <th>Display Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Phone 2</th>
                <th>Company</th>
                <th>GST</th>
                <th>Type</th>
                <th>Category</th>
                <th>Source</th>
                <th>KYC Status</th>

                <!-- Products -->
                <th>Ordered Products</th>

                <!-- Billing Address -->
                <th>Billing Label</th>
                <th>Billing Name</th>
                <th>Billing Phone</th>
                <th>Billing Address 1</th>
                <th>Billing Address 2</th>
                <th>Billing Village</th>
                <th>Billing Taluka</th>
                <th>Billing District</th>
                <th>Billing Post Office</th>
                <th>Billing State</th>
                <th>Billing Country</th>
                <th>Billing Pincode</th>

                <!-- Shipping Address -->
                <th>Shipping Label</th>
                <th>Shipping Name</th>
                <th>Shipping Phone</th>
                <th>Shipping Address 1</th>
                <th>Shipping Address 2</th>
                <th>Shipping Village</th>
                <th>Shipping Taluka</th>
                <th>Shipping District</th>
                <th>Shipping Post Office</th>
                <th>Shipping State</th>
                <th>Shipping Country</th>
                <th>Shipping Pincode</th>

                <!-- Logistics -->
                <th>Courier Service</th>
                <th>Tracking Number</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                @php
                    // Shipments
                    $couriers = $order->shipments->pluck('carrier')->filter()->unique()->implode(', ');
                    $trackingNumbers = $order->shipments->pluck('tracking_number')->filter()->unique()->implode(', ');

                    // Products String
                    $orderedProducts = $order->items->map(function ($item) {
                        return $item->product_name . ' (' . floatval($item->quantity) . 'x)';
                    })->implode(', ');
                @endphp
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ ucfirst($order->payment_status) }}</td>
                    <td>{{ number_format($order->grand_total, 2) }}</td>

                    <!-- Customer -->
                    <td>{{ $order->customer?->first_name ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->middle_name ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->last_name ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->display_name ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->mobile ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->email ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->phone_number_2 ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->company_name ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->gst_number ?? 'N/A' }}</td>
                    <td>{{ ucfirst($order->customer?->type ?? 'N/A') }}</td>
                    <td>{{ ucfirst($order->customer?->category ?? 'N/A') }}</td>
                    <td>{{ $order->customer?->source ?? 'N/A' }}</td>
                    <td>{{ $order->customer?->kyc_completed ? 'Completed' : 'Pending' }}</td>

                    <!-- Products -->
                    <td>{{ $orderedProducts }}</td>

                    <!-- Billing -->
                    <td>{{ $order->billingAddress?->label ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->contact_name ?? $order->billingAddress?->name ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->contact_phone ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->address_line1 ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->address_line2 ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->village ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->taluka ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->district ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->post_office ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->state ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->country ?? 'N/A' }}</td>
                    <td>{{ $order->billingAddress?->pincode ?? 'N/A' }}</td>

                    <!-- Shipping -->
                    <td>{{ $order->shippingAddress?->label ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->contact_name ?? $order->shippingAddress?->name ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->contact_phone ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->address_line1 ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->address_line2 ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->village ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->taluka ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->district ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->post_office ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->state ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->country ?? 'N/A' }}</td>
                    <td>{{ $order->shippingAddress?->pincode ?? 'N/A' }}</td>

                    <!-- Logistics -->
                    <td>{{ $couriers ?: 'N/A' }}</td>
                    <td>{{ $trackingNumbers ?: 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>