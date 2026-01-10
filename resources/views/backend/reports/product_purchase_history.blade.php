@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product Purchase History</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Product Purchase Tracking</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Track Product Purchases by Vendor & User</h4>
                        </div>
                        <div class="card-body">
                            <div x-data="{ product_id: '{{ request('product_id') }}' }" class="mb-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Filter by Product</label>
                                        <select class="form-control select2" x-model="product_id" @change="window.location.href = '{{ route('admin.reports.product-purchase-history') }}?product_id=' + product_id">
                                            <option value="">All Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="button" @click="product_id = ''; window.location.href = '{{ route('admin.reports.product-purchase-history') }}'" class="btn btn-secondary btn-block">
                                            <i class="fas fa-redo"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice No</th>
                                            <th>Product</th>
                                            <th>Vendor</th>
                                            <th>Created By</th>
                                            <th>Qty</th>
                                            <th>Unit Cost</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($details as $detail)
                                            <tr>
                                                <td>{{ $detail->purchase->date }}</td>
                                                <td>{{ $detail->purchase->invoice_no }}</td>
                                                <td>{{ $detail->product->name }}</td>
                                                <td>{{ $detail->purchase->vendor->shop_name ?? 'N/A' }}</td>
                                                <td>{{ $detail->purchase->user->name ?? 'System' }}</td>
                                                <td>{{ $detail->qty }}</td>
                                                <td>${{ number_format($detail->unit_cost, 2) }}</td>
                                                <td>${{ number_format($detail->total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $("#table-1").dataTable({
            "order": [[0, "desc"]]
        });
    </script>
@endpush
