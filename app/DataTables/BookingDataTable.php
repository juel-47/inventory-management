<?php

namespace App\DataTables;

use App\Models\Booking;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BookingDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $edit = '<a href="' . route('admin.bookings.edit', $query->id) . '" class="btn btn-primary"><i class="fas fa-edit"></i></a>';
                $delete = '<a href="' . route('admin.bookings.destroy', $query->id) . '" class="btn btn-danger delete-item ml-2"><i class="fas fa-trash"></i></a>';
                return $edit . $delete;
            })
            ->addColumn('product', function ($query) {
                return $query->product->name ?? 'N/A';
            })
            ->addColumn('vendor', function ($query) {
                return $query->vendor->name ?? 'N/A'; // Assuming Vendor has 'name' or similar, strict user said 'Vendor Management Module' ... 'Vendor name'.
            })
            ->addColumn('total_cost', function ($query) {
                return formatWithCurrency($query->total_cost);
            })
            ->addColumn('status', function ($query) {
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    public function query(Booking $model)
    {
        return $model->newQuery()->with(['product', 'vendor']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('booking-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('booking_no')->title('Booking No'),
            Column::make('vendor')->title('Vendor'),
            Column::make('product')->title('Product'),
            Column::make('qty')->title('Qty'),
            Column::make('total_cost')->title('Total Cost'),
            Column::make('status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Booking_' . date('YmdHis');
    }
}
