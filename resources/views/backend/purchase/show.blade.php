@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Purchase Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></div>
                <div class="breadcrumb-item">Invoice</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>Invoice</h2>
                                <div class="invoice-number">Order #{{ $purchase->invoice_no }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Vendor:</strong><br>
                                        {{ $purchase->vendor->shop_name }}<br>
                                        {{ $purchase->vendor->address }}<br>
                                        {{ $purchase->vendor->phone }}
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Created By:</strong><br>
                                        {{ $purchase->user->name ?? 'System' }}<br>
                                        {{ $purchase->user->email ?? '' }}
                                    </address>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Order Date:</strong><br>
                                        {{ $purchase->date }}<br><br>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Order Summary</div>
                            <p class="section-lead">All items here cannot be deleted.</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tr>
                                        <th data-width="40">#</th>
                                        <th>Item</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Vendor Price</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Vendor Total</th>
                                    </tr>
                                    @foreach ($purchase->details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{ $detail->product->name }} (SKU: {{ $detail->product->sku }})
                                            @if($detail->variant_info)
                                                <div class="mt-1">
                                                    @foreach($detail->variant_info as $name => $qty)
                                                        @if(is_array($qty)) {{-- Fallback for old single-item format if it exists --}}
                                                            @continue
                                                        @endif
                                                        <span class="badge badge-light border text-muted small mr-1 mb-1">
                                                            {{ is_numeric($name) ? '' : $name.':' }} {{ $qty }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ formatConverted($detail->unit_cost) }}</td>
                                        <td class="text-center">{{ formatWithVendor($detail->unit_cost, $purchase->vendor->currency_icon, $purchase->vendor->currency_rate) }}</td>
                                        <td class="text-center">{{ $detail->qty }}</td>
                                        <td class="text-right">{{ formatConverted($detail->total) }}</td>
                                        <td class="text-right">{{ formatWithVendor($detail->total, $purchase->vendor->currency_icon, $purchase->vendor->currency_rate) }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-8">
                                    @if($purchase->note)
                                        <div class="section-title">Note</div>
                                        <p class="section-lead">{{ $purchase->note }}</p>
                                    @endif
                                </div>
                                <div class="col-lg-4 text-right">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Subtotal (Items)</div>
                                        <div class="invoice-detail-value">{{ formatConverted($purchase->total_amount - ($purchase->material_cost + $purchase->transport_cost + $purchase->tax)) }}</div>
                                    </div>
                                    @if($purchase->material_cost > 0)
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Material Cost</div>
                                        <div class="invoice-detail-value">{{ formatConverted($purchase->material_cost) }}</div>
                                    </div>
                                    @endif
                                    @if($purchase->transport_cost > 0)
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Transport Cost</div>
                                        <div class="invoice-detail-value">{{ formatConverted($purchase->transport_cost) }}</div>
                                    </div>
                                    @endif
                                    @if($purchase->tax > 0)
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Tax</div>
                                        <div class="invoice-detail-value">{{ formatConverted($purchase->tax) }}</div>
                                    </div>
                                    @endif
                                    <hr class="mt-2 mb-2">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Grand Total</div>
                                        <div class="invoice-detail-value invoice-detail-value-lg">{{ formatConverted($purchase->total_amount) }}</div>
                                    </div>
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Vendor Total ({{ $purchase->vendor->currency_name }})</div>
                                        <div class="invoice-detail-value">{{ formatWithVendor($purchase->total_amount, $purchase->vendor->currency_icon, $purchase->vendor->currency_rate) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-md-right">
                     <button class="btn btn-warning btn-icon icon-left" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                </div>
            </div>
        </div>
    </section>
@endsection
