<?php

namespace App\DataTables;

use App\Models\Product;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                if (!auth()->user()->hasRole('Admin')) {
                    return '';
                }
                 $edit = '<a href="' . route('admin.products.edit', $query->id) . '" class="btn btn-primary"><i class="fas fa-edit"></i></a>';
                 $delete = '<a href="' . route('admin.products.destroy', $query->id) . '" class="btn btn-danger delete-item ml-2"><i class="fas fa-trash"></i></a>';
                return $edit . $delete;
            })
            ->addColumn('thumb_image', function ($query) {
                 return $query->thumb_image ? '<img src="' . asset('storage/' . $query->thumb_image) . '" width="80px" class="img-thumbnail">' : '';
            })
            ->addColumn('status', function ($query) {
                if (!auth()->user()->hasRole('Admin')) {
                    return $query->status ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
                }
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
             ->addColumn('category', function($query){
                return $query->category->name ?? '';
             })
             ->addColumn('price', function($query){
                return formatWithCurrency($query->price);
             })
            ->rawColumns(['action', 'status', 'thumb_image'])
            ->setRowId('id');
    }

    public function query(Product $model)
    {
        return $model->newQuery()->with('category'); // Eager load category
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-table')
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
        $columns = [
            Column::make('id'),
            Column::make('thumb_image')->title('Image'),
            Column::make('name'),
            Column::make('category')->title('Category'),
            Column::make('price')->title('Selling Price'),
            Column::make('qty')->title('Qty'),
        ];

        if (auth()->user()->hasRole('Admin')) {
            $columns[] = Column::make('status');
            $columns[] = Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center');
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Product_' . date('YmdHis');
    }
}
