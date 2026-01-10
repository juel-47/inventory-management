@extends('backend.layouts.master')
@section('title', 'Admin Dashboard')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-chart-pie mr-2 text-primary"></i>Dashboard</h1>
        </div>

        <div class="row">
            @if(Auth::user()->hasRole('Admin'))
                {{-- Admin Stats --}}
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Sales</h4>
                            </div>
                            <div class="card-body">
                                {!! formatWithCurrency($totalSales) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pending Requests</h4>
                            </div>
                            <div class="card-body">
                                {{ $pendingRequests }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Products</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalProducts }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Active Outlets</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalOutlets }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Outlet Stats --}}
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Spent</h4>
                            </div>
                            <div class="card-body">
                                {!! formatWithCurrency($myTotalSpent) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-info">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Requests</h4>
                            </div>
                            <div class="card-body">
                                {{ $myTotalRequests }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pending Orders</h4>
                            </div>
                            <div class="card-body">
                                {{ $myPendingRequests }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card border shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h4 class="text-dark"><i class="fas fa-history mr-2 text-primary"></i>Recent Product Requests</h4>
                        <a href="{{ route('admin.product-requests.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-whitesmoke">
                                    <tr>
                                        <th class="pl-4">Request No</th>
                                        @if(Auth::user()->hasRole('Admin'))
                                        <th>Requester</th>
                                        @endif
                                        <th>Date</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-right pr-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentRequests as $request)
                                        <tr>
                                            <td class="pl-4 font-weight-bold">{{ $request->request_no }}</td>
                                            @if(Auth::user()->hasRole('Admin'))
                                            <td>{{ $request->user->name }}</td>
                                            @endif
                                            <td>{{ $request->created_at->format('d M, Y') }}</td>
                                            <td class="text-right font-weight-bold text-dark">{!! formatWithCurrency($request->total_amount) !!}</td>
                                            <td class="text-center">
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'warning',
                                                        'approved' => 'info',
                                                        'shipped' => 'primary',
                                                        'completed' => 'success',
                                                        'rejected' => 'danger'
                                                    ];
                                                    $class = $statusClasses[$request->status] ?? 'dark';
                                                @endphp
                                                <span class="badge badge-{{ $class }} text-uppercase">{{ $request->status }}</span>
                                            </td>
                                            <td class="text-right pr-4">
                                                <a href="{{ route('admin.product-requests.show', $request->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">Details</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted font-italic">No recent requests found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
