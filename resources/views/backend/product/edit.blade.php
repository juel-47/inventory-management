@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Edit Product</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name" value="{{ $product->name }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>item number </label>
                                        <input type="text" class="form-control" name="product_number" value="{{ $product->product_number }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Category</label>
                                        <select class="form-control main-category select2" name="category_id">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option {{ $category->id == $product->category_id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Sub Category</label>
                                        <select class="form-control sub-category select2" name="sub_category_id">
                                            <option value="">Select Sub Category</option>
                                            @foreach ($subCategories ?? [] as $subCategory)
                                                <option {{ $subCategory->id == $product->sub_category_id ? 'selected' : '' }} value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Child Category</label>
                                        <select class="form-control child-category select2" name="child_category_id">
                                            <option value="">Select Child Category</option>
                                            @foreach ($childCategories ?? [] as $childCategory)
                                                <option {{ $childCategory->id == $product->child_category_id ? 'selected' : '' }} value="{{ $childCategory->id }}">{{ $childCategory->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Brand</label>
                                        <select class="form-control select2" name="brand_id">
                                            <option value="">Select Brand</option>
                                            @foreach ($brands as $brand)
                                                <option {{ $brand->id == $product->brand_id ? 'selected' : '' }} value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Unit</label>
                                        <select class="form-control select2" name="unit_id">
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $unit)
                                                <option {{ $unit->id == $product->unit_id ? 'selected' : '' }} value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                     <div class="form-group col-md-4">
                                         <label>Purchase Price</label>
                                         <input type="number" class="form-control" name="purchase_priceanytep="any" value="{{ $product->purchase_price }}">
                                     </div>
                                     <div class="form-group col-md-4">
                                         <label>Sale Price</label>
                                         <input type="number" class="form-control" name="priceanytep="any" value="{{ $product->price }}">
                                     </div>
                                    <div class="form-group col-md-4">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control" name="qty" value="{{ $product->qty }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Status</label>
                                        <select class="form-control" name="status">
                                            <option {{ $product->status == 1 ? 'selected' : '' }} value="1">Active</option>
                                            <option {{ $product->status == 0 ? 'selected' : '' }} value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Barcode (Optional)</label>
                                    <input type="text" class="form-control" name="barcode" id="barcode_input" placeholder="Scan or enter barcode" value="{{ $product->barcode }}">
                                    <small class="form-text text-muted">Compatible with barcode scanners. Scanners typically input the code and press Enter.</small>
                                </div>



                                <div class="form-group">
                                    <label>Long Description</label>
                                    <textarea name="long_description" class="summernote">{{ $product->long_description }}</textarea>
                                </div>

                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Product Variants</h4>
                                        <div class="card-header-action">
                                            <button type="button" class="btn btn-success" id="add-variant"><i class="fas fa-plus"></i> Add Variant</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="30%">Type</th>
                                                    <th width="40%">Variant</th>
                                                    <th>Qty</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="variant-list">
                                                @foreach ($product->variants as $index => $variant)
                                                    <tr id="variant-row-{{ $index }}">
                                                        <td>
                                                            <select class="form-control variant-type" data-row="{{ $index }}">
                                                                <option value="color" {{ $variant->color_id ? 'selected' : '' }}>Color</option>
                                                                <option value="size" {{ $variant->size_id ? 'selected' : '' }}>Size</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="color-select-wrapper-{{ $index }}" style="{{ $variant->color_id ? '' : 'display:none;' }}">
                                                                <select name="variants[{{ $index }}][color_id]" class="form-control">
                                                                    <option value="">Select Color</option>
                                                                    @foreach($colors as $color)
                                                                        <option value="{{ $color->id }}" {{ $variant->color_id == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="size-select-wrapper-{{ $index }}" style="{{ $variant->size_id ? '' : 'display:none;' }}">
                                                                <select name="variants[{{ $index }}][size_id]" class="form-control">
                                                                    <option value="">Select Size</option>
                                                                    @foreach($sizes as $size)
                                                                        <option value="{{ $size->id }}" {{ $variant->size_id == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td><input type="number" name="variants[{{ $index }}][qty]" class="form-control" value="{{ $variant->qty }}"></td>
                                                        <td><button type="button" class="btn btn-danger remove-variant" data-id="{{ $index }}"><i class="fas fa-trash"></i></button></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6 text-center">
                                    <label>Preview</label><br>
                                    <img src="{{ asset('storage/' . $product->thumb_image) }}" width="150px" alt="">
                                </div>
                                <div class="form-group col-md-6 center">
                                    <label>Thumbnail Image</label>
                                    <div id="image-preview" class="image-preview">
                                        <label for="image-upload" id="image-label">Choose File</label>
                                        <input type="file" name="image" id="image-upload" />
                                    </div>
                                </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Update Product</button>
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
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label",
                label_default: "Choose File",
                label_selected: "Change File",
                no_label: false,
                success_callback: null
            });

            // Get sub categories
            $('body').on('change', '.main-category', function(e) {
                let id = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "{{ route('admin.get-subCategories') }}",
                    data: {
                        id: id
                    },
                    success: function(data) {
                        $('.sub-category').html('<option value="">Select Sub Category</option>')
                        $('.child-category').html('<option value="">Select Child Category</option>')
                        $.each(data, function(i, item) {
                            $('.sub-category').append(`<option value="${item.id}">${item.name}</option>`)
                        })
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })

            // Get child categories
            $('body').on('change', '.sub-category', function(e) {
                let id = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "{{ route('admin.get-child-categories') }}",
                    data: {
                        id: id
                    },
                    success: function(data) {
                        $('.child-category').html('<option value="">Select Child Category</option>')
                        $.each(data, function(i, item) {
                            $('.child-category').append(`<option value="${item.id}">${item.name}</option>`)
                        })
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })

            // Variant Logic
            let variantCount = {{ count($product->variants) }};
            $('#add-variant').on('click', function(){
                let colorOptions = '<option value="">Select Color</option>';
                @foreach($colors as $color)
                    colorOptions += '<option value="{{ $color->id }}">{{ $color->name }}</option>';
                @endforeach

                let sizeOptions = '<option value="">Select Size</option>';
                @foreach($sizes as $size)
                    sizeOptions += '<option value="{{ $size->id }}">{{ $size->name }}</option>';
                @endforeach

                let html = `
                    <tr id="variant-row-${variantCount}">
                        <td>
                            <select class="form-control variant-type" data-row="${variantCount}">
                                <option value="color">Color</option>
                                <option value="size">Size</option>
                            </select>
                        </td>
                        <td>
                            <div class="color-select-wrapper-${variantCount}">
                                <select name="variants[${variantCount}][color_id]" class="form-control">
                                    ${colorOptions}
                                </select>
                            </div>
                            <div class="size-select-wrapper-${variantCount}" style="display:none;">
                                <select name="variants[${variantCount}][size_id]" class="form-control">
                                    ${sizeOptions}
                                </select>
                            </div>
                        </td>
                        <td><input type="number" name="variants[${variantCount}][qty]" class="form-control" value="0"></td>
                        <td><button type="button" class="btn btn-danger remove-variant" data-id="${variantCount}"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `;
                $('#variant-list').append(html);
                variantCount++;
            });

            $(document).on('change', '.variant-type', function() {
                let rowId = $(this).data('row');
                let type = $(this).val();
                
                if (type === 'color') {
                    $(`.color-select-wrapper-${rowId}`).show();
                    $(`.size-select-wrapper-${rowId}`).hide();
                    $(`.size-select-wrapper-${rowId} select`).val('');
                } else {
                    $(`.color-select-wrapper-${rowId}`).hide();
                    $(`.size-select-wrapper-${rowId}`).show();
                    $(`.color-select-wrapper-${rowId} select`).val('');
                }
            });

            $(document).on('click', '.remove-variant', function(){
                let id = $(this).data('id');
                $('#variant-row-'+id).remove();
            });

            // Prevent form submit on barcode scan Enter
            $('#barcode_input').on('keypress', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endpush
