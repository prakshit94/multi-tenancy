<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>COD Receipt - {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 10px;
            size: a5;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.3;
        }

        .label-container {
            border: 2px solid #166534;
            border-radius: 6px;
            padding: 8px;
            max-width: 500px;
            /* Optional: adjust based on need */
            margin: 0 auto;
            background-color: #f0fdf4;
        }

        .row {
            display: table;
            width: 100%;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .box {
            border: 1px solid #86efac;
            background-color: #ffffff;
            border-radius: 4px;
            padding: 6px;
            margin-top: 6px;
        }

        .title {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #166534;
        }

        .big {
            font-size: 13px;
            font-weight: bold;
            color: #15803d;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .muted {
            font-size: 10px;
            color: #333;
        }

        .divider {
            border-top: 1px dashed #166534;
            margin: 8px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #86efac;
            padding: 4px;
            text-align: left;
        }

        .items-table th {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>

<body>

    <div class="label-container">

        <div class="box">
            <div class="row">
                <div class="col big">
                    Pincode: {{ optional($order->shippingAddress)->pincode ?? '-' }}
                </div>
                <div class="col right big">
                    COD Amount: Rs. {{ number_format($order->grand_total, 0) }}
                </div>
            </div>
        </div>

        <div class="center title" style="margin-top:8px;">
            BUSINESS PARCEL<br>
            CASH ON DELIVERY (COD)
        </div>

        <div class="center muted" style="margin-top:4px;">
            <strong>Order No: {{ $order->order_number }}</strong><br>
            Payment Office : Rajkot H.O. <br>
            Register No / E-Biller ID : 1211658094<br>
            Order Date: {{ $order->created_at->format('d-m-Y H:i') }}
        </div>

        <div class="divider"></div>

        <div class="box">
            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">To,</div>
            <strong>Name:</strong> {{ $order->customer->name }}<br>
            <strong>Address:</strong> {{ $order->shippingAddress->address_line1 }}<br>
            @if($order->shippingAddress->village) <strong>Village:</strong> {{ $order->shippingAddress->village }},
            @endif
            @if($order->shippingAddress->taluka) <strong>Taluka:</strong> {{ $order->shippingAddress->taluka }}, @endif
            <strong>District:</strong> {{ $order->shippingAddress->district }}<br>
            <strong>Post Office:</strong> {{ $order->shippingAddress->post_office }}<br>
            <strong>State:</strong> {{ $order->shippingAddress->state }} - {{ $order->shippingAddress->pincode }}<br>
            <strong>Contact:</strong> {{ $order->customer->mobile }} @if($order->customer->phone_number_2) / {{ $order->customer->phone_number_2 }} @endif
        </div>

        <div class="box">
            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">From (Sender),</div>
            <strong>Krushify Agro Pvt. Ltd.</strong><br>
            Srp Camp, New 150ft Ring Road, Ghanteshwar,<br>
            Bapa Sitaram Chowk, Vardhman Sheri Block No: 22<br>
            360006 Rajkot, Gujarat. | <strong>Mobile:</strong> 9199125925<br>
            <strong>GST:</strong> 24AAMCK0386L1Z6
        </div>



        <div class="divider"></div>

        <div class="muted center">
            If article undelivered, please arrange return to <strong>Rajkot H.O.</strong><br>
            <em>“I hereby certify that this article does not contain any dangerous or prohibited goods according to
                Indian Post rules.”</em>
        </div>



    </div>

</body>

</html>