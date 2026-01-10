@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Create Sale</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>New Sale Invoice</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.sales.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Date</label>
                                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Select Outlet</label>
                                        <select name="outlet_user_id" class="form-control select2" required>
                                            <option value="">Select Outlet</option>
                                            @foreach ($outletUsers as $outlet)
                                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                            @endforeach
                                        </select>
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
                                                <th width="15%">Available</th>
                                                <th width="15%">Unit Price</th>
                                                <th width="10%">Qty</th>
                                                <th width="15%">Subtotal</th>
                                                <th width="10%">Action</th>
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
                                        <h4>Total: $<span id="grand_total">0.00</span></h4>
                                        <button type="submit" class="btn btn-success btn-lg mt-2">Save Sale</button>
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
            addRow();

            $('#add_row_btn').on('click', function() {
                addRow();
            });

            $(document).on('click', '.remove_row', function() {
                $(this).closest('tr').remove();
                calculateGrandTotal();
            });

            $(document).on('change', '.product_select', function() {
                let id = $(this).val();
                let row = $(this).closest('tr');
                let product = products.find(p => p.id == id);
                
                if(product) {
                    row.find('.available_qty').text(product.qty);
                    row.find('.unit_price').val(product.price);
                    row.find('.qty').attr('max', product.qty);
                    calculateRowTotal(row);
                }
            });

            $(document).on('input', '.qty, .unit_price', function() {
                calculateRowTotal($(this).closest('tr'));
            });
        });

        function addRow() {
            let html = `
                <tr>
                    <td>
                        <select class="form-control product_select" name="items[${rowCount}][product_id]" required>
                            <option value="">Select Product</option>
                            ${products.map(p => `<option value="${p.id}">${p.name} (SKU: ${p.sku})</option>`).join('')}
                        </select>
                    </td>
                    <td class="available_qty">-</td>
                    <td>
                        <input type="number" class="form-control unit_price" name="items[${rowCount}][unit_price]" step="0.01" required>
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
            rowCount++;
        }

        function calculateRowTotal(row) {
            let qty = parseFloat(row.find('.qty').val()) || 0;
            let price = parseFloat(row.find('.unit_price').val()) || 0;
            let total = qty * price;
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
