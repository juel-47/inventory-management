@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Purchase History Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Purchase History</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Purchase Invoices</h4>
                        </div>
                        <div class="card-body">
                            <div x-data="{ start_date: '{{ request('start_date') }}', end_date: '{{ request('end_date') }}', vendor_id: '{{ request('vendor_id') }}' }" class="mb-4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Start Date</label>
                                        <input type="date" class="form-control" x-model="start_date" @change="window.location.href = '{{ route('admin.reports.purchase') }}?start_date=' + start_date + '&end_date=' + end_date + '&vendor_id=' + vendor_id">
                                    </div>
                                    <div class="col-md-3">
                                        <label>End Date</label>
                                        <input type="date" class="form-control" x-model="end_date" @change="window.location.href = '{{ route('admin.reports.purchase') }}?start_date=' + start_date + '&end_date=' + end_date + '&vendor_id=' + vendor_id">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Vendor</label>
                                        <select class="form-control" x-model="vendor_id" @change="window.location.href = '{{ route('admin.reports.purchase') }}?start_date=' + start_date + '&end_date=' + end_date + '&vendor_id=' + vendor_id">
                                            <option value="">All Vendors</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="button" @click="start_date = ''; end_date = ''; vendor_id = ''; window.location.href = '{{ route('admin.reports.purchase') }}'" class="btn btn-secondary btn-block">
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
                                            <th>Vendor</th>
                                            <th>Created By</th>
                                            <th>Items Count</th>
                                             <th>Base Total</th>
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
                                                <td>{{ $purchase->details->count() }}</td>
                                                <td>{!! formatConverted($purchase->total_amount) !!}</td>
                                                <td>
                                                    <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
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
            "order": [[0, "desc"]]
        });
    </script>
@endpush
