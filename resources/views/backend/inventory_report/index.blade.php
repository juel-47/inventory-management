@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Current Inventory Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Inventory Stock</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Stock Levels</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-inventory">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Variant</th>
                                            <th>Item Number</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stocks as $stock)
                                            <tr>
                                                <td>{{ $stock->product->name ?? 'N/A' }}</td>
                                                <td>{{ $stock->variant ? $stock->variant->name : '-' }}</td>
                                                <td>{{ $stock->product->product_number ?? '-' }}</td>
                                                <td>{{ $stock->product->category->name ?? '-' }}</td>
                                                <td class="font-weight-bold {{ $stock->quantity <= 5 ? 'text-danger' : 'text-success' }}">
                                                    {{ $stock->quantity }}
                                                </td>
                                                <td>
                                                    @if($stock->quantity > 0)
                                                        <div class="badge badge-success">In Stock</div>
                                                    @else
                                                        <div class="badge badge-danger">Out of Stock</div>
                                                    @endif
                                                </td>
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
        $("#table-inventory").dataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Current Inventory Report'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Current Inventory Report'
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Current Inventory Report'
                }
            ]
        });
    </script>
@endpush
