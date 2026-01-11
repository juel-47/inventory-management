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
                                                <option value="{{ $product->id }}">{{ $product->name }} (sku: {{ $product->sku }})</option>
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
                                        <div class="section-title mt-0">Variant & Unit</div>
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
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Barcode</label>
                                        <input type="text" class="form-control" name="barcode" id="barcode">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="section-title mt-0 text-primary">Pricing & Costing</div>
                                        <hr>
                                    </div>
                                      <div class="form-group col-md-3">
                                         <label class="font-weight-bold">Quantity</label>
                                         <input type="number" class="form-control form-control-lg" name="qty" required>
                                     </div>
                                       <div class="form-group col-md-3">
                                          <label>Price (Vendor Currency)</label>
                                          <input type="number" class="form-control" name="unit_price" id="unit_price" step="0.01">
                                          <small class="form-text text-muted">Enter price in Vendor's currency</small>
                                      </div>
                                       <div class="form-group col-md-3">
                                          <label>Extra Cost (Vendor)</label>
                                          <input type="number" class="form-control" name="extra_cost" id="extra_cost" step="0.01">
                                      </div>
                                       <div class="form-group col-md-3">
                                           <label>Selling Price</label>
                                           <input type="number" class="form-control" name="sale_price" id="sale_price" step="0.01">
                                       </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="row p-3 bg-light rounded">
                                            <div class="form-group col-md-3 mb-0">
                                                <label>System Unit Price</label>
                                                <input type="text" class="form-control border-0 bg-transparent font-weight-bold p-0" id="unit_price_vendor" readonly>
                                            </div>
                                            <div class="form-group col-md-3 mb-0">
                                                <label>Vendor Total</label>
                                                <input type="text" class="form-control border-0 bg-transparent font-weight-bold p-0 text-primary" id="total_cost_vendor" style="font-size: 1.2em;" readonly>
                                            </div>
                                            <div class="form-group col-md-3 mb-0">
                                                <label>System Total</label>
                                                <input type="number" class="form-control border-0 bg-transparent font-weight-bold p-0" name="total_cost" id="total_cost" step="0.01" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                     <div class="form-group col-md-4">
                                        <label>Min Inventory Qty</label>
                                        <input type="number" class="form-control" name="min_inventory_qty">
                                    </div>
                                     <div class="form-group col-md-4">
                                        <label>Min Sale Qty</label>
                                        <input type="number" class="form-control" name="min_sale_qty">
                                    </div>
                                     <div class="form-group col-md-4">
                                        <label>Min Purchase Price</label>
                                        <input type="number" class="form-control" name="min_purchase_price" step="0.01">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Description</label>
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
                                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Booking</button>
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
                    $('#product_number').val(product.sku); // Assuming SKU as number
                    $('#product_category').val(product.category ? product.category.name : '');
                    
                    if (product.thumb_image) {
                        $('#product_image').attr('src', "{{ asset('storage') }}/" + product.thumb_image).show();
                    } else {
                        $('#product_image').hide();
                    }

                    // Auto-select Category/Sub/Child if not manually set (Optional Override logic)
                    // If user hasn't touched the selects? 
                    // Let's just set the selects to the product's defaults if they are empty
                    if(!$('#category_id').val()) {
                        $('#category_id').val(product.category_id).trigger('change');
                        // Wait for ajax or just set if we had the data? 
                        // Since subcategories are loaded via ajax, we might need a timeout or chain
                    }
                    
                    if(product.unit_id) {
                         $('#unit_select').val(product.unit_id);
                    }
                    
                    // Variants
                    let variantHtml = '<option value="">Select Variant (Optional)</option>';
                    // Assuming product.variants is array/json?
                    // Controller passes: with(['variants'...])
                    if(product.variants && product.variants.length > 0) {
                         product.variants.forEach(v => {
                             variantHtml += `<option value="${v.id}">${v.name}</option>`;
                         });
                    }
                    $('#variant_select').html(variantHtml);
                } else {
                     $('#product_name').val('');
                     $('#product_number').val('');
                     $('#product_category').val('');
                     $('#product_image').hide();
                     $('#variant_select').html('<option value="">Select Variant (Optional)</option>');
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

            $('input[name="qty"], #unit_price, #extra_cost').on('keyup change', function() {
                recalculateAllPrices();
            });

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

                $('#total_cost').val(systemTotal.toFixed(2)); // System Total
                $('#unit_price_vendor').val('{{ $settings->currency_icon }}' + systemPrice.toFixed(2)); // System Unit Price
                
                // Vendor Display
                $('#total_cost_vendor').val(currentVendorIcon + vendorTotal.toFixed(2));
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
