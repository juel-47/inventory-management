@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product Request</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Create Product Request</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.product-requests.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h4 class="mb-0 text-primary"><i class="fas fa-shopping-basket mr-2"></i>Select Products</h4>
                                                <div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill mr-2" id="clear-all-items">
                                                        <i class="fas fa-trash-alt mr-1"></i> Clear All
                                                    </button>
                                                    <button type="button" class="btn btn-primary btn-sm rounded-pill" id="add-item">
                                                        <i class="fas fa-plus mr-1"></i> Add Product
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0" id="items-table">
                                                        <thead class="bg-whitesmoke text-uppercase small font-weight-bold">
                                                            <tr>
                                                                <th width="50%">Product Details</th>
                                                                 <th width="15%" class="text-right">Buying Price</th>
                                                                 <th width="10%" class="text-center">Selling Price</th>
                                                                 <th width="10%" class="text-center">Total Qty</th>
                                                                 <th width="20%" class="text-right">Local Total Price</th>
                                                                <th width="5%"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="items-container">
                                                            <!-- Dynamic Rows -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-md-3">
                                        <div class="card border shadow-sm sticky-top" style="top: 80px;">
                                            <div class="card-header bg-primary text-white">
                                                <h4 class="mb-0 text-white">Summary</h4>
                                            </div>
                                            <div class="card-body bg-light">
                                                @if(Auth::user()->can('Manage Product Requests'))
                                                    <div class="form-group mb-4">
                                                        <label class="font-weight-bold text-dark">Select Outlet / User</label>
                                                        <select name="user_id" class="form-control select2" required>
                                                            <option value="" disabled selected>Choose Outlet/User...</option>
                                                            @foreach($users as $u)
                                                                <option value="{{ $u->id }}">{{ $u->outlet_name ?? $u->name }} ({{ $u->name }})</option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">Select which outlet this request is for.</small>
                                                    </div>
                                                    <hr>
                                                @endif
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Total Products:</span>
                                                    <span id="total-items-count" class="font-weight-bold">0</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                    <span class="text-muted">Grand Total Qty:</span>
                                                    <span id="total-qty-display" class="font-weight-bold">0</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0 text-dark">Grand Total:</h5>
                                                     <h4 class="mb-0 text-primary">{{ $settings->base_currency_icon }}<span id="grand-total-display">0.00</span></h4>
                                                </div>
                                                <hr>
                                                <div class="form-group">
                                                    <label class="font-weight-bold text-dark">Required Days <span class="text-muted">(Optional)</span></label>
                                                    <input type="number" name="required_days" class="form-control" min="1" placeholder="e.g., 5">
                                                    <small class="form-text text-muted">How many days until you need these products?</small>
                                                </div>
                                                <div class="form-group">
                                                    <label class="font-weight-bold text-dark">Note <span class="text-muted">(Optional)</span></label>
                                                    <textarea name="note" class="form-control" rows="3" placeholder="Add any special instructions..."></textarea>
                                                </div>
                                                <hr>
                                                <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm" id="submit-btn" disabled>
                                                    <i class="fas fa-paper-plane mr-2"></i> Submit Request
                                                </button>
                                            </div>
                                        </div>
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
        $(document).ready(function() {
            let rowCounter = 0;
            const currencyIcon = "{{ $settings->base_currency_icon }}";
            const products = @json($products);

            // Start with pre-selected rows or one empty row
            const selectedIds = @json($selectedIds ?? []);
            
            if (selectedIds && selectedIds.length > 0) {
                selectedIds.forEach(id => {
                    addProductRow(id);
                });
            } else {
                addProductRow();
            }

            $('#add-item').on('click', function() { addProductRow(); });

            $('#clear-all-items').on('click', function() {
                Swal.fire({
                    title: 'Clear all items?',
                    text: 'Are you sure you want to remove all products from this request?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, clear all!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#items-container').empty();
                        addProductRow();
                        updateGlobalSummary();
                        toastr.info('All items removed');
                    }
                });
            });

            function addProductRow(productId = null) {
                let options = '<option value=""></option>';
                products.forEach(p => {
                    options += `<option value="${p.id}">${p.name}</option>`;
                });

                let html = `
                    <tr id="row-${rowCounter}" class="product-row border-bottom">
                        <td class="p-4" style="vertical-align: top;">
                            <div class="d-flex align-items-start mb-3">
                                <div class="product-image-container mr-3" style="width: 60px; height: 60px; border: 1px solid #e4e6fc; border-radius: 6px; overflow: hidden; background: #fbfbfb; flex-shrink: 0;">
                                    <img src="" class="product-thumb w-100 h-100" style="object-fit: cover; display: none;">
                                    <div class="no-image-placeholder h-100 d-flex align-items-center justify-content-center text-muted small">
                                        <i class="fas fa-image"></i>
                                    </div>
                                </div>
                                <div class="form-group mb-0 flex-grow-1">
                                    <select class="form-control select2 product-selector" data-placeholder="Choose Product">
                                        ${options}
                                    </select>
                                </div>
                            </div>
                            <div class="variant-entry-area mt-4" style="display:none;">
                                <div class="variant-list d-flex flex-wrap" style="gap: 15px;">
                                    <!-- Variants will be injected here -->
                                </div>
                            </div>
                        </td>
                        <td class="text-right align-middle px-4">
                            <div class="outlet-price-display font-weight-bold">0.00</div>
                        </td>
                        <td class="text-right align-middle px-4">
                            <div class="sell-price-display font-weight-bold">0.00</div>
                        </td>
                        <td class="text-center align-middle px-4">
                            <div class="row-qty-text font-weight-bold h6 mb-0">0</div>
                        </td>
                        <td class="text-right align-middle px-4">
                            <div class="row-total-text font-weight-bold h6 mb-0 text-primary">0.00</div>
                        </td>
                        <td class="text-center align-middle pr-4">
                            <button type="button" class="btn btn-light btn-sm text-danger remove-row" data-id="${rowCounter}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                $('#items-container').append(html);
                const newRow = $(`#row-${rowCounter}`);
                const selector = newRow.find('.product-selector');
                
                selector.select2({ width: '100%', dropdownAutoWidth: true });
                
                if (productId) {
                    selector.val(productId);
                    // Triggering change after a tiny timeout to ensure select2 is completely ready
                    setTimeout(() => {
                        selector.trigger('change');
                    }, 50);
                }

                rowCounter++;
                updateGlobalSummary();
            }

            // Clear basket on form submit
            $('form').on('submit', function() {
                localStorage.removeItem('request_basket');
            });

            $(document).on('click', '.remove-row', function() {
                const id = $(this).data('id');
                $(`#row-${id}`).fadeOut(200, function() {
                    $(this).remove();
                    reindexFormInputs();
                    updateGlobalSummary();
                });
            });

            $(document).on('change', '.product-selector', function() {
                const productId = $(this).val();
                const row = $(this).closest('tr');
                const product = products.find(p => p.id == productId);
                const variantArea = row.find('.variant-entry-area');
                const variantList = row.find('.variant-list');
                const imageTag = row.find('.product-thumb');
                const noImagePlaceholder = row.find('.no-image-placeholder');
                
                variantList.empty();
                
                if (product) {
                    let oPrice = parseFloat(product.outlet_price);
                    let sPrice = parseFloat(product.price);
                    
                    let outletPrice = (isNaN(oPrice) || oPrice <= 0) ? (isNaN(sPrice) ? 0 : sPrice) : oPrice;
                    let sellPrice = isNaN(sPrice) ? 0 : sPrice;

                    row.find('.outlet-price-display').text(outletPrice.toFixed(2));
                    row.find('.sell-price-display').text(sellPrice.toFixed(2));

                    // Update Image
                    if (product.thumb_image) {
                        imageTag.attr('src', `/storage/${product.thumb_image}`).show();
                        noImagePlaceholder.hide();
                    } else {
                        imageTag.hide();
                        noImagePlaceholder.show();
                    }

                    if (product.variants && product.variants.length > 0) {
                        product.variants.forEach(v => {
                            const stocks = v.inventory_stocks || v.inventory_stock || [];
                            const stockObj = Array.isArray(stocks) ? stocks.find(s => s.outlet_id == 1) : null;
                            const stock = stockObj ? (stockObj.quantity || 0) : 0;
                            
                            variantList.append(`
                                <div class="variant-item bg-white p-2 border rounded text-center shadow-sm" style="min-width: 100px;">
                                    <div class="small font-weight-bold text-dark mb-1">${v.name}</div>
                                    <div class="text-muted small mb-2">Stock: ${stock}</div>
                                    <input type="number" class="form-control form-control-sm variant-qty-input text-center mx-auto" 
                                           style="width: 70px; height: 32px;"
                                           data-product-id="${product.id}" 
                                           data-variant-id="${v.id}" 
                                           data-max="${stock}" 
                                           min="0" max="${stock}" value="0">
                                </div>
                            `);
                        });
                        variantArea.show();
                    } else {
                        const stocks = product.inventory_stocks || product.inventory_stock || [];
                        const stockObj = Array.isArray(stocks) ? stocks.find(s => s.outlet_id == 1) : null;
                        const stock = stockObj ? (stockObj.quantity || 0) : 0;

                        variantList.append(`
                            <div class="variant-item bg-white p-2 border rounded text-center shadow-sm" style="min-width: 120px;">
                                <div class="small font-weight-bold text-dark mb-1">Standard</div>
                                <div class="text-muted small mb-2">Stock: ${stock}</div>
                                <input type="number" class="form-control form-control-sm variant-qty-input text-center mx-auto" 
                                       style="width: 70px; height: 32px;"
                                       data-product-id="${product.id}" 
                                       data-variant-id="" 
                                       data-max="${stock}" 
                                       min="0" max="${stock}" value="0">
                            </div>
                        `);
                        variantArea.show();
                    }
                } else {
                    row.find('.outlet-price-display').text('0.00');
                    row.find('.sell-price-display').text('0.00');
                    variantArea.hide();
                }
                calculateRowTotals(row);
            });

            $(document).on('input', '.variant-qty-input', function() {
                const val = parseInt($(this).val()) || 0;
                const max = parseInt($(this).data('max')) || 0;
                
                if (val > max) {
                    $(this).addClass('border-danger text-danger');
                } else {
                    $(this).removeClass('border-danger text-danger');
                }
                
                const row = $(this).closest('.product-row');
                calculateRowTotals(row);
            });

            function calculateRowTotals(row) {
                let totalQty = 0;
                row.find('.variant-qty-input').each(function() {
                    totalQty += parseInt($(this).val()) || 0;
                });
                
                const price = parseFloat(row.find('.outlet-price-display').text()) || 0;
                const totalAmount = totalQty * price;
                
                row.find('.row-qty-text').text(totalQty);
                row.find('.row-total-text').text(totalAmount.toFixed(2));
                
                updateGlobalSummary();
            }

            function updateGlobalSummary() {
                let grandTotal = 0;
                let grandTotalQty = 0;
                let hasInvalid = false;
                let hasItems = false;

                $('.product-row').each(function() {
                    let qty = parseInt($(this).find('.row-qty-text').text()) || 0;
                    let total = parseFloat($(this).find('.row-total-text').text()) || 0;
                    
                    if (qty > 0) hasItems = true;
                    
                    $(this).find('.variant-qty-input').each(function() {
                        if (parseInt($(this).val()) > parseInt($(this).data('max'))) hasInvalid = true;
                    });

                    grandTotalQty += qty;
                    grandTotal += total;
                });

                $('#total-items-count').text($('.product-row').length);
                $('#total-qty-display').text(grandTotalQty);
                $('#grand-total-display').text(grandTotal.toFixed(2));
                
                $('#submit-btn').prop('disabled', hasInvalid || !hasItems);
                
                // Regenerate hidden inputs for submission
                reindexFormInputs();
            }

            function reindexFormInputs() {
                // Remove all previous hidden inputs
                $('#hidden-inputs-container').remove();
                
                const container = $('<div id="hidden-inputs-container"></div>');
                let index = 0;
                
                $('.variant-qty-input').each(function() {
                    const qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        const pid = $(this).data('product-id');
                        const vid = $(this).data('variant-id');
                        
                        container.append(`<input type="hidden" name="items[${index}][product_id]" value="${pid}">`);
                        if (vid) container.append(`<input type="hidden" name="items[${index}][variant_id]" value="${vid}">`);
                        container.append(`<input type="hidden" name="items[${index}][qty]" value="${qty}">`);
                        index++;
                    }
                });
                
                $('form').append(container);
            }
        });
    </script>
@endpush
