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
                                                <th width="40%">Product</th>
                                                <th width="15%">Unit Cost</th>
                                                <th width="15%">Qty</th>
                                                <th width="15%">Subtotal</th>
                                                <th width="10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4">
                                                    <button type="button" class="btn btn-primary btn-sm" id="add_row_btn"><i class="fas fa-plus"></i> Add Product</button>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-12 text-right">
                                        <h4>Total: $<span id="grand_total">0.00</span></h4>
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

        $(document).ready(function() {
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
                    row.find('.unit_cost').val(product.purchase_price);
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
                        <input type="number" class="form-control qty" name="items[${rowCount}][qty]" value="1" min="1" required>
                    </td>
                    <td>
                        <input type="text" class="form-control subtotal" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove_row"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#product_table tbody').append(html);
            // Re-init select2 for new row if your template uses it (assuming global select2 init might not catch dynamic)
            // But let's rely on standard bootstrap select first or if we need to init:
             $('.select2').select2();
            
            rowCount++;
        }

        function calculateRowTotal(row) {
            let qty = parseFloat(row.find('.qty').val()) || 0;
            let cost = parseFloat(row.find('.unit_cost').val()) || 0;
            let total = qty * cost;
            row.find('.subtotal').val(total.toFixed(2));
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            $('.subtotal').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#grand_total').text(total.toFixed(2));
        }
    </script>
@endpush
