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
            <div class="row mb-4 justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <form id="search-form">
                        <div class="input-group" style="border-radius: 25px; overflow: hidden; background-color: #e7eaed; border: 1px solid #d1d3e2;">
                            <div class="input-group-prepend">
                                <span class="input-group-text border-0 pl-3 pr-2" style="background-color: transparent;">
                                    <i class="fas fa-search text-secondary"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control search-input border-0 pl-1" name="search" placeholder="Type to search products..." value="{{ request('search') }}" style="height: 45px; background-color: transparent; color: #444; font-weight: 500;" autocomplete="off">
                        </div>
                    </form>
                </div>
                <div class="col-12 col-md-4 col-lg-3 text-md-right mt-3 mt-md-0">
                    @can('Manage Products')
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-lg shadow-sm rounded-pill px-4">
                            <i class="fas fa-plus mr-2"></i>Create New
                        </a>
                    @endcan
                </div>
            </div>

            <div id="product-grid-container">
                @include('backend.product.product_grid')
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Prevent Enter key from submitting form
            $('#search-form').on('submit', function(e) {
                e.preventDefault();
            });

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
                let search = $(this).val();
                
                // Clear existing timeout to debounce
                clearTimeout(timeout);

                // Set new timeout (500ms -> reduced to 300ms for snappier feel provided user types normally)
                timeout = setTimeout(function() {
                    let url = "{{ route('admin.products.index') }}";
                    
                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: { search: search },
                        beforeSend: function() {
                             // Show loader or opacity change
                             $('#product-grid-container').css('opacity', '0.5');
                        },
                        success: function(response) {
                            $('#product-grid-container').html(response);
                            $('#product-grid-container').css('opacity', '1');
                            
                            // Update history API without reloading
                            let newUrl = url + (search ? '?search=' + search : '');
                            window.history.replaceState({path: newUrl}, '', newUrl);
                        },
                        error: function(xhr) {
                            console.log(xhr);
                            $('#product-grid-container').css('opacity', '1');
                        }
                    });
                }, 300); 
            });
            
            // Handle Pagination clicks via AJAX (Optional enhancement)
             $('body').on('click', '.pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                window.history.pushState({path: url}, '', url);
                
                $.ajax({
                    url: url,
                    success: function(response) {
                        $('#product-grid-container').html(response);
                    }
                });
            });
        })
    </script>
@endpush
