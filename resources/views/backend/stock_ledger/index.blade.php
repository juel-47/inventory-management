@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Stock Ledger</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Stock Ledger</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Inventory Movement History</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-ledger">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th width="80">Image</th>
                                            <th>Product</th>
                                            <th>Variant</th>
                                            <th>Reference</th>
                                            <th>Type</th>
                                            <th>In Qty</th>
                                            <th>Out Qty</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ledgers as $ledger)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($ledger->date)->format('Y-m-d') }} {{ $ledger->created_at->format('h:i A') }}</td>
                                                <td>
                                                    @if($ledger->product && $ledger->product->thumb_image)
                                                        <img src="{{ asset('storage/'.$ledger->product->thumb_image) }}" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 40px; height: 40px;">N/A</div>
                                                    @endif
                                                </td>
                                                <td>{{ $ledger->product->name ?? 'Deleted' }}</td>
                                                <td>{{ $ledger->variant ? $ledger->variant->name : '-' }}</td>
                                                <td>{{ $ledger->reference_type }} #{{ $ledger->reference_id }}</td>
                                                <td>
                                                    @if($ledger->in_qty > 0)
                                                        <div class="badge badge-success">IN</div>
                                                    @else
                                                        <div class="badge badge-danger">OUT</div>
                                                    @endif
                                                </td>
                                                <td>{{ $ledger->in_qty }}</td>
                                                <td>{{ $ledger->out_qty }}</td>
                                                <td class="font-weight-bold">{{ $ledger->balance_qty }}</td>
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
        $("#table-ledger").dataTable({
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
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
                }
            ],
            "order": [[0, "desc"]]
        });
    </script>
@endpush
