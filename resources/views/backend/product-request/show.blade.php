@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-file-invoice mr-2"></i>Request Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.product-requests.index') }}">Requests</a></div>
                <div class="breadcrumb-item">#{{ $productRequest->request_no }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-primary">Itemized Request</h4>
                            <span class="text-muted font-weight-bold">Order #{{ $productRequest->request_no }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-whitesmoke text-uppercase small font-weight-bold">
                                        <tr>
                                            <th class="pl-4">#</th>
                                            <th>Product Name</th>
                                             <th class="text-right">Base Unit Price</th>
                                             <th class="text-center">Quantity</th>
                                             <th class="text-right pr-4">Base Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productRequest->items as $item)
                                            <tr>
                                                <td class="pl-4">{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="font-weight-600">{{ $item->product->name }}</span><br>
                                                    <small class="text-muted">{{ $item->product->sku }}</small>
                                                </td>
                                                <td class="text-right">{!! formatConverted($item->unit_price) !!}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-light px-3">{{ $item->qty }}</span>
                                                </td>
                                                <td class="text-right pr-4 font-weight-bold text-dark">{!! formatConverted($item->subtotal) !!}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-top">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="section-title mt-0">Requested Note</div>
                                    <p class="text-muted mb-0">{{ $productRequest->note ?? 'No special instructions provided.' }}</p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="mb-2">
                                        <span class="text-muted mr-2">Total Amount:</span>
                                         <h3 class="d-inline text-primary">{!! formatConverted($productRequest->total_amount) !!}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0 text-white">Administration</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="text-muted small text-uppercase font-weight-bold">Requester Details</h6>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="bg-light rounded-circle p-2 mr-3">
                                        <i class="fas fa-user-tie text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold text-dark">{{ $productRequest->user->name }}</div>
                                        <div class="small text-muted">{{ $productRequest->user->email }}</div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h6 class="text-muted small text-uppercase font-weight-bold mb-3">Workflow Status</h6>
                                @php
                                    $statusClasses = [
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        'shipped' => 'primary',
                                        'completed' => 'success'
                                    ];
                                    $class = $statusClasses[$productRequest->status] ?? 'dark';
                                @endphp
                                <div class="alert alert-{{ $class }} border-0 shadow-sm text-center font-weight-bold text-uppercase py-2 mb-0">
                                    {{ $productRequest->status }}
                                </div>
                            </div>

                            @if(Auth::user()->hasRole('Admin'))
                                <form action="{{ route('admin.product-requests.update-status', $productRequest->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group">
                                        <label class="font-weight-bold">Update Tracking Status</label>
                                        <select name="status" class="form-control select2">
                                            <option value="pending" {{ $productRequest->status == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                            <option value="approved" {{ $productRequest->status == 'approved' ? 'selected' : '' }}>Approve (Create Sale)</option>
                                            <option value="rejected" {{ $productRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                                            <option value="shipped" {{ $productRequest->status == 'shipped' ? 'selected' : '' }}>Dispatched/Shipped</option>
                                            <option value="completed" {{ $productRequest->status == 'completed' ? 'selected' : '' }}>Mark as Completed</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Internal Admin Note</label>
                                        <textarea name="admin_note" class="form-control" rows="4" placeholder="Mention tracking numbers or approval notes...">{{ $productRequest->admin_note }}</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm">
                                        <i class="fas fa-save mr-2"></i> Update Request
                                    </button>
                                </form>
                            @else
                                <div class="card bg-light border-0 shadow-none">
                                    <div class="card-body p-3">
                                        <h6 class="small text-uppercase font-weight-bold text-primary mb-2">Message from Admin</h6>
                                        <p class="text-dark mb-0 font-italic">
                                            "{{ $productRequest->admin_note ?? 'Your request is currently being reviewed by the administration.' }}"
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
