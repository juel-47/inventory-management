@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Low Stock Alert</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Low Stock Alert</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Products Below Minimum Inventory Level</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Purchase Order
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($products->count() > 0)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>{{ $products->count() }}</strong> product(s) have stock levels at or below 100!
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th>Category</th>
                                                <th>Current Stock</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($products as $product)
                                                @php
                                                    $currentStock = $product->inventory_stocks_sum_quantity ?? 0;
                                                    $isCritical = $currentStock == 0;
                                                @endphp
                                                <tr class="{{ $isCritical ? 'table-danger' : '' }}">
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->sku }}</td>
                                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $isCritical ? 'danger' : 'warning' }}">
                                                            {{ $currentStock }} {{ $product->unit->name ?? '' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($isCritical)
                                                            <span class="badge badge-danger">OUT OF STOCK</span>
                                                        @else
                                                            <span class="badge badge-warning">LOW STOCK</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> 
                                    All products are adequately stocked!
                                </div>
                            @endif
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
            "order": [[3, "asc"]]
        });
    </script>
@endpush
