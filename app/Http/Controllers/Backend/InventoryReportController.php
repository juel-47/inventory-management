<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryStock;

class InventoryReportController extends Controller
{
    public function index()
    {
        // Consolidate duplicates: Group by Product+Variant and Sum Quantity
        $rawStocks = InventoryStock::with(['product', 'variant'])->get();
        
        $stocks = $rawStocks->groupBy(function($item) {
            return $item->product_id . '-' . $item->variant_id . '-' . $item->outlet_id;
        })->map(function($group) {
            $first = $group->first();
            $first->quantity = $group->sum('quantity');
            return $first;
        })->values();
        
        return view('backend.inventory_report.index', compact('stocks'));

        //if show all then use this : 
        // $stocks = InventoryStock::with(['product', 'variant'])->orderBy('product_id')->get();
        // return view('backend.inventory_report.index', compact('stocks'));
    }
}
