@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Sales</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Sales</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Sales</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.sales.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Sale</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice No</th>
                                            <th>Outlet</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sales as $sale)
                                            <tr>
                                                <td>{{ $sale->date }}</td>
                                                <td>{{ $sale->invoice_no }}</td>
                                                <td>{{ $sale->outletUser->name ?? 'N/A' }}</td>
                                                <td>{!! formatWithCurrency($sale->total_amount) !!}</td>
                                                <td>
                                                    @if($sale->status == 1)
                                                        <div class="badge badge-success">Completed</div>
                                                    @else
                                                        <div class="badge badge-warning">Draft</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
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
        $("#table-1").dataTable({
            "columnDefs": [
                { "sortable": false, "targets": [5] }
            ]
        });
    </script>
@endpush
