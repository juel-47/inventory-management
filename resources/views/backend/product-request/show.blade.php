@extends('backend.layouts.master')

@section('title', 'Product Request Details')

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.product-requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Request #{{ $productRequest->request_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.product-requests.index') }}">Product Requests</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-lg-8">
                    {{-- Items Card --}}
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-list mr-2"></i>Itemized List</h4>
                            <div class="card-header-action">
                                <small class="text-muted">Date: {{ $productRequest->created_at->format('d M, Y h:i A') }}</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" width="5%">#</th>
                                            <th class="text-center" width="10%">Image</th>
                                            <th>Product Details</th>
                                            <th class="text-center">Current Stock</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Local Unit Price</th>
                                            <th class="text-right">Local Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productRequest->items as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="text-center">
                                                    @if($item->product->thumb_image)
                                                        <img src="{{ asset('storage/'.$item->product->thumb_image) }}" alt="{{ $item->product->name }}" width="50" style="border-radius: 4px; object-fit: cover; border: 1px solid #eee;">
                                                    @else
                                                        <span class="badge badge-light">No Image</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <a href="{{ route('admin.products.edit', $item->product_id) }}" class="font-weight-600 text-primary" style="text-decoration: none;">
                                                        {{ $item->product->name }}
                                                    </a>
                                                    @if($item->variant)
                                                        <div class="mt-1">
                                                            <span class="badge badge-primary px-2 py-1" style="font-size: 10px; text-transform: uppercase;">
                                                                {{ $item->variant->name }}
                                                                @if($item->variant->color || $item->variant->size)
                                                                    ({{ $item->variant->color->name ?? '' }}{{ $item->variant->color && $item->variant->size ? ' / ' : '' }}{{ $item->variant->size->name ?? '' }})
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div class="small text-muted mt-1 italic">Standard Edition</div>
                                                    @endif
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-info px-3">{{ $item->current_stock ?? 0 }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-light px-3">{{ $item->qty }}</span>
                                                </td>
                                                <td class="text-right align-middle font-weight-bold text-muted">
                                                    {!! formatConverted($item->unit_price) !!}
                                                </td>
                                                <td class="text-right align-middle font-weight-bold text-dark">
                                                    {!! formatConverted($item->subtotal) !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-whitesmoke">
                                        <tr>
                                    <tfoot class="bg-whitesmoke">
                                        <tr>
                                            <td colspan="6" class="text-right font-weight-bold text-muted text-uppercase small" style="vertical-align: middle;">Total Request Amount</td>
                                            <td class="text-right font-weight-bold h6 text-primary mb-0" style="vertical-align: middle;">
                                                {!! formatConverted($productRequest->total_amount) !!}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Notes Card --}}
                    @if($productRequest->note)
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-sticky-note mr-2"></i>Requester Note</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 bg-light p-3 rounded text-muted font-italic">{{ $productRequest->note }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-12 col-lg-4">
                    {{-- Status Card --}}
                    <div class="card card-statistic-1 mb-3">
                         <div class="card-icon bg-primary">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Current Status</h4>
                            </div>
                             <div class="card-body">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'shipped' => 'primary',
                                        'completed' => 'success'
                                    ];
                                    $statusColor = $statusColors[$productRequest->status] ?? 'secondary';
                                @endphp
                                <div class="text-{{ $statusColor }} text-uppercase">{{ $productRequest->status }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Requester Info --}}
                    <div class="card mb-3">
                         <div class="card-header">
                            <h4>Requester</h4>
                        </div>
                        <div class="card-body">
                             <div class="d-flex align-items-center">
                                <div class="avatar-item mr-3">
                                    <img alt="image" src="https://ui-avatars.com/api/?name={{ urlencode($productRequest->user->name) }}&background=e3eaef&color=3c8dbc" class="rounded-circle" width="50">
                                </div>
                                <div>
                                    <div class="font-weight-bold text-dark">{{ $productRequest->user->name }}</div>
                                    <div class="text-small text-muted">{{ $productRequest->user->email }}</div>
                                    <div class="text-small text-muted">{{ $productRequest->user->role }}</div>
                                </div>
                            </div>
                            <div class="mt-3 text-small text-muted">
                                <i class="fas fa-phone mr-1"></i> {{ $productRequest->user->phone ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    {{-- Admin Actions --}}
                    <div class="card card-warning">
                        <div class="card-header">
                            <h4><i class="fas fa-user-cog mr-2"></i>Actions</h4>
                        </div>
                        <div class="card-body">
                            @if(Auth::user()->can('Manage Product Requests'))
                                <form action="{{ route('admin.product-requests.update-status', $productRequest->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold small text-muted text-uppercase">Change Status</label>
                                        <select name="status" class="form-control select2">
                                            <option value="pending" {{ $productRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $productRequest->status == 'approved' ? 'selected' : '' }}>Approve (<code>create issue</code>)</option>
                                             @if($productRequest->status == 'completed')
                                                <option value="completed" selected>Completed</option>
                                            @endif
                                            {{-- <option value="shipped" {{ $productRequest->status == 'shipped' ? 'selected' : '' }}>Shipped / Dispatched</option>
                                            <option value="completed" {{ $productRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="rejected" {{ $productRequest->status == 'rejected' ? 'selected' : '' }}>Reject</option> --}}
                                        </select>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold small text-muted text-uppercase">Admin Note</label>
                                        <textarea name="admin_note" class="form-control" style="height: 80px;" placeholder="Internal tracking notes...">{{ $productRequest->admin_note }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block shadow-sm mb-3">
                                        Update Request
                                    </button>
                                </form>

                                @if($productRequest->status == 'approved')
                                    <div class="border-top pt-4 mt-2">
                                        <a href="{{ route('admin.issues.create', ['request_id' => $productRequest->id]) }}" class="btn btn-success btn-lg btn-block shadow-sm py-3 font-weight-bold">
                                            <i class="fas fa-box-open mr-2"></i> Create Stock Issue
                                        </a>
                                        <p class="text-center text-muted small mt-2 mb-0">Prepare items for physical issuance.</p>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-light border">
                                    <h6 class="alert-heading text-primary font-weight-bold mb-1">Response:</h6>
                                    <p class="mb-0 text-muted small">
                                        {{ $productRequest->admin_note ?? 'No admin notes available.' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
