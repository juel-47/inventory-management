@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product Requests</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Product Requests</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.product-requests.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Request No</th>
                                            <th>Requester</th>
                                            <th>Total Qty</th>
                                             <th>Base Total</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productRequests as $request)
                                            <tr>
                                                <td>{{ $request->id }}</td>
                                                <td>{{ $request->request_no }}</td>
                                                <td>{{ $request->user->name }}</td>
                                                <td>{{ $request->total_qty }}</td>
                                                <td>{!! formatConverted($request->total_amount) !!}</td>
                                                <td>
                                                    @if($request->status == 'pending')
                                                        <span class="badge badge-warning">Pending</span>
                                                    @elseif($request->status == 'approved')
                                                        <span class="badge badge-info">Approved</span>
                                                    @elseif($request->status == 'rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @elseif($request->status == 'shipped')
                                                        <span class="badge badge-primary">Shipped</span>
                                                    @elseif($request->status == 'completed')
                                                        <span class="badge badge-success">Completed</span>
                                                    @endif
                                                </td>
                                                <td>{{ $request->created_at->format('d M, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.product-requests.show', $request->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                                                    @if($request->status == 'pending' || Auth::user()->hasRole('Admin'))
                                                        <a href="{{ route('admin.product-requests.destroy', $request->id) }}" class="btn btn-danger btn-sm delete-item"><i class="fas fa-trash"></i></a>
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
        $("#table-1").dataTable({
            "columnDefs": [
                { "sortable": false, "targets": [7] }
            ]
        });
    </script>
@endpush
