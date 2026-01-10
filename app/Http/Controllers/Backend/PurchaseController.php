<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with(['vendor', 'user'])->orderBy('id', 'desc')->get(); // Using get() for simple list first, or DataTable later if requested in plan
        return view('backend.purchase.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = Vendor::where('status', 1)->get();
        // Passing products for JS selection
        $products = Product::where('status', 1)->select('id', 'name', 'sku', 'purchase_price')->get(); 
        return view('backend.purchase.create', compact('vendors', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchase = new Purchase();
            $purchase->invoice_no = 'INV-' . mt_rand(100000, 999999);
            $purchase->vendor_id = $request->vendor_id;
            $purchase->user_id = Auth::id(); // Track Creator
            $purchase->date = $request->date;
            $purchase->note = $request->note;
            $purchase->total_amount = 0; // Will calculate
            $purchase->status = 1;
            $purchase->save();

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $subTotal = $item['qty'] * $item['unit_cost'];
                $totalAmount += $subTotal;

                // Create Detail
                $detail = new PurchaseDetail();
                $detail->purchase_id = $purchase->id;
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->unit_cost = $item['unit_cost'];
                $detail->total = $subTotal;
                $detail->save();

                // Update Stock
                $product = Product::findOrFail($item['product_id']);
                $product->increment('qty', $item['qty']);
                
                // Optional: Update purchase_price if changed? 
                // Let's keep it simple: We don't overwrite product default price automatically unless requested
            }

            $purchase->total_amount = $totalAmount;
            $purchase->save();

            DB::commit();
            
            Toastr::success('Purchase Created Successfully!', 'Success');
            return redirect()->route('admin.purchases.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Something went wrong: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchase = Purchase::with(['vendor', 'user', 'details.product'])->findOrFail($id);
        return view('backend.purchase.show', compact('purchase'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $purchase = Purchase::findOrFail($id);
        
        // Revert Stock? 
        // If we allow delete, we should decrement stock.
        foreach($purchase->details as $detail){
             $product = Product::find($detail->product_id);
             if($product){
                 $product->decrement('qty', $detail->qty);
             }
        }
        
        $purchase->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
}
