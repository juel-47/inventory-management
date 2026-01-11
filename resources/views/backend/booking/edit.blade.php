@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Booking</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Booking ({{ $booking->booking_no }})</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="section-title mt-0">General Information</div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Vendor</label>
                                            <select class="form-control select2" name="vendor_id">
                                                <option value="">Select Vendor</option>
                                                @foreach ($vendors as $vendor)
                                                    <option {{ $booking->vendor_id == $vendor->id ? 'selected' : '' }} value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Product</label>
                                            <select class="form-control select2" name="product_id" id="product_id">
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option {{ $booking->product_id == $product->id ? 'selected' : '' }} value="{{ $product->id }}">{{ $product->name }} (sku: {{ $product->sku }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Barcode</label>
                                            <input type="text" class="form-control" name="barcode" id="barcode" value="{{ $booking->barcode }}">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="section-title mt-0">Product Details (Read Only)</div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Product Name</label>
                                            <input type="text" class="form-control bg-light" id="product_name" value="{{ $booking->product->name ?? '' }}" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Product Number</label>
                                            <input type="text" class="form-control bg-light" id="product_number" value="{{ $booking->product->product_number ?? '' }}" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Category</label>
                                            <input type="text" class="form-control bg-light" id="product_category" value="{{ $booking->product->category->name ?? '' }}" readonly>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label>Product Image</label>
                                            <div>
                                                <img id="product_image" src="{{ $booking->product->thumb_image ? asset('storage/'.$booking->product->thumb_image) : '' }}" class="img-thumbnail" alt="Product Image" style="width: 120px; display: {{ $booking->product->thumb_image ? 'block' : 'none' }};">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="section-title mt-0">Categorization (Optional Override)</div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Category</label>
                                            <select class="form-control select2" name="category_id" id="category_id">
                                                <option value="">Select Category</option>
                                                @foreach ($categories as $category)
                                                    <option {{ $booking->category_id == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Sub Category</label>
                                            <select class="form-control select2" name="sub_category_id" id="sub_category_id">
                                                <option value="">Select Sub Category</option>
                                                @foreach ($subCategories as $subCategory)
                                                    <option {{ $booking->sub_category_id == $subCategory->id ? 'selected' : '' }} value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Child Category</label>
                                            <select class="form-control select2" name="child_category_id" id="child_category_id">
                                                <option value="">Select Child Category</option>
                                                @foreach ($childCategories as $childCategory)
                                                    <option {{ $booking->child_category_id == $childCategory->id ? 'selected' : '' }} value="{{ $childCategory->id }}">{{ $childCategory->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                         <div class="form-group col-md-4">
                                            <label>Variant</label>
                                            <select class="form-control" name="variant_info" id="variant_select">
                                                <option value="">Select Variant (Optional)</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Unit</label>
                                            <select class="form-control" name="unit_id" id="unit_select">
                                                <option value="">Select Unit</option>
                                                @foreach ($units as $unit)
                                                    <option {{ (isset($booking->unit_id) && $booking->unit_id == $unit->id) || (!isset($booking->unit_id) && $booking->product->unit_id == $unit->id) ? 'selected' : '' }} value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="section-title mt-0 text-primary">Pricing & Costing</div>
                                            <hr>
                                        </div>
                                        
                                        <div class="form-group col-md-3">
                                            <label class="font-weight-bold">Quantity</label>
                                            <input type="number" class="form-control form-control-lg" name="qty" value="{{ $booking->qty }}" required>
                                        </div>
                                        
                                        <div class="form-group col-md-3">
                                            <label>Unit Price (Vendor)</label>
                                             @php
                                                $rate = $booking->vendor->currency_rate > 0 ? $booking->vendor->currency_rate : 1;
                                                $vendorPrice = $booking->unit_price / $rate;
                                                $vendorExtra = $booking->extra_cost / $rate;
                                            @endphp
                                            <input type="number" class="form-control" name="unit_price" id="unit_price" value="{{ $vendorPrice }}" step="0.01">
                                            <small class="form-text text-muted">Enter price in Vendor's currency</small>
                                        </div>
                                        
                                        <div class="form-group col-md-3">
                                            <label>Extra Cost (Vendor)</label>
                                            <input type="number" class="form-control" name="extra_cost" id="extra_cost" value="{{ $vendorExtra }}" step="0.01">
                                        </div>
                                        
                                        <div class="form-group col-md-3">
                                            <label>Selling Price</label>
                                            <input type="number" class="form-control" name="sale_price" id="sale_price" step="0.01" value="{{ $booking->sale_price }}">
                                        </div>

                                        <!-- Calculated Fields Row -->
                                        <div class="col-md-12 mt-2">
                                            <div class="row p-3 bg-light rounded">
                                                <div class="form-group col-md-3 mb-0">
                                                    <label>System Unit Price</label>
                                                    <input type="text" class="form-control border-0 bg-transparent font-weight-bold p-0" id="unit_price_system" readonly>
                                                </div>
                                                <div class="form-group col-md-3 mb-0">
                                                    <label>Vendor Total</label>
                                                    <input type="text" class="form-control border-0 bg-transparent font-weight-bold p-0 text-primary" id="total_cost_vendor" style="font-size: 1.2em;" readonly>
                                                </div>
                                                <div class="form-group col-md-3 mb-0">
                                                    <label>System Total</label>
                                                    <input type="number" class="form-control border-0 bg-transparent font-weight-bold p-0" name="total_cost" id="total_cost" value="{{ $booking->total_cost }}" step="0.01" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea name="description" class="form-control" rows="4">{{ $booking->description }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card border">
                                                <div class="card-header bg-whitesmoke">
                                                    <h4>Custom Fields</h4>
                                                    <div class="card-header-action">
                                                        <button type="button" class="btn btn-sm btn-success" id="add-custom-field"><i class="fas fa-plus"></i> Add Field</button>
                                                    </div>
                                                </div>
                                                <div class="card-body" id="custom-fields-container">
                                                    @if($booking->custom_fields)
                                                        @foreach($booking->custom_fields as $index => $field)
                                                            <div class="row mb-2 align-items-center" id="custom-field-{{$index}}">
                                                                <div class="col-md-5">
                                                                    <input type="text" name="custom_fields[{{$index}}][key]" class="form-control" value="{{ $field['key'] }}" placeholder="Field Name">
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <input type="text" name="custom_fields[{{$index}}][value]" class="form-control" value="{{ $field['value'] }}" placeholder="Value">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="button" class="btn btn-danger btn-icon" onclick="$('#custom-field-{{$index}}').remove()"><i class="fas fa-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" name="status">
                                                    <option {{ $booking->status == 'pending' ? 'selected' : '' }} value="pending">Pending</option>
                                                    <option {{ $booking->status == 'complete' ? 'selected' : '' }} value="complete">Complete</option>
                                                    <option {{ $booking->status == 'cancelled' ? 'selected' : '' }} value="cancelled">Cancelled</option>
                                                    <option {{ $booking->status == 'missing' ? 'selected' : '' }} value="missing">Missing</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-12 text-right">
                                            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Update Booking</button>
                                        </div>
                                    </div>
    </section>
@endsection

@push('scripts')
    <script>
        const products = @json($products);
        let currentVendorRate = {{ $booking->vendor->currency_rate > 0 ? $booking->vendor->currency_rate : 1 }};
        let currentVendorIcon = "{{ $booking->vendor->currency_icon }}";

        $(document).ready(function() {
            
            function recalculateAllPrices() {
                let vendorPrice = parseFloat($('#unit_price').val()) || 0;
                let vendorExtra = parseFloat($('#extra_cost').val()) || 0;
                let qty = parseFloat($('input[name="qty"]').val()) || 0;
                
                // Calculate System Prices (Display is Vendor, Storage is System)
                // Logic: System = Vendor * Rate
                let rate = currentVendorRate > 0 ? currentVendorRate : 1;
                let systemPrice = vendorPrice * rate;
                let systemExtra = vendorExtra * rate;

                let systemTotal = (systemPrice * qty) + systemExtra;
                let vendorTotal = (vendorPrice * qty) + vendorExtra;

                $('#total_cost').val(systemTotal.toFixed(2));
                $('#unit_price_system').val('{{ $settings->currency_icon }}' + systemPrice.toFixed(2));
                
                // Vendor Display
                $('#total_cost_vendor').val(currentVendorIcon + vendorTotal.toFixed(2));
            }

            // Trigger initial calculation
            $('select[name="vendor_id"]').trigger('change');
            
            // Category Change - Load Subcategories
            $('#category_id').on('change', function() {
                let categoryId = $(this).val();
                $('#sub_category_id').html('<option value="">Select Sub Category (Optional)</option>');
                $('#child_category_id').html('<option value="">Select Child Category (Optional)</option>');
                
                if(categoryId) {
                    $.ajax({
                        url: "{{ route('admin.bookings.get-subcategories') }}",
                        method: 'GET',
                        data: { id: categoryId },
                        success: function(data) {
                            let html = '<option value="">Select Sub Category (Optional)</option>';
                            data.forEach(function(subCategory) {
                                html += `<option value="${subCategory.id}">${subCategory.name}</option>`;
                            });
                            $('#sub_category_id').html(html);
                        },
                        error: function() {
                            console.log('Error loading subcategories');
                        }
                    });
                }
            });
            
            // SubCategory Change - Load Child Categories
            $('#sub_category_id').on('change', function() {
                let subCategoryId = $(this).val();
                $('#child_category_id').html('<option value="">Select Child Category (Optional)</option>');
                
                if(subCategoryId) {
                    $.ajax({
                        url: "{{ route('admin.bookings.get-childcategories') }}",
                        method: 'GET',
                        data: { id: subCategoryId },
                        success: function(data) {
                            let html = '<option value="">Select Child Category (Optional)</option>';
                            data.forEach(function(childCategory) {
                                html += `<option value="${childCategory.id}">${childCategory.name}</option>`;
                            });
                            $('#child_category_id').html(html);
                        },
                        error: function() {
                            console.log('Error loading child categories');
                        }
                    });
                }
            });
            
            // Custom Fields
            let fieldCount = {{ $booking->custom_fields ? count($booking->custom_fields) : 0 }};
            $('#add-custom-field').on('click', function(){
                let html = `
                    <div class="row mb-2" id="custom-field-${fieldCount}">
                        <div class="col-5">
                            <input type="text" name="custom_fields[${fieldCount}][key]" class="form-control" placeholder="Field Name">
                        </div>
                        <div class="col-5">
                            <input type="text" name="custom_fields[${fieldCount}][value]" class="form-control" placeholder="Value">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-danger" onclick="$('#custom-field-${fieldCount}').remove()">X</button>
                        </div>
                    </div>
                `;
                $('#custom-fields-container').append(html);
                fieldCount++;
            });

            // Prevent form submit on barcode scan Enter
            $('#barcode').on('keypress', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endpush
