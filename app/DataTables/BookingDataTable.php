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
                return formatConverted($query->total_cost);
            })
            ->addColumn('total_cost_vendor', function ($query) {
                if ($query->vendor) {
                    return formatWithVendor($query->total_cost, $query->vendor->currency_icon, $query->vendor->currency_rate);
                }
                return formatConverted($query->total_cost);
            })
            ->addColumn('status', function ($query) {
                // Determine current status. Default strict check.
                $status = strtolower($query->status);
                $options = [
                    'pending' => 'Pending',
                    'complete' => 'Complete',
                    'cancelled' => 'Cancelled',
                    'missing' => 'Missing'
                ];
                
                $html = '<select class="form-control change-status" data-id="' . $query->id . '" style="min-width: 100px;">';
                foreach($options as $key => $label) {
                    $selected = $status === $key ? 'selected' : '';
                    $html .= '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
                }
                $html .= '</select>';
                
                return $html;
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
        $settings = getSettings();
        return [
            Column::make('id'),
            Column::make('booking_no')->title('Booking No'),
            Column::make('vendor')->title('Vendor'),
            Column::make('product')->title('Product'),
            Column::make('qty')->title('Qty'),
            Column::make('total_cost')->title('Total'),
            Column::make('total_cost_vendor')->title('Vendor Total'),
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
