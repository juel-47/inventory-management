@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Stock Valuation Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Stock Valuation</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Current Inventory Value</h4>
                        </div>
                        <div class="card-body">
                            <div x-data="{ category_id: '{{ request('category_id') }}', brand_id: '{{ request('brand_id') }}' }" class="mb-4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Category</label>
                                        <select class="form-control" x-model="category_id" @change="window.location.href = '{{ route('admin.reports.stock') }}?category_id=' + category_id + '&brand_id=' + brand_id">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Brand</label>
                                        <select class="form-control" x-model="brand_id" @change="window.location.href = '{{ route('admin.reports.stock') }}?category_id=' + category_id + '&brand_id=' + brand_id">
                                            <option value="">All Brands</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="button" @click="category_id = ''; brand_id = ''; window.location.href = '{{ route('admin.reports.stock') }}'" class="btn btn-secondary btn-block">
                                            <i class="fas fa-redo"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>local Purchase Unit Price</th>
                                            <th>Total price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalValue = 0; @endphp
                                        @foreach ($products as $product)
                                            @php 
                                                $totalValue += ($product->total ?? 0);
                                            @endphp
                                            <tr>
                                                <td>
                                                    @if($product->thumb_image)
                                                        <img src="{{ asset('storage/' . $product->thumb_image) }}" width="50" alt="">
                                                    @else
                                                        <span class="badge badge-secondary">No Image</span>
                                                    @endif
                                                </td>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                <td>{{ $product->qty }}</td>
                                                <td>{{ $product->unit->name ?? 'N/A' }}</td>
                                                <td>{!! formatConverted($product->purchase_price) !!}</td>
                                                {{-- <td>{!! formatConverted($value) !!}</td> --}}
                                                <td>{!! formatConverted($product->total ?? 0) !!}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6" class="text-right">Total Inventory Value:</th>
                                            <th>{!! formatConverted($totalValue) !!}</th>
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
        $("#table-1").dataTable();
    </script>
@endpush
