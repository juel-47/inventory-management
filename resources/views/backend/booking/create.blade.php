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
                                <div class="row">
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

                                <div class="row">
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

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Product Name (Read Only)</label>
                                        <input type="text" class="form-control" id="product_name" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Product Number (Read Only)</label>
                                        <input type="text" class="form-control" id="product_number" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Product Category (Read Only)</label>
                                        <input type="text" class="form-control" id="product_category" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Product Image</label>
                                        <br>
                                        <img id="product_image" src="" alt="Product Image" style="width: 100px; display: none;">
                                    </div>
                                </div>

                                <div class="row">
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

                                <div class="row">
                                     <div class="form-group col-md-3">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control" name="qty" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Unit Cost Price</label>
                                        <input type="number" class="form-control" name="unit_price" step="0.01">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Extra Cost</label>
                                        <input type="number" class="form-control" name="extra_cost" step="0.01">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Sale Price</label>
                                        <input type="number" class="form-control" name="sale_price" id="sale_price" step="0.01">
                                    </div>
                                </div>
                                
                                <div class="row">
                                     <div class="form-group col-md-6">
                                        <label>Min Inventory Qty</label>
                                        <input type="number" class="form-control" name="min_inventory_qty">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Min Sale Qty</label>
                                        <input type="number" class="form-control" name="min_sale_qty">
                                    </div>
                                        <div class="form-group col-md-6">
                                            <label>Min Purchase Price</label>
                                            <input type="number" class="form-control" name="min_purchase_price" step="0.01">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Status</label>
                                            <select name="status" class="form-control">
                                                <option value="pending">Pending</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control"></textarea>
                                </div>

                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Custom Fields</h4>
                                        <div class="card-header-action">
                                            <button type="button" class="btn btn-sm btn-success" id="add-custom-field">Add Field</button>
                                        </div>
                                    </div>
                                    <div class="card-body" id="custom-fields-container">
                                        <!-- Dynamic Fields -->
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Create Booking</button>
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

        $(document).ready(function() {
            $('#product_id').on('change', function() {
                let id = $(this).val();
                let product = products.find(p => p.id == id);
                
                if(product) {
                    $('#product_name').val(product.name);
                    $('#product_number').val(product.product_number);
                    $('#product_category').val(product.category ? product.category.name : '');
                    
                    // Auto-select Vendor
                    if(product.vendor_id) {
                         $('select[name="vendor_id"]').val(product.vendor_id).trigger('change');
                    }
                    
                    // Auto-select Unit
                     if(product.unit_id) {
                         $('select[name="unit_id"]').val(product.unit_id).trigger('change');
                    }

                    // Show Image
                    if(product.thumb_image) {
                        $('#product_image').attr('src', "{{ asset('storage/') }}" + "/" + product.thumb_image).show();
                    } else {
                        $('#product_image').hide();
                    }
                    
                    $('#sale_price').val(product.price);
                    $('#barcode').val(product.barcode);
                    
                    // Variants
                    let variantHtml = '<option value="">Select Variant (Optional)</option>';
                    if(product.variants && product.variants.length > 0) {
                        product.variants.forEach(v => {
                            variantHtml += `<option value='${JSON.stringify({name: v.name, id: v.id})}'>${v.name}</option>`;
                        });
                    }
                    $('#variant_select').html(variantHtml);
                    
                    // Auto-populate Category, SubCategory, ChildCategory
                    if(product.category_id) {
                        $('#category_id').val(product.category_id).trigger('change');
                        
                        // Load subcategories for this category
                        $.ajax({
                            url: "{{ route('admin.bookings.get-subcategories') }}",
                            method: 'GET',
                            data: { id: product.category_id },
                            success: function(data) {
                                let html = '<option value="">Select Sub Category (Optional)</option>';
                                data.forEach(function(subCategory) {
                                    let selected = product.sub_category_id == subCategory.id ? 'selected' : '';
                                    html += `<option value="${subCategory.id}" ${selected}>${subCategory.name}</option>`;
                                });
                                $('#sub_category_id').html(html);
                                
                                // If product has subcategory, load child categories
                                if(product.sub_category_id) {
                                    $.ajax({
                                        url: "{{ route('admin.bookings.get-childcategories') }}",
                                        method: 'GET',
                                        data: { id: product.sub_category_id },
                                        success: function(childData) {
                                            let childHtml = '<option value="">Select Child Category (Optional)</option>';
                                            childData.forEach(function(childCategory) {
                                                let selected = product.child_category_id == childCategory.id ? 'selected' : '';
                                                childHtml += `<option value="${childCategory.id}" ${selected}>${childCategory.name}</option>`;
                                            });
                                            $('#child_category_id').html(childHtml);
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        // Clear category dropdowns if product has no category
                        $('#category_id').val('').trigger('change');
                        $('#sub_category_id').html('<option value="">Select Sub Category (Optional)</option>');
                        $('#child_category_id').html('<option value="">Select Child Category (Optional)</option>');
                    }
                }
            });
            
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
            
            // Note: Category and Unit name might be missing if not eager loaded.
            
            // Custom Fields
            let fieldCount = 0;
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
