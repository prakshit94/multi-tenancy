<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>COD Label - {{ $order->order_number ?? '' }}</title>
    <style>
        @page { 
            size: 100mm 150mm; 
            margin: 0; 
        }
        
        * { 
            box-sizing: border-box; 
        }

        html, body { 
            width: 100mm;
            height: 150mm;
            margin: 0 !important; 
            padding: 4mm 0 0 0 !important; 
            font-family: Arial, sans-serif; 
            color: #000; 
            background: #fff;
            overflow: hidden;
        }

        .wrapper {
            border: 2px solid #000;
            padding: 4pt;
            width: 90mm;
            margin: 0 auto;
        }

        table.full-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed;
        }

        /* Brand Banner Styling */
        .brand-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 3pt;
            margin-bottom: 4pt;
        }

        .brand-name {
            font-size: 14pt;
            font-weight: 900;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 7.5pt;
            font-weight: bold;
            color: #333;
        }

        .pincode-badge { 
            font-size: 11pt; 
            font-weight: bold; 
            border: 2px solid #000; 
            padding: 3pt 1pt; 
            text-align: center;
            line-height: 1.1;
        }

        .cod-banner { 
            background-color: #000; 
            color: #fff; 
            padding: 3pt 1pt; 
            text-align: center; 
            font-weight: bold; 
            font-size: 10.5pt; 
            border: 2px solid #000;
            line-height: 1.1;
        }

        .meta-table td { 
            font-size: 7.5pt; 
            padding: 1pt 0; 
            vertical-align: top; 
        }

        .section-box { 
            border: 1px solid #000; 
            padding: 4pt; 
            margin-bottom: 4pt; 
            font-size: 8pt; 
            line-height: 1.25; 
        }

        .customer-box {
            border: 2px solid #000;
            padding: 4pt;
            margin-bottom: 4pt;
        }

        .section-title { 
            font-weight: bold; 
            text-transform: uppercase; 
            border-bottom: 1px solid #000; 
            margin-bottom: 3pt; 
            padding-bottom: 1pt; 
            font-size: 8pt; 
        }

        .footer { 
            text-align: center; 
            font-size: 6.5pt; 
            border-top: 1px dashed #000; 
            padding-top: 2pt; 
            margin-top: 4pt;
            line-height: 1.1;
        }
    </style>
</head>
<body>
    @php
        $addressObj = $order->shippingAddress ?? $order->shipping_address ?? null;
        $customerObj = $order->customer ?? null;
        $pincode = is_object($addressObj) ? ($addressObj->pincode ?? $order->pincode ?? '') : ($order->pincode ?? '');
        $customerName = is_object($customerObj) ? ($customerObj->name ?? $order->customer_name ?? $order->name ?? '') : ($order->customer_name ?? $order->name ?? '');
        $mobile1 = is_object($customerObj) ? ($customerObj->mobile ?? $order->contact_number ?? '') : ($order->contact_number ?? '');
        $mobile2 = is_object($customerObj) ? ($customerObj->phone_number_2 ?? null) : null;

        $addressLine1 = '';
        if (is_object($addressObj)) {
            $rawLine = $addressObj->address_line1 ?? $addressObj->address ?? '';
            if (!empty($addressObj->village) && preg_match('/^village\s*:-?/i', trim($rawLine))) {
                $addressLine1 = '';
            } else {
                $addressLine1 = $rawLine;
            }
        }
    @endphp

    <div class="wrapper">
        <!-- Prominent Brand Header -->
        <div class="brand-header">
            <div class="brand-name">KRUSHIFY AGRO</div>
            <div class="brand-subtitle">Krushify Agro Pvt. Ltd.</div>
        </div>

        <!-- COD & Pincode Header -->
        <table class="full-table" style="margin-bottom: 4pt;">
            <tr>
                <td style="width: 48%; padding-right: 2pt;">
                    <div class="pincode-badge">PIN: {{ $pincode }}</div>
                </td>
                <td style="width: 52%; padding-left: 2pt;">
                    <div class="cod-banner">
                        COD: Rs. {{ number_format($order->cod_amount ?? $order->grand_total ?? $order->total_amount ?? 0, 0) }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Order Meta Header -->
        <div class="section-box" style="text-align: center; background-color: #f9f9f9;">
            <div style="font-weight: bold; font-size: 8pt;">BUSINESS PARCEL (COD)</div>
            <table class="full-table meta-table" style="margin-top: 2pt;">
                <tr>
                    <td style="text-align: left; width: 50%;"><strong>Order:</strong> {{ $order->order_number ?? '' }}</td>
                    <td style="text-align: right; width: 50%;"><strong>Date:</strong> {{ isset($order->created_at) ? \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') : '' }}</td>
                </tr>
                <tr>
                    <td style="text-align: left; width: 50%;"><strong>Office:</strong> Rajkot H.O.</td>
                    <td style="text-align: right; width: 50%;"><strong>E-Biller:</strong> 1211658094</td>
                </tr>
            </table>
        </div>

        <!-- Deliver To Box (Customer) -->
        <div class="section-box customer-box">
            <div class="section-title">DELIVER TO (CUSTOMER)</div>
            <div style="font-size: 9.5pt; font-weight: bold; margin-bottom: 2pt;">{{ $customerName }}</div>
            @if(is_object($addressObj))
                @if(!empty($addressLine1))
                    <div>{{ $addressLine1 }}</div>
                @endif
                <div>
                    @if(!empty($addressObj->village)) <strong>Village:</strong> {{ $addressObj->village }} @endif
                    @if(!empty($addressObj->taluka)) | <strong>Taluka:</strong> {{ $addressObj->taluka }} @endif
                </div>
                <div><strong>Dist:</strong> {{ $addressObj->district ?? '' }} | <strong>PO:</strong> {{ $addressObj->post_office ?? '' }}</div>
                <div><strong>State:</strong> {{ $addressObj->state ?? 'Gujarat' }} - <strong>{{ $pincode }}</strong></div>
            @endif
            <div style="margin-top: 4pt; font-weight: bold; font-size: 8pt; background: #f0f0f0; padding: 2pt 4pt; display: inline-block; border: 1px solid #000;">
                Mobile: {{ $mobile1 }} @if($mobile2) / {{ $mobile2 }} @endif
            </div>
        </div>

        <!-- Sender Box -->
        <div class="section-box" style="margin-bottom: 0;">
            <div class="section-title">RETURN ADDRESS (SENDER)</div>
            <div style="font-weight: bold; font-size: 8.5pt;">Krushify Agro Pvt. Ltd.</div>
            <div>Plot No 19, Raj Ind Amul Cross Road, Ruda Transport Nagar</div>
            <div>360003 Rajkot, Gujarat. | <strong>Ph:</strong> 9199125925</div>
            <div><strong>GSTIN:</strong> 24AAMCK0386L1Z6</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>If undelivered, please return to <strong>Rajkot H.O.</strong></div>
            <div><i>Does not contain dangerous or prohibited goods per Indian Post rules.</i></div>
        </div>
    </div>
</body>
</html>
