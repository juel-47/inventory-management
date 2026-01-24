@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Product</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-body p-3">
                            <form id="filter-form">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-3 mb-3">
                                        <div class="input-group shadow-sm" style="border-radius: 25px; overflow: hidden; background-color: #f4f6f9; border: 1px solid #e0e0e0;">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-0 pl-3 pr-2" style="background-color: transparent;">
                                                    <i class="fas fa-search text-secondary"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control search-input border-0 pl-1" name="search" placeholder="Search..." value="{{ request('search') }}" style="height: 40px; background-color: transparent;" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3 mb-3">
                                        <select name="category" id="category" class="form-control select2" style="border-radius: 25px;">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        <select name="sub_category" id="sub_category" class="form-control select2" style="border-radius: 25px;">
                                            <option value="">Sub Category</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        <select name="child_category" id="child_category" class="form-control select2" style="border-radius: 25px;">
                                            <option value="">Child Category</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 mb-3 text-right">
                                        @can('Manage Products')
                                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 btn-block">
                                                <i class="fas fa-plus"></i> Create
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-3 mb-3 mb-md-0">
                                        <select name="sort" id="sort" class="form-control select2">
                                            <option value="a-z" {{ request('sort') == 'a-z' ? 'selected' : '' }}>A-Z way (Alphabetical)</option>
                                            <option value="z-a" {{ request('sort') == 'z-a' ? 'selected' : '' }}>Z-A way (Reverse)</option>
                                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Products</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3 mb-3 mb-md-0">
                                        <select name="alphabet" id="alphabet-dropdown" class="form-control select2">
                                            <option value="">Filter by Alphabet (All)</option>
                                            @foreach(range('A', 'Z') as $char)
                                                <option value="{{ $char }}" {{ request('alphabet') == $char ? 'selected' : '' }}>Starts with: {{ $char }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <button type="button" id="reset-filters" class="btn btn-danger btn-sm shadow-sm rounded-pill">
                                            <i class="fas fa-redo mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div id="product-grid-container">
                @include('backend.product.product_grid')
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <style>
        /* Professional Pagination Styling - Enhanced for White Background */
        .custom-pagination .pagination {
            margin-bottom: 0;
            gap: 6px;
        }
        .custom-pagination .page-item .page-link {
            border: 1px solid #dfe3e8;
            color: #454f5b;
            padding: 10px 18px;
            border-radius: 6px !important;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease-in-out;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); /* Subtle border shadow */
        }
        .custom-pagination .page-item.active .page-link {
            background-color: #6777ef;
            border-color: #6777ef;
            color: #fff;
            box-shadow: 0 4px 12px rgba(103, 119, 239, 0.25);
        }
        .custom-pagination .page-item .page-link:hover {
            background-color: #f4f6f8;
            color: #6777ef;
            border-color: #6777ef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        }
        .custom-pagination .page-item.disabled .page-link {
            background-color: #f9fafb;
            border-color: #e1e4e8;
            color: #919eab;
            box-shadow: none;
        }
        .custom-pagination .page-item:first-child .page-link,
        .custom-pagination .page-item:last-child .page-link {
            background-color: #f4f6f8;
            font-weight: bold;
        }
        .alphabet-filter .alphabet-btn {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            padding: 0;
            font-weight: 600;
        }
        .alphabet-filter .alphabet-btn.active {
            background-color: #6777ef;
            color: #fff;
            border-color: #6777ef;
        }
    </style>
    <script>
        $(document).ready(function() {
            let initialLoad = true;

            // Prevent Enter key from submitting form
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
            });

            function fetchProducts(url = "{{ route('admin.products.index') }}") {
                if (initialLoad) {
                    initialLoad = false;
                    return;
                }

                let search = $('.search-input').val();
                let category = $('#category').val();
                let sub_category = $('#sub_category').val();
                let child_category = $('#child_category').val();
                let alphabet = $('#alphabet-dropdown').val();
                let sort = $('#sort').val();

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: { 
                        search: search,
                        category: category,
                        sub_category: sub_category,
                        child_category: child_category,
                        alphabet: alphabet,
                        sort: sort
                    },
                    beforeSend: function() {
                         $('#product-grid-container').css('opacity', '0.5');
                    },
                    success: function(response) {
                        $('#product-grid-container').html(response);
                        $('#product-grid-container').css('opacity', '1');
                        
                        // Update history API without reloading
                        let params = new URLSearchParams({
                            search: search,
                            category: category,
                            sub_category: sub_category,
                            child_category: child_category,
                            alphabet: alphabet,
                            sort: sort
                        });
                        
                        // Handle pagination page if in URL
                        let pageMatch = url.match(/page=(\d+)/);
                        if (pageMatch) {
                            params.set('page', pageMatch[1]);
                        }

                        let newUrl = "{{ route('admin.products.index') }}" + '?' + params.toString();
                        window.history.replaceState({path: newUrl}, '', newUrl);

                        // Scroll to top only if needed
                        if ($(window).scrollTop() > 200) {
                            $('html, body').stop().animate({ scrollTop: 0 }, 400);
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        $('#product-grid-container').css('opacity', '1');
                    }
                });
            }

            // Status Change
            $('body').on('change', '.change-status', function() {
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.products.change-status') }}",
                    method: 'PUT',
                    data: {
                        status: isChecked,
                        id: id
                    },
                    success: function(data) {
                        toastr.success(data.message)
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })

            // Auto Search
            let timeout = null;
            $('body').on('input', '.search-input', function() {
                initialLoad = false; // Allow search to trigger even if first action
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    fetchProducts();
                }, 300); 
            });

            // Category Change
            $('body').on('change', '#category', function() {
                let id = $(this).val();
                
                // Clear and reset sub/child categories
                $('#sub_category').html('<option value="">Sub Category</option>').trigger('change');
                $('#child_category').html('<option value="">Child Category</option>').trigger('change');

                if (id) {
                    $.ajax({
                        url: "{{ route('admin.get-subCategories') }}",
                        method: 'GET',
                        data: { id: id },
                        success: function(data) {
                            $.each(data, function(i, item) {
                                $('#sub_category').append(`<option value="${item.id}">${item.name}</option>`);
                            });
                        }
                    });
                }
                fetchProducts();
            });

            // Sub Category Change
            $('body').on('change', '#sub_category', function() {
                let id = $(this).val();
                
                // Clear and reset child categories
                $('#child_category').html('<option value="">Child Category</option>').trigger('change');

                if (id) {
                    $.ajax({
                        url: "{{ route('admin.get-child-categories') }}",
                        method: 'GET',
                        data: { id: id },
                        success: function(data) {
                            $.each(data, function(i, item) {
                                $('#child_category').append(`<option value="${item.id}">${item.name}</option>`);
                            });
                        }
                    });
                }
                fetchProducts();
            });

            // Child Category Filter
            $('body').on('change', '#child_category', function() {
                fetchProducts();
            });

            // Sort Change
            $('body').on('change', '#sort', function() {
                fetchProducts();
            });

            // Alphabet Dropdown Change
            $('body').on('change', '#alphabet-dropdown', function() {
                fetchProducts();
            });

            // Reset Filters
            $('body').on('click', '#reset-filters', function() {
                initialLoad = false; // Allow reset to trigger
                $('.search-input').val('');
                $('#category').val('').trigger('change');
                $('#sub_category').html('<option value="">Sub Category</option>').trigger('change');
                $('#child_category').html('<option value="">Child Category</option>').trigger('change');
                $('#alphabet-dropdown').val('').trigger('change');
                $('#sort').val('a-z').trigger('change');
                
                fetchProducts();
            });
            
            // Handle Pagination clicks via AJAX
             $('body').on('click', '.pagination a', function(e) {
                e.preventDefault();
                initialLoad = false; // Allow pagination to trigger
                let url = $(this).attr('href');
                fetchProducts(url);
            });

            // Set initialLoad to false after a short delay so that user actions work
            setTimeout(function() {
                initialLoad = false;
            }, 1000);
        })
    </script>
@endpush
