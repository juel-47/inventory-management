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
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill" id="add-item">
                                                    <i class="fas fa-plus mr-1"></i> Add Product
                                                </button>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0" id="items-table">
                                                        <thead class="bg-whitesmoke text-uppercase small font-weight-bold">
                                                            <tr>
                                                                <th width="40%">Product Description</th>
                                                                <th width="15%" class="text-center">Stock</th>
                                                                 <th width="15%" class="text-right">Base Unit Price</th>
                                                                 <th width="15%" class="text-center">Quantity</th>
                                                                 <th width="15%" class="text-right">Base Total</th>
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

                                        <div class="form-group mt-3">
                                            <label class="font-weight-bold text-muted">Additional Notes</label>
                                            <textarea name="note" class="form-control" rows="3" placeholder="Enter any specific requirements or notes here..."></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="card border shadow-sm sticky-top" style="top: 80px;">
                                            <div class="card-header bg-primary text-white">
                                                <h4 class="mb-0 text-white">Request Summary</h4>
                                            </div>
                                            <div class="card-body bg-light">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Total Items:</span>
                                                    <span id="total-items-count" class="font-weight-bold">0</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                    <span class="text-muted">Total Quantity:</span>
                                                    <span id="total-qty-display" class="font-weight-bold">0</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0 text-dark">Grand Total:</h5>
                                                     <h4 class="mb-0 text-primary">{{ $settings->base_currency_icon }}<span id="grand-total-display">0.00</span></h4>
                                                </div>
                                                <hr>
                                                <p class="small text-muted mb-4">
                                                    <i class="fas fa-info-circle mr-1"></i> Prices are based on current company rates.
                                                </p>
                                                <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm">
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
            let itemIndex = 0;

            function addRow() {
                let html = `
                    <tr id="row-${itemIndex}" class="item-row">
                        <td>
                            <select name="items[${itemIndex}][product_id]" class="form-control select2 product-select" data-placeholder="Choose a product" required>
                                <option value=""></option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-qty="{{ $product->qty }}"
                                            data-price="{{ $product->price }}">
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-light stock-badge">0</span>
                        </td>
                        <td class="text-right align-middle">
                            <span class="text-muted unit-price-display">0.00</span>
                        </td>
                        <td>
                            <input type="number" name="items[${itemIndex}][qty]" class="form-control text-center qty-input" min="1" placeholder="0" required>
                        </td>
                        <td class="text-right align-middle font-weight-bold">
                            <span class="subtotal-text text-dark">0.00</span>
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row border-0" data-id="${itemIndex}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#items-container').append(html);
                
                // Initialize Select2 for the specific new select
                $(`#row-${itemIndex} .select2`).select2({
                    width: '100%'
                });
                
                itemIndex++;
                updateGlobalSummary();
            }

            // Initial row
            addRow();

            $('#add-item').on('click', function() {
                addRow();
            });

            $(document).on('click', '.remove-row', function() {
                let id = $(this).data('id');
                $(`#row-${id}`).fadeOut(200, function() {
                    $(this).remove();
                    updateGlobalSummary();
                });
            });

            $(document).on('change', '.product-select', function() {
                let selected = $(this).find(':selected');
                let qty = selected.data('qty') || 0;
                let price = selected.data('price') || 0;
                
                let row = $(this).closest('tr');
                row.find('.stock-badge').text(qty).removeClass('badge-light badge-danger badge-success').addClass(qty > 0 ? 'badge-success' : 'badge-danger');
                row.find('.unit-price-display').text(parseFloat(price).toFixed(2));
                
                calculateRowSubtotal(row);
            });

            $(document).on('input change', '.qty-input', function() {
                let row = $(this).closest('tr');
                calculateRowSubtotal(row);
            });

            function calculateRowSubtotal(row) {
                let qty = parseFloat(row.find('.qty-input').val()) || 0;
                let priceDisplay = row.find('.unit-price-display').text();
                let price = parseFloat(priceDisplay) || 0;
                let subtotal = qty * price;
                
                row.find('.subtotal-text').text(subtotal.toFixed(2));
                updateGlobalSummary();
            }

            function updateGlobalSummary() {
                let grandTotal = 0;
                let totalQty = 0;
                let itemCount = $('.item-row').length;

                $('.item-row').each(function() {
                    let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                    let subtotal = parseFloat($(this).find('.subtotal-text').text()) || 0;
                    
                    totalQty += qty;
                    grandTotal += subtotal;
                });

                $('#total-items-count').text(itemCount);
                $('#total-qty-display').text(totalQty);
                $('#grand-total-display').text(grandTotal.toFixed(2));
            }
        });
    </script>
@endpush
