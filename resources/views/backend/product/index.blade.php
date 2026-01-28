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
                                            <option value="latest" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>Latest Products</option>
                                            <option value="a-z" {{ request('sort') == 'a-z' ? 'selected' : '' }}>A-Z way (Alphabetical)</option>
                                            <option value="z-a" {{ request('sort') == 'z-a' ? 'selected' : '' }}>Z-A way (Reverse)</option>
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
    <!-- Floating Baskets Container -->
    <div class="position-fixed d-flex align-items-center" style="bottom: 30px; right: 30px; z-index: 9999; gap: 20px;">
        <!-- Floating Basket Widget (Product Request) -->
        @can('Create Product Requests')
        <div id="floating-request-basket" style="display: none;">
            <div class="d-flex flex-column align-items-center">
                <div class="cursor-pointer bg-success text-white shadow-lg rounded-circle d-flex align-items-center justify-content-center position-relative mb-2 basket-fab" 
                     id="go-to-request" title="Product Request" style="width: 55px; height: 55px; transition: all 0.3s ease;">
                    <i class="fas fa-file-import fa-lg"></i>
                    <span id="request-basket-count" class="badge badge-warning position-absolute" style="top: -5px; right: -5px; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px; border: 2px solid #fff; color: #000;">0</span>
                </div>
                <button class="btn btn-sm btn-light shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                        id="clear-request-basket" title="Clear Request Basket" style="width: 25px; height: 25px; padding: 0; opacity: 0.8;">
                    <i class="fas fa-times text-danger" style="font-size: 10px;"></i>
                </button>
            </div>
        </div>
        @endcan

        <!-- Floating Basket Widget (Booking) -->
        @can('Manage Order Place')
        <div id="floating-basket" style="display: none;">
            <div class="d-flex flex-column align-items-center">
                <div class="cursor-pointer bg-primary text-white shadow-lg rounded-circle d-flex align-items-center justify-content-center position-relative mb-2 basket-fab" 
                     id="go-to-booking" title="Place Order" style="width: 55px; height: 55px; transition: all 0.3s ease;">
                    <i class="fas fa-shopping-basket fa-lg"></i>
                    <span id="basket-count" class="badge badge-danger position-absolute" style="top: -5px; right: -5px; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px; border: 2px solid #fff;">0</span>
                </div>
                <button class="btn btn-sm btn-light shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                        id="clear-booking-basket" title="Clear Booking Basket" style="width: 25px; height: 25px; padding: 0; opacity: 0.8;">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 10px;"></i>
                </button>
            </div>
        </div>
        @endcan
    </div>

    <style>
        .hover-white { transition: color 0.2s ease; }
        .hover-white:hover { color: #fff !important; }
        .cursor-pointer { cursor: pointer; }
        
        /* Animation Styles */
        @keyframes shake-basket {
            0% { transform: scale(1) rotate(0); }
            20% { transform: scale(1.2) rotate(-10deg); }
            40% { transform: scale(1.2) rotate(10deg); }
            60% { transform: scale(1.2) rotate(-10deg); }
            80% { transform: scale(1.2) rotate(10deg); }
            100% { transform: scale(1) rotate(0); }
        }
        .animate-shake {
            animation: shake-basket 0.5s ease-in-out;
        }

        .basket-fab:hover {
            transform: scale(1.1);
            filter: brightness(1.1);
        }
        .basket-fab {
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        }
        .add-to-basket.added, .add-to-request-basket.added {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }
    </style>
    <script>
        $(document).ready(function() {
            let initialLoad = true;

            // --- Basket Logic Start ---
            let booking_basket = JSON.parse(localStorage.getItem('booking_basket')) || [];
            let request_basket = JSON.parse(localStorage.getItem('request_basket')) || [];

            function updateBasketUI() {
                // Update Booking Basket
                $('#basket-count').text(booking_basket.length);
                if (booking_basket.length > 0) {
                    $('#floating-basket').fadeIn();
                } else {
                    $(`#floating-basket`).fadeOut();
                }

                // Update Request Basket
                if ($('#request-basket-count').length) {
                    $('#request-basket-count').text(request_basket.length);
                    if (request_basket.length > 0) {
                        $('#floating-request-basket').fadeIn();
                    } else {
                        $('#floating-request-basket').fadeOut();
                    }
                }

                // Update Booking Buttons
                $('.add-to-basket').each(function() {
                    let id = $(this).data('id');
                    if (booking_basket.includes(id)) {
                        $(this).addClass('added').html('<i class="fas fa-check mr-1"></i> Added to Basket');
                    } else {
                        $(this).removeClass('added').html('<i class="fas fa-shopping-basket mr-1"></i> Add to Basket');
                    }
                });

                // Update Request Buttons
                $('.add-to-request-basket').each(function() {
                    let id = $(this).data('id');
                    if (request_basket.includes(id)) {
                        $(this).addClass('added').html('<i class="fas fa-check mr-1"></i> Added to Request');
                    } else {
                        $(this).removeClass('added').html('<i class="fas fa-file-import mr-1"></i> Add to Request Basket');
                    }
                });
            }

            // Initial UI Update
            updateBasketUI();

            // Clear Request Basket
            $('#clear-request-basket').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                Swal.fire({
                    title: 'Clear Request Basket?',
                    text: "You are about to remove all items from the request basket.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        request_basket = [];
                        localStorage.setItem('request_basket', JSON.stringify(request_basket));
                        updateBasketUI();
                        toastr.info('Request basket cleared');
                    }
                });
            });

            // Clear Booking Basket
            $('#clear-booking-basket').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                Swal.fire({
                    title: 'Clear Booking Basket?',
                    text: "You are about to remove all items from the booking basket.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6777ef',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        booking_basket = [];
                        localStorage.setItem('booking_basket', JSON.stringify(booking_basket));
                        updateBasketUI();
                        toastr.info('Booking basket cleared');
                    }
                });
            });

            // Add to Booking Basket Click
            $('body').on('click', '.add-to-basket', function() {
                let id = $(this).data('id');
                if (booking_basket.includes(id)) {
                    booking_basket = booking_basket.filter(itemId => itemId !== id);
                    toastr.info('Removed from basket');
                } else {
                    booking_basket.push(id);
                    toastr.success('Added to basket');
                    // Trigger Shake
                    $('#go-to-booking').addClass('animate-shake');
                    setTimeout(() => $('#go-to-booking').removeClass('animate-shake'), 500);
                }
                localStorage.setItem('booking_basket', JSON.stringify(booking_basket));
                updateBasketUI();
            });

            // Add to Request Basket Click
            $('body').on('click', '.add-to-request-basket', function() {
                let id = $(this).data('id');
                if (request_basket.includes(id)) {
                    request_basket = request_basket.filter(itemId => itemId !== id);
                    toastr.info('Removed from request basket');
                } else {
                    request_basket.push(id);
                    toastr.success('Added to request basket');
                    // Trigger Shake
                    $('#go-to-request').addClass('animate-shake');
                    setTimeout(() => $('#go-to-request').removeClass('animate-shake'), 500);
                }
                localStorage.setItem('request_basket', JSON.stringify(request_basket));
                updateBasketUI();
            });

            // Navigation
            $('#go-to-booking').on('click', function() {
                window.location.href = "{{ route('admin.bookings.create') }}";
            });

            $('#go-to-request').on('click', function() {
                let ids = request_basket.join(',');
                window.location.href = "{{ route('admin.product-requests.create') }}?ids=" + ids;
            });

            // Re-apply UI state after AJAX load
            $(document).ajaxComplete(function() {
                updateBasketUI();
            });
            // --- Basket Logic End ---

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
                let $this = $(this); // Store reference

                $.ajax({
                    url: "{{ route('admin.products.change-status') }}",
                    method: 'PUT',
                    data: {
                        status: isChecked,
                        id: id
                    },
                    success: function(data) {
                         toastr.success(data.message); 
                    },
                    error: function(xhr, status, error) {
                        console.error("Status update error:", error);
                        console.log("Response:", xhr.responseText);
                        if(xhr.status !== 200) {
                             $this.prop('checked', !isChecked);
                             toastr.error('Failed to update status');
                        }
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
                $('#sort').val('latest').trigger('change');
                
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
