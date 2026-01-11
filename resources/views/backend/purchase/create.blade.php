@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Create Purchase</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>New Purchase Invoice</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.purchases.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Vendor</label>
                                        <select class="form-control select2" name="vendor_id" required>
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Date</label>
                                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                     <div class="form-group col-md-4">
                                        <label>Note (Optional)</label>
                                        <input type="text" class="form-control" name="note">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="product_table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th id="vendor_unit_cost_header">Vendor Unit Cost</th>
                                                <th>System Unit Cost</th>
                                                <th>Qty</th>
                                                <th id="vendor_subtotal_header">Vendor Sub Total</th>
                                                <th>System Sub Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5">
                                                    <button type="button" class="btn btn-primary btn-sm" id="add_row_btn"><i class="fas fa-plus"></i> Add Product</button>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-12 text-right">
                                        <h4 id="vendor_grand_total_container">Vendor Total: <span id="vendor_grand_total">0.00</span></h4>
                                        <h4>System Total: <span id="grand_total_display">{{ $settings->currency_icon }}0.00</span></h4>
                                        <input type="hidden" name="total_amount" id="total_amount_hidden">
                                        <button type="submit" class="btn btn-success btn-lg mt-2">Save Purchase</button>
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
        let rowCount = 0;
        let currentVendorRate = 1;
        let currentVendorIcon = '{{ $settings->currency_icon }}';
        let currentVendorName = '{{ $settings->currency_name }}';
        
        const systemIcon = '{{ $settings->currency_icon }}';

        $(document).ready(function() {
            // Vendor selection change
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
                            
                            $('#vendor_unit_cost_header').text('Vendor Unit Cost (' + currentVendorName + ')');
                            $('#vendor_subtotal_header').text('Vendor Subtotal (' + currentVendorName + ')');
                            $('#vendor_grand_total_container').html('Vendor Total (' + currentVendorName + '): <span id="vendor_grand_total">0.00</span>');
                            
                            recalculateAllRows();
                        }
                    });
                } else {
                    currentVendorRate = 1;
                    currentVendorIcon = '{{ $settings->currency_icon }}';
                    currentVendorName = '{{ $settings->currency_name }}';
                    $('#vendor_unit_cost_header').text('Vendor Unit Cost (' + currentVendorName + ')');
                    $('#vendor_subtotal_header').text('Vendor Subtotal (' + currentVendorName + ')');
                    $('#vendor_grand_total_container').html('Vendor Total (' + currentVendorName + '): <span id="vendor_grand_total">0.00</span>');
                    recalculateAllRows();
                }
            });

            // Add initial row
            addRow();

            $('#add_row_btn').on('click', function() {
                addRow();
            });

            $(document).on('click', '.remove_row', function() {
                $(this).closest('tr').remove();
                calculateGrandTotal();
            });

            // On Product Change
            $(document).on('change', '.product_select', function() {
                let id = $(this).val();
                let row = $(this).closest('tr');
                let product = products.find(p => p.id == id);
                
                if(product) {
                    // Assuming product.purchase_price is System Price
                    // We need to back-calculate Vendor Price? 
                    // Or set System Price and empty Vendor Price?
                    // Let's set the Input (Vendor Price) to 0 or product.purchase_price (converted?)
                    // For now, let's just set it to product.purchase_price (System) / Rate (if we want match)
                    // But simplified: user enters what they buy at.
                    row.find('.unit_cost').val(''); 
                    calculateRowTotal(row);
                }
            });

            // On Input Change
            $(document).on('input', '.qty, .unit_cost', function() {
                calculateRowTotal($(this).closest('tr'));
            });
        });

        function addRow() {
            let html = `
                <tr>
                    <td>
                        <select class="form-control product_select select2" name="items[${rowCount}][product_id]" required>
                            <option value="">Select Product</option>
                            ${products.map(p => `<option value="${p.id}">${p.name} (SKU: ${p.sku})</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control unit_cost" name="items[${rowCount}][unit_cost]" step="0.01" required>
                    </td>
                    <td>
                        <input type="text" class="form-control unit_cost_system" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control qty" name="items[${rowCount}][qty]" value="1" min="1" required>
                    </td>
                     <td>
                        <input type="text" class="form-control subtotal" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control subtotal_system" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove_row"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#product_table tbody').append(html);
            $('.select2').select2();
            rowCount++;
        }

        function calculateRowTotal(row) {
            let qty = parseFloat(row.find('.qty').val()) || 0;
            let vendorCost = parseFloat(row.find('.unit_cost').val()) || 0; // Input is Vendor
            let vendorTotal = qty * vendorCost;
            
            row.find('.subtotal').val(vendorTotal.toFixed(2)); // Use .subtotal for vendor display initially? No, .subtotal was System in prev code.
            // Let's rely on specific classes.
            
            // System conversions
            let systemCost = (vendorCost * currentVendorRate).toFixed(2);
            let systemSubtotal = (vendorTotal * currentVendorRate).toFixed(2);
            
            row.find('.unit_cost_system').val(systemIcon + systemCost);
            row.find('.subtotal_system').val(systemIcon + systemSubtotal);
            
            // Wait, previous code used .subtotal for input total.
            // Let's ensure headers match.
        
            calculateGrandTotal();
        }

        function recalculateAllRows() {
            $('#product_table tbody tr').each(function() {
                calculateRowTotal($(this));
            });
        }

        function calculateGrandTotal() {
            let vendorTotal = 0;
            $('#product_table tbody tr').each(function() {
                 let qty = parseFloat($(this).find('.qty').val()) || 0;
                 let cost = parseFloat($(this).find('.unit_cost').val()) || 0;
                 vendorTotal += qty * cost;
            });
            
            let systemTotal = (vendorTotal * currentVendorRate).toFixed(2);
            
            $('#vendor_grand_total').text(currentVendorIcon + vendorTotal.toFixed(2));
            $('#grand_total_display').text(systemIcon + systemTotal);
            $('#total_amount_hidden').val(systemTotal); // Send System Total? Or Backend calcs it? Backend calcs it. This hidden might be for display or other checks.
        }
    </script>
@endpush
