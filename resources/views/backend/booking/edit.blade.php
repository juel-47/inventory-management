@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Order Place</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Order Place ({{ $booking->booking_no }})</h4>
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
                                                    <option {{ $booking->product_id == $product->id ? 'selected' : '' }} value="{{ $product->id }}">{{ $product->name }}</option>
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
                                        
                                    <div class="row mb-4 px-3">
                                        <div class=" col-12">
                                            <div class="section-title mt-0">Variant, Unit & Qty</div>
                                        </div>
                                        <div class="col-12" id="variant_container" style="display: none;">
                                            <table class="table table-sm table-bordered">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="60%">Variant</th>
                                                        <th width="40%">Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="variant_table_body">
                                                    <!-- Variants will be injected here -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="font-weight-bold">Quantity</label>
                                            <input type="number" class="form-control" name="qty" id="qty_input" value="{{ $booking->qty }}" required>
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
                                        <div class="form-group col-md-4">
                                            <label>Barcode</label>
                                            <input type="text" class="form-control" name="barcode" id="barcode" value="{{ $booking->barcode }}">
                                        </div>
                                    </div>

                                    {{-- <div class="row"> --}}
                                        <div class="col-12">
                                            <div class="form-group ">
                                                <label>Notes</label>
                                                <textarea name="description" class="form-control" rows="4">{{ $booking->description }}</textarea>
                                            </div>
                                        </div>
                                    {{-- </div> --}}

                                    {{-- <div class="row"> --}}
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
                                    {{-- </div> --}}

                                    {{-- <div class="row"> --}}
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
                                    {{-- </div> --}}

                                    <div class="row mt-4 px-3">
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
            
            // Product Variant Population (Initial & Change)
            function populateVariants(product, savedVariantsMap = {}) {
                if(product.unit_id) {
                     $('#unit_select').val(product.unit_id);
                }
                
                let hasVariants = false;
                let tableHtml = '';
                
                if(product.variants && product.variants.length > 0) {
                     $('#variant_container').show();
                     
                      product.variants.forEach(v => {
                           let colorName = v.color ? v.color.name : '';
                           let sizeName = v.size ? v.size.name : '';
                           let name = (colorName + ' ' + sizeName).trim() || 'Default';
                           
                           // Backward compatibility check for old "Color: Name - Size: Name" format
                           let parts = [];
                           if(v.color) parts.push(`Color: ${v.color.name}`);
                           if(v.size) parts.push(`Size: ${v.size.name}`);
                           let oldName = parts.join(' - ');

                            if(name) {
                                hasVariants = true;
                                
                                let inputValue = '';
                                // Check map for this variant name (New Format first, then Old Format)
                                if(savedVariantsMap && (savedVariantsMap[name] !== undefined)) {
                                    inputValue = savedVariantsMap[name];
                                } else if(savedVariantsMap && (savedVariantsMap[oldName] !== undefined)) {
                                    inputValue = savedVariantsMap[oldName];
                                }
                                
                                let safeName = name.replace(/"/g, '&quot;');
                               
                               tableHtml += `
                                 <tr>
                                     <td class="align-middle">${name}</td>
                                     <td>
                                         <input type="number" class="form-control form-control-sm variant-qty" 
                                                name="variant_quantities[${safeName}]" 
                                                min="0" placeholder="0" value="${inputValue}">
                                     </td>
                                 </tr>
                               `;
                           }
                      });
                }
                
                if(hasVariants) {
                     $('#variant_table_body').html(tableHtml);
                     updateTotalQty(); 
                } else {
                     $('#variant_container').hide();
                     $('#variant_table_body').empty();
                }
            }
            
            function updateTotalQty() {
                let total = 0;
                $('.variant-qty').each(function() {
                    let val = parseInt($(this).val()) || 0;
                    total += val;
                });
                
                // Only update if total table qty is greater than current manual input
                // Or if manual input is empty
                let currentQty = parseInt($('#qty_input').val()) || 0;
                
                if(total > currentQty) {
                    $('#qty_input').val(total);
                }
                
                // Never readonly, user can add more "Unassigned" qty
                $('#qty_input').prop('readonly', false);
                $('#qty_input').attr('min', total); // Enforce min
            }
            
            // Listen for variant quantity changes
            $(document).on('keyup change', '.variant-qty', function() {
                updateTotalQty();
            });

            // Initial Load
            let initialProductId = $('#product_id').val();
            if(initialProductId) {
                let product = products.find(p => p.id == initialProductId);
                if(product) {
                    // Prepare Saved Variant Data map
                    let savedVariantsMap = {};
                    let rawVariantInfo = @json($booking->variant_info);
                    
                    if(rawVariantInfo) {
                        // Check if it's the old single-variant format (key 'variant' exists)
                        if(rawVariantInfo['variant']) {
                            savedVariantsMap[rawVariantInfo['variant']] = {{ $booking->qty }};
                        } else {
                            // New Aggregated Format (Key = name, Value = qty)
                            savedVariantsMap = rawVariantInfo;
                        }
                    }
                    populateVariants(product, savedVariantsMap);
                }
            }
            // Product Selection Change
            $('#product_id').on('change', function() {
                let productId = $(this).val();
                let product = products.find(p => p.id == productId);
                
                if (product) {
                    $('#product_name').val(product.name);
                    $('#product_number').val(product.product_number); 
                    $('#product_category').val(product.category ? product.category.name : '');
                    
                    if (product.thumb_image) {
                        $('#product_image').attr('src', "{{ asset('storage') }}/" + product.thumb_image).show();
                    } else {
                        $('#product_image').hide();
                    }

                    // Auto-select Category
                    if(product.category_id) {
                         $('#category_id').val(product.category_id).trigger('change');
                         
                         setTimeout(function(){
                              if(product.sub_category_id) {
                                  $('#sub_category_id').val(product.sub_category_id).trigger('change');
                                  
                                  setTimeout(function(){
                                      if(product.child_category_id) {
                                          $('#child_category_id').val(product.child_category_id);
                                      }
                                  }, 800);
                              }
                         }, 800);
                    }
                    
                    if(product.unit_id) {
                         $('#unit_select').val(product.unit_id);
                    }

                    populateVariants(product);

                } else {
                     $('#product_name').val('');
                     $('#product_number').val('');
                     $('#product_category').val('');
                     $('#product_image').hide();
                     populateVariants(null);
                }
            });


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
