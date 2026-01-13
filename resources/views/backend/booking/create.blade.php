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
                            <h4>Create Booking</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.bookings.store') }}" method="POST">
                                @csrf
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="section-title mt-0">General Information</div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Vendor</label>
                                        <select class="form-control select2" name="vendor_id">
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Product</label>
                                        <select class="form-control select2" name="product_id" id="product_id">
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="section-title mt-0">Categorization (Optional Override)</div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Category</label>
                                        <select class="form-control select2" name="category_id" id="category_id">
                                            <option value="">Select Category (Optional)</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Sub Category</label>
                                        <select class="form-control select2" name="sub_category_id" id="sub_category_id">
                                            <option value="">Select Sub Category (Optional)</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Child Category</label>
                                        <select class="form-control select2" name="child_category_id" id="child_category_id">
                                            <option value="">Select Child Category (Optional)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="section-title mt-0">Product Details (Read Only)</div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Product Name</label>
                                        <input type="text" class="form-control bg-light" id="product_name" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Product Number</label>
                                        <input type="text" class="form-control bg-light" id="product_number" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Product Category</label>
                                        <input type="text" class="form-control bg-light" id="product_category" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Product Image</label>
                                        <br>
                                        <img id="product_image" src="" alt="Product Image" style="width: 100px; display: none;">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
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
                                        <input type="number" class="form-control" name="qty" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Unit</label>
                                        <select class="form-control" name="unit_id" id="unit_select">
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Barcode</label>
                                        <input type="text" class="form-control" name="barcode" id="barcode">
                                    </div>
                                    
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea name="description" class="form-control" rows="4"></textarea>
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
                                                <!-- Custom fields will participate here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" name="status">
                                                <option value="pending">Pending</option>
                                                <option value="complete">Complete</option>
                                                <option value="cancelled">Cancelled</option>
                                                <option value="missing">Missing</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-right">
                                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Create Booking</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const products = @json($products);
        let currentVendorRate = 1;
        let currentVendorIcon = "{{ $settings->currency_icon }}";
        let currentVendorName = "{{ $settings->currency_name }}";

        $(document).ready(function() {
            
            // Product Selection Logic
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
                    if(!$('#category_id').val() && product.category_id) {
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
                    
                    if(product.unit_id) {
                         $('#unit_select').val(product.unit_id);
                    }
                    
                    if(product.unit_id) {
                         $('#unit_select').val(product.unit_id);
                    }
                    
                    // Populate Variants (Simple Table)
                    let hasVariants = false;
                    let tableHtml = '';
                    
                    if(product.variants && product.variants.length > 0) {
                         product.variants.forEach(v => {
                             let colorName = v.color ? v.color.name : '';
                             let sizeName = v.size ? v.size.name : '';
                             let name = (colorName + ' ' + sizeName).trim() || 'Default';
                             
                             if(name) {
                                 hasVariants = true;
                                 let safeName = name.replace(/"/g, '&quot;');
                                 
                                 tableHtml += `
                                    <tr>
                                        <td class="align-middle">${name}</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm variant-qty" name="variant_quantities[${safeName}]" min="0" placeholder="0">
                                        </td>
                                    </tr>
                                 `;
                             }
                         });
                    }
                    
                    if(hasVariants) {
                        $('#variant_table_body').html(tableHtml);
                        $('#variant_container').show();
                        // unlock main qty, but keep auto-calc
                    } else {
                        $('#variant_container').hide();
                        $('#variant_table_body').empty();
                    }

                } else {
                     $('#product_name').val('');
                     $('#product_number').val('');
                     $('#product_category').val('');
                     $('#product_image').hide();
                     $('#variant_container').hide();
                     $('#variant_table_body').empty();
                }
            });

            // Listen for variant quantity changes
            $(document).on('keyup change', '.variant-qty', function() {
                let total = 0;
                $('.variant-qty').each(function() {
                    let val = parseInt($(this).val()) || 0;
                    total += val;
                });
                
                let currentQty = parseInt($('input[name="qty"]').val()) || 0;

                if(total > 0) {
                    // Only increase, don't decrease if user manually typed more
                    if(total > currentQty) {
                         $('input[name="qty"]').val(total);
                    }
                    $('input[name="qty"]').attr('min', total);
                } else {
                    $('input[name="qty"]').removeAttr('min');
                }
            });


            // Vendor Currency Logic
            $('select[name="vendor_id"]').on('change', function() {
                let vendorId = $(this).val();
                if (vendorId) {
                    $.ajax({
                        url: "{{ route('admin.vendor.get-details') }}",
                        method: 'GET',
                        data: { id: vendorId },
                        success: function(data) {
                            currentVendorRate = data.currency_rate;
                            currentVendorIcon = data.currency_icon;
                            currentVendorName = data.currency_name;
                            recalculateAllPrices();
                        }
                    });
                } else {
                    currentVendorRate = 1;
                    currentVendorIcon = '{{ $settings->currency_icon }}';
                    currentVendorName = '{{ $settings->currency_name }}';
                    recalculateAllPrices();
                }
            });

            $('input[name="qty"]').on('keyup change', function() {
                // Pricing recalculation removed
            });

            function recalculateAllPrices() {
                // Pricing recalculation removed
            }
            
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
            let fieldCount = 0;
            $('#add-custom-field').on('click', function(){
                let html = `
                    <div class="row mb-2" id="custom-field-${fieldCount}">
                        <div class="col-md-5">
                            <input type="text" name="custom_fields[${fieldCount}][key]" class="form-control" placeholder="Field Name">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="custom_fields[${fieldCount}][value]" class="form-control" placeholder="Value">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-icon" onclick="$('#custom-field-${fieldCount}').remove()"><i class="fas fa-trash"></i></button>
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
