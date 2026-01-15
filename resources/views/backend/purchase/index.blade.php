@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Purchases</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Purchases</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Purchases</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Purchase</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice No</th>
                                            <th>Vendor</th>
                                            <th>Created By</th>
                                            <th>local currency Total</th>
                                            <th>Vendor Total price</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->date }}</td>
                                                <td>{{ $purchase->invoice_no }}</td>
                                                <td>{{ $purchase->vendor->shop_name ?? 'N/A' }}</td>
                                                <td>{{ $purchase->user->name ?? 'System' }}</td>
                                                <td>{{ formatConverted($purchase->total_amount) }}</td>
                                                <td>
                                                    @if($purchase->vendor)
                                                        @php
                                                            $vendorSubtotal = $purchase->details->sum(function($d) { return $d->unit_cost_vendor * $d->qty; });
                                                        @endphp
                                                        {{ $purchase->vendor->currency_icon }}{{ number_format($vendorSubtotal, 2) }}
                                                    @else
                                                        {{ formatConverted($purchase->total_amount) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($purchase->status == 1)
                                                        <div class="badge badge-success">Completed</div>
                                                    @else
                                                        <div class="badge badge-warning">Draft</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                                    
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
                { "sortable": false, "targets": [6] }
            ]
        });
    </script>
@endpush
