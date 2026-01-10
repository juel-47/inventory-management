<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

use App\Models\User;
use Spatie\Permission\Models\Role;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index()
    {
        $sales = Sale::with(['user', 'outletUser'])->orderBy('id', 'desc')->get();
        return view('backend.sale.index', compact('sales'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create()
    {
        // Fetch all users with the 'Outlet User' role
        $outletUsers = User::role('Outlet User')->where('status', 1)->get();
        $products = Product::where('status', 1)->select('id', 'name', 'sku', 'price', 'qty')->get();
        return view('backend.sale.create', compact('products', 'outletUsers'));
    }

    /**
     * Store a newly created sale.
     */
    public function store(Request $request)
    {
        $request->validate([
            'outlet_user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $sale = new Sale();
            $sale->invoice_no = 'SALE-' . mt_rand(100000, 999999);
            $sale->user_id = Auth::id(); // Admin who is recording it
            $sale->outlet_user_id = $request->outlet_user_id;
            $sale->date = $request->date;
            $sale->note = $request->note;
            $sale->total_amount = 0;
            $sale->status = 1;
            $sale->save();

            $totalAmount = 0;

            foreach ($request->items as $item) {
                // Check stock availability
                $product = Product::findOrFail($item['product_id']);
                if ($product->qty < $item['qty']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->qty}");
                }

                $subTotal = $item['qty'] * $item['unit_price'];
                $totalAmount += $subTotal;

                // Create Detail
                $detail = new SaleDetail();
                $detail->sale_id = $sale->id;
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->unit_price = $item['unit_price'];
                $detail->total = $subTotal;
                $detail->save();

                // Decrease Stock
                $product->decrement('qty', $item['qty']);
            }

            $sale->total_amount = $totalAmount;
            $sale->save();

            DB::commit();
            
            Toastr::success('Sale Created Successfully!', 'Success');
            return redirect()->route('admin.sales.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(string $id)
    {
        $sale = Sale::with(['user', 'outletUser', 'details.product'])->findOrFail($id);
        return view('backend.sale.show', compact('sale'));
    }

    /**
     * Remove the specified sale.
     */
    public function destroy(string $id)
    {
        $sale = Sale::findOrFail($id);
        
        // Restore Stock
        foreach($sale->details as $detail){
             $product = Product::find($detail->product_id);
             if($product){
                 $product->increment('qty', $detail->qty);
             }
        }
        
        $sale->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
}
