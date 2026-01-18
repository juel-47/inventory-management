@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-chart-pie mr-2 text-primary"></i>Stock  Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Stock</div>
            </div>
        </div>

        <div class="section-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Stock Qty</h4>
                            </div>
                            <div class="card-body">
                                <span id="span-total-qty">{{ number_format($totalQty) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Asset Value</h4>
                            </div>
                            <div class="card-body">
                                <span id="span-total-value">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-info">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Potential Revenue</h4>
                            </div>
                            <div class="card-body">
                                <span id="span-potential-revenue">{{ $settings->currency_icon }}{{ number_format($potentialRevenue, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                             <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Potential Profit</h4>
                            </div>
                            <div class="card-body">
                                <span id="span-potential-profit">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h4><i class="fas fa-filter mr-2"></i>Filter Options</h4>
                    <div class="card-header-action">
                        <a data-collapse="#mycard-collapse" class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                    </div>
                </div>
                <div class="collapse show" id="mycard-collapse">
                    <div class="card-body">
                        <form action="{{ route('admin.reports.stock') }}" method="GET">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select name="category_id" class="form-control select2">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ request()->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Brand</label>
                                        <select name="brand_id" class="form-control select2">
                                            <option value="">All Brands</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ request()->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2" style="margin-top: 29px;">
                                    <a href="{{ route('admin.reports.stock') }}" class="btn btn-danger btn-block"><i class="fas fa-undo"></i> Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4>Detailed Stock List</h4>
                            <div class="card-header-action">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-stock">
                                    <thead>
                                        <tr>
                                            <th>Product Info</th>
                                            <th>Category / Brand</th>
                                            <th class="text-center">Stock Qty</th>
                                            <th class="text-right">Unit Cost</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Total Asset Value</th>
                                            <th class="text-right">Profit Potential</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stock-table-body">
                                        @include('backend.reports.partials.stock_table_rows')
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="5" class="text-right text-dark">GRAND TOTAL:</td>
                                            <td class="text-right text-primary"><span id="span-grand-total-value">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</span></td>
                                            <td class="text-right text-success"><span id="span-grand-total-profit">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</span></td>
                                        </tr>
                                    </tfoot>
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
        var table = $("#table-stock").dataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });

        // AJAX Filter on Change
        $('select[name="category_id"], select[name="brand_id"]').on('change', function() {
            let category_id = $('select[name="category_id"]').val();
            let brand_id = $('select[name="brand_id"]').val();

            $.ajax({
                url: "{{ route('admin.reports.stock') }}",
                method: 'GET',
                data: {
                    category_id: category_id,
                    brand_id: brand_id
                },
                beforeSend: function() {
                    // Optional: Show loader or opacity
                    $('#table-stock').css('opacity', '0.5');
                },
                success: function(response) {
                    $('#table-stock').css('opacity', '1');
                    
                    // Update Summary Cards
                    $('#span-total-qty').text(response.totalQty);
                    $('#span-total-value').text(response.totalValue);
                    $('#span-potential-revenue').text(response.potentialRevenue);
                    $('#span-potential-profit').text(response.potentialProfit);
                    
                    // Update Footer Totals (Reuse summary values)
                    $('#span-grand-total-value').text(response.totalValue);
                    $('#span-grand-total-profit').text(response.potentialProfit);

                    // Update Table Content
                    // Destroy datatable, replace body, re-init datatable
                    table.fnDestroy(); 
                    $('#stock-table-body').html(response.html);
                    table = $("#table-stock").dataTable({
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });
                },
                error: function(xhr) {
                    console.error(xhr);
                    $('#table-stock').css('opacity', '1');
                    alert('Error loading data');
                }
            });
        });
    </script>
@endpush
