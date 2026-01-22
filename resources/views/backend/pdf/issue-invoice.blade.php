<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Issue Invoice</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Web View Styling - Looks like paper */
        @media screen {
            body {
                background-color: #f0f0f0;
                padding: 40px 0;
            }
            .container {
                max-width: 800px;
                background: #fff;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
                border-radius: 4px;
                padding: 40px;
            }
        }
        @media print {
            .no-print { display: none; }
            .container { box-shadow: none; border: none; padding: 0; }
        }
        .action-bar {
            text-align: right;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .btn {
            text-decoration: none;
            display: inline-block;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 5px;
        }
        .btn-secondary { background: #6c757d; color: #fff; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-primary { background: #007bff; color: #fff; }
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .company-info {
            text-align: right;
            float: right;
            width: 60%;
        }
        .invoice-title {
            float: left;
            width: 40%;
        }
        .invoice-title h1 {
            margin: 0;
            color: #333;
            font-size: 28px;
            text-transform: uppercase;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        .details-box {
            margin-bottom: 30px;
        }
        .box-left {
            float: left;
            width: 48%;
        }
        .box-right {
            float: right;
            width: 48%;
            text-align: right;
        }
        .table-responsive {
            width: 100%;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
            border-top: 2px solid #ddd;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .signature-section {
            margin-top: 60px;
        }
        .signature-box {
            float: left;
            width: 30%;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 10px;
        }
        .signature-box.right {
            float: right;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #fff;
            background-color: #6c757d;
            border-radius: 4px;
        }
        .badge-success { background-color: #28a745; }
        .badge-info { background-color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        @if(!($is_pdf ?? false))
        <div class="action-bar no-print">
            <a href="{{ route('admin.issues.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="#" onclick="window.print(); return false;" class="btn btn-warning">Print</a>
            <a href="{{ route('admin.issues.download-invoice', $issue->id) }}" class="btn btn-primary">Download PDF</a>
        </div>
        @endif
        <div class="header clearfix">
            <div class="invoice-title">
                <h1>Issue Invoice</h1>
                <p><strong>Ref:</strong> {{ $issue->issue_no }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge badge-success">Completed</span>
                </div>
            </div>
            <div class="company-info">
                <h3>{{ $settings->site_name ?? config('app.name') }}</h3>
                <p>
                    {{ $settings->contact_email ?? '' }}<br>
                    {!! nl2br(e($settings->address ?? '')) !!}
                </p>
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="box-left">
                <h4>Issued To:</h4>
                <p>
                    <strong>{{ $issue->outlet->outlet_name ?? $issue->outlet->name ?? 'N/A' }}</strong><br>
                    User: {{ $issue->outlet->name ?? '' }}<br>
                    Phone: {{ $issue->outlet->phone ?? 'N/A' }}<br>
                    Email: {{ $issue->outlet->email ?? 'N/A' }}<br>
                    Address: {{ $issue->outlet->address ?? 'N/A' }}
                </p>
            </div>
            <div class="box-right">
                <h4>Issue Details:</h4>
                <p>
                    <strong>Date:</strong> {{ $issue->created_at->format('d M, Y h:i A') }}<br>
                    <strong>Request Ref:</strong> {{ $issue->productRequest->request_no ?? 'N/A' }}<br>
                    <strong>Issued By:</strong> {{ Auth::user()->name }}
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%; text-align: center;">Image</th>
                        <th style="width: 40%;">Product</th>
                        <th style="width: 20%;">Variant</th>
                        <th style="width: 25%; text-align: center;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($issue->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="text-align: center;">
                                @if($item->product && $item->product->thumb_image)
                                    @php
                                        $imagePath = 'storage/'.$item->product->thumb_image;
                                        $fullPath = ($is_pdf ?? false) ? public_path($imagePath) : asset($imagePath);
                                    @endphp
                                    <img src="{{ $fullPath }}" alt="" width="40">
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                            </td>
                            <td>
                                @if($item->variant)
                                    {{ $item->variant->name }} 
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;">Total Quantity</td>
                        <td style="text-align: center;">{{ $issue->total_qty }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($issue->note)
        <div style="margin-bottom: 40px; background: #f8f9fa; padding: 15px; border-left: 4px solid #ddd;">
            <strong>Note:</strong> {{ $issue->note }}
        </div>
        @endif

        <div class="signature-section clearfix">
            <div class="signature-box">
                Authorized Signature
            </div>
            <div class="signature-box right">
                Receiver Signature
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p><small>Generated on: {{ now()->format('d M, Y h:i A') }}</small></p>
        </div>
    </div>
</body>
</html>
