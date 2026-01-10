@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Sale Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></div>
                <div class="breadcrumb-item">Invoice</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>Sale Invoice</h2>
                                <div class="invoice-number">Order #{{ $sale->invoice_no }}</div>
                            </div>
                            <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <address>
                                            <strong>Sold To (Outlet):</strong><br>
                                            {{ $sale->outletUser->name ?? 'Unknown' }}<br>
                                            {{ $sale->outletUser->email ?? '' }}
                                        </address>
                                    </div>
                                    <div class="col-md-6 text-md-right">
                                        <address>
                                            <strong>Sale Recorded By:</strong><br>
                                            {{ $sale->user->name ?? 'System' }}<br>
                                            {{ $sale->user->email ?? '' }}
                                        </address>
                                        <address>
                                            <strong>Sale Date:</strong><br>
                                            {{ $sale->date }}
                                        </address>
                                    </div>
                                </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Order Summary</div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tr>
                                        <th data-width="40">#</th>
                                        <th>Item</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-right">Totals</th>
                                    </tr>
                                    @foreach ($sale->details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $detail->product->name }} (SKU: {{ $detail->product->sku }})</td>
                                        <td class="text-center">${{ number_format($detail->unit_price, 2) }}</td>
                                        <td class="text-center">{{ $detail->qty }}</td>
                                        <td class="text-right">${{ number_format($detail->total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-8">
                                    @if($sale->note)
                                        <div class="section-title">Note</div>
                                        <p class="section-lead">{{ $sale->note }}</p>
                                    @endif
                                </div>
                                <div class="col-lg-4 text-right">
                                    <hr class="mt-2 mb-2">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Total</div>
                                        <div class="invoice-detail-value invoice-detail-value-lg">${{ number_format($sale->total_amount, 2) }}</div>
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
