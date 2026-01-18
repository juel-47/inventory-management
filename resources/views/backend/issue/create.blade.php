@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Create Stock Issue</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <form action="{{ route('admin.issues.store') }}" method="POST" id="issue_form">
                        @csrf
                        <input type="hidden" name="product_request_id" id="product_request_id_hidden">
                        
                        {{-- Hidden container for actual form inputs --}}
                        <div id="hidden-inputs-container"></div>

                        <div class="row">
                            <div class="col-md-9">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom-0 pb-0">
                                        <h4 class="text-primary"><i class="fas fa-box-open mr-2"></i>Issue Items</h4>
                                        <div class="d-flex align-items-center" style="gap: 15px;">
                                            <div style="width: 250px;">
                                                <select class="form-control select2" id="import_request_select" data-placeholder="Import from Request...">
                                                    <option value=""></option>
                                                    @foreach($productRequests as $pr)
                                                        <option value="{{ $pr->id }}">#{{ $pr->request_no }} - {{ $pr->user->name }} ({{ $pr->status }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-primary" id="add_row_btn">
                                                <i class="fas fa-plus"></i> Add Item
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table" id="issue_table">
                                                <thead>
                                                    <tr class="text-uppercase small text-muted font-weight-bold">
                                                        <th width="45%">Product Details</th>
                                                        <th width="15%" class="text-right">Price</th>
                                                        <th width="15%" class="text-center">Total Qty</th>
                                                        <th width="15%" class="text-right">Subtotal</th>
                                                        <th width="10%" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="issue_items_body">
                                                    <!-- Dynamic Rows -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="empty_state" class="text-center py-5 text-muted">
                                            <i class="fas fa-layer-group fa-3x mb-3 opacity-2"></i>
                                            <p>No items added yet. Add manually or import from a request.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <label class="font-weight-bold text-muted text-uppercase small">Note / Reference</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Enter reason for issue or reference number..." required></textarea>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                                    <div class="card-header bg-primary text-white">
                                        <h4 class="mb-0 text-white font-weight-600" style="font-size: 1.1rem;">Issue Summary</h4>
                                    </div>
                                    <div class="card-body bg-light">
                                        <div class="d-flex justify-content-between mb-3 px-1">
                                            <span class="text-muted">Issue Date:</span>
                                            <span class="font-weight-bold text-dark">{{ date('d M, Y') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3 border-top pt-3 px-1">
                                            <span class="text-muted text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">Product Types:</span>
                                            <span id="summary_total_items" class="font-weight-bold">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-4 px-1">
                                            <span class="text-muted text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">Total Quantity:</span>
                                            <span id="summary_total_qty" class="h5 mb-0 font-weight-bold text-primary">0</span>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm py-3 font-weight-bold" id="confirm_btn" disabled>
                                            <i class="fas fa-check-circle mr-2"></i> Confirm Issue
                                        </button>
                                        <p class="text-center text-muted small mt-3 mb-0">Confirming will deduct stock and generate a ledger entry.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const products = @json($products);
        const requestIdParam = @json($requestId ?? null);
        let rowCount = 0;

        $(document).ready(function() {
            $('.select2').select2({ width: '100%', dropdownAutoWidth: true });

            // Initialize with one row ONLY if not importing
            if (requestIdParam) {
                $('#import_request_select').val(requestIdParam).trigger('change');
            } else {
                addRow();
            }

            $('#add_row_btn').on('click', function() { addRow(); });
            
            $(document).on('click', '.remove_row', function() {
                const id = $(this).data('id');
                $(`#row-${id}`).fadeOut(200, function() {
                    $(this).remove();
                    updateGlobalSummary();
                });
            });

            // Import from Product Request
            $('#import_request_select').on('change', function() {
                const requestId = $(this).val();
                if (!requestId) return;

                Swal.fire({
                    title: 'Importing Request...',
                    text: 'Bringing items into the issue form.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "{{ route('admin.issues.get-request-items') }}",
                    method: "GET",
                    data: { request_id: requestId },
                    success: function(items) {
                        Swal.close();
                        $('#issue_items_body').empty();
                        $('#product_request_id_hidden').val(requestId);
                        rowCount = 0;

                        // Group by product so we can use the bulk entry UI
                        const grouped = {};
                        items.forEach(item => {
                            if (!grouped[item.product_id]) grouped[item.product_id] = [];
                            grouped[item.product_id].push(item);
                        });

                        Object.keys(grouped).forEach(pid => {
                            addRow(grouped[pid]);
                        });

                        $('#import_request_select').val(null).trigger('change.select2');
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to fetch request items.', 'error');
                    }
                });
            });

            $(document).on('change', '.product_selector', function() {
                const productId = $(this).val();
                const row = $(this).closest('tr');
                const product = products.find(p => p.id == productId);
                const variantArea = row.find('.variant-entry-area');
                const variantList = row.find('.variant-list');
                const imageTag = row.find('.product-thumb');
                const noImagePlaceholder = row.find('.no-image-placeholder');
                
                variantList.empty();
                
                if (product) {
                    row.find('.unit-price-display').text(parseFloat(product.price).toFixed(2));

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
                            const stock = v.inventory_stocks.find(s => s.outlet_id == 1)?.quantity || 0;
                            const colorName = v.color ? v.color.name : '';
                            const sizeName = v.size ? v.size.name : '';
                            const vName = v.name || `${colorName} ${sizeName}`.trim() || 'No Name';

                            variantList.append(`
                                <div class="variant-item bg-white p-2 border rounded text-center shadow-sm" style="min-width: 100px;">
                                    <div class="small font-weight-bold text-dark mb-1">${vName}</div>
                                    <div class="text-muted small mb-2">Stock: <span class="v-stock">${stock}</span></div>
                                    <input type="number" class="form-control form-control-sm variant-qty-input text-center mx-auto" 
                                           style="width: 70px; height: 32px;"
                                           data-product-id="${product.id}" 
                                           data-variant-id="${v.id}" 
                                           data-max="${stock}" 
                                           min="0" max="${stock}" value="0">
                                </div>
                            `);
                        });
                        variantArea.fadeIn();
                    } else {
                        const stock = product.inventory_stocks.find(s => s.outlet_id == 1)?.quantity || 0;
                        variantList.append(`
                            <div class="variant-item bg-white p-2 border rounded text-center shadow-sm" style="min-width: 120px;">
                                <div class="small font-weight-bold text-dark mb-1">Standard</div>
                                <div class="text-muted small mb-2">Stock: <span class="v-stock">${stock}</span></div>
                                <input type="number" class="form-control form-control-sm variant-qty-input text-center mx-auto" 
                                       style="width: 70px; height: 32px;"
                                       data-product-id="${product.id}" 
                                       data-variant-id="" 
                                       data-max="${stock}" 
                                       min="0" max="${stock}" value="0">
                            </div>
                        `);
                        variantArea.fadeIn();
                    }
                } else {
                    variantArea.fadeOut();
                    imageTag.hide();
                    noImagePlaceholder.show();
                    row.find('.unit-price-display').text('0.00');
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
                
                const row = $(this).closest('.issue-row');
                calculateRowTotals(row);
            });

            $('#issue_form').on('submit', function() {
                reindexFormInputs();
                return true;
            });
        });

        function addRow(preDataItems = null) {
            $('#empty_state').hide();
            
            let options = '<option value=""></option>';
            products.forEach(p => {
                const selected = preDataItems && preDataItems[0].product_id == p.id ? 'selected' : '';
                options += `<option value="${p.id}" ${selected}>${p.name}</option>`;
            });

            let html = `
                <tr id="row-${rowCount}" class="issue-row border-bottom">
                    <td class="p-4" style="vertical-align: top;">
                        <div class="d-flex align-items-start mb-3">
                            <div class="product-image-container mr-3" style="width: 60px; height: 60px; border: 1px solid #e4e6fc; border-radius: 6px; overflow: hidden; background: #fbfbfb; flex-shrink: 0;">
                                <img src="" class="product-thumb w-100 h-100" style="object-fit: cover; display: none;">
                                <div class="no-image-placeholder h-100 d-flex align-items-center justify-content-center text-muted small">
                                    <i class="fas fa-image"></i>
                                </div>
                            </div>
                            <div class="form-group mb-0 flex-grow-1">
                                <select class="form-control select2 product_selector" data-placeholder="Choose Product">
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
                        <div class="text-muted small mb-1 text-uppercase font-weight-bold" style="font-size: 10px;">Price</div>
                        <div class="unit-price-display font-weight-bold text-dark">0.00</div>
                    </td>
                    <td class="text-center align-middle px-4">
                        <div class="text-muted small mb-1 text-uppercase font-weight-bold" style="font-size: 10px;">Total Qty</div>
                        <div class="row-qty-text font-weight-bold h6 mb-0">0</div>
                    </td>
                    <td class="text-right align-middle px-4">
                        <div class="text-muted small mb-1 text-uppercase font-weight-bold" style="font-size: 10px;">Subtotal</div>
                        <div class="row-total-text font-weight-bold h6 mb-0 text-primary">0.00</div>
                    </td>
                    <td class="text-center align-middle pr-4">
                        <button type="button" class="btn btn-light btn-sm text-danger remove_row" data-id="${rowCount}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#issue_items_body').append(html);
            const newRow = $(`#row-${rowCount}`);
            newRow.find('.select2').select2({ width: '100%', dropdownAutoWidth: true });
            
            // If we have pre-data (from import), trigger the population
            if (preDataItems) {
                const product = products.find(p => p.id == preDataItems[0].product_id);
                if (product) {
                    newRow.find('.product_selector').trigger('change');
                    // Now fill specific variant quantities
                    preDataItems.forEach(item => {
                        const vInput = newRow.find(`.variant-qty-input[data-variant-id="${item.variant_id || ''}"]`);
                        if (vInput.length) {
                             vInput.val(item.requested_qty).trigger('input');
                        }
                    });
                }
            }

            rowCount++;
            updateGlobalSummary();
        }

        function calculateRowTotals(row) {
            let totalQty = 0;
            row.find('.variant-qty-input').each(function() {
                totalQty += parseInt($(this).val()) || 0;
            });
            
            const price = parseFloat(row.find('.unit-price-display').text()) || 0;
            const totalAmount = totalQty * price;
            
            row.find('.row-qty-text').text(totalQty);
            row.find('.row-total-text').text(totalAmount.toFixed(2));
            
            updateGlobalSummary();
        }

        function updateGlobalSummary() {
            let grandTotalQty = 0;
            let productTypes = 0;
            let hasInvalid = false;
            let hasPositiveQty = false;

            $('.issue-row').each(function() {
                const qty = parseInt($(this).find('.row-qty-text').text()) || 0;
                if (qty > 0) {
                    grandTotalQty += qty;
                    productTypes++;
                    hasPositiveQty = true;
                }

                $(this).find('.variant-qty-input').each(function() {
                    const val = parseInt($(this).val()) || 0;
                    const max = parseInt($(this).data('max')) || 0;
                    if (val > max) hasInvalid = true;
                });
            });

            $('#summary_total_items').text(productTypes);
            $('#summary_total_qty').text(grandTotalQty);
            
            $('#confirm_btn').prop('disabled', hasInvalid || !hasPositiveQty);
            
            if ($('.issue-row').length === 0) $('#empty_state').show();
            else $('#empty_state').hide();
        }

        function reindexFormInputs() {
            $('#hidden-inputs-container').empty();
            let index = 0;
            
            $('.variant-qty-input').each(function() {
                const qty = parseInt($(this).val()) || 0;
                if (qty > 0) {
                    const pid = $(this).data('product-id');
                    const vid = $(this).data('variant-id');
                    
                    $('#hidden-inputs-container').append(`
                        <input type="hidden" name="items[${index}][product_id]" value="${pid}">
                        <input type="hidden" name="items[${index}][variant_id]" value="${vid || ''}">
                        <input type="hidden" name="items[${index}][quantity]" value="${qty}">
                    `);
                    index++;
                }
            });
        }
    </script>
@endpush


