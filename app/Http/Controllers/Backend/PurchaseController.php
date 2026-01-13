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
        $products = Product::where('status', 1)->with('variants.color', 'variants.size')->get(); 
        
        // Fetch Bookings (Only those not fully purchased? For now, only 'pending' bookings)
        $bookings = \App\Models\Booking::with('product', 'vendor')
                    ->where('status', 'pending')
                    ->latest()
                    ->get();

        return view('backend.purchase.create', compact('vendors', 'products', 'bookings'));
    }

    public function getBookingDetails(Request $request) {
        $booking = \App\Models\Booking::with(['product', 'vendor', 'unit'])->findOrFail($request->id);
        return response()->json($booking);
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
            $purchase->booking_id = $request->booking_id; // Save Booking ID
            $purchase->user_id = Auth::id(); // Track Creator
            $purchase->date = $request->date;
            $purchase->note = $request->note;
            $purchase->material_cost = $request->material_cost ?? 0;
            $purchase->transport_cost = $request->transport_cost ?? 0;
            $purchase->tax = $request->tax ?? 0;
            $purchase->total_amount = 0; // Will calculate
            $purchase->status = 1;
            $purchase->save();

            // Calculate costs in System Currency (Input is Vendor Currency)
            $vendor = Vendor::findOrFail($request->vendor_id);
            $rate = $vendor->currency_rate > 0 ? $vendor->currency_rate : 1;

            $totalAmount = 0;

            foreach ($request->items as $item) {
                // Convert Vendor Currency (Input) to System Currency (Storage)
                $itemUnitCost = $item['unit_cost'] * $rate;
                
                $subTotal = $item['qty'] * $itemUnitCost;
                $totalAmount += $subTotal;

                // Create Detail
                $detail = new PurchaseDetail();
                $detail->purchase_id = $purchase->id;
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->unit_cost = $itemUnitCost;
                $detail->total = $subTotal;
                
                // Save & Standardize Variant Info
                $vInfo = null;
                if(isset($item['variant_info']) && !empty($item['variant_info'])) {
                    $vInfo = is_string($item['variant_info']) ? json_decode($item['variant_info'], true) : $item['variant_info'];
                    $detail->variant_info = $vInfo; 
                }
                
                $detail->save();

                // Update Stock and Purchase Price (Main Product)
                $product = Product::findOrFail($item['product_id']);
                $product->purchase_price = $itemUnitCost;
                $product->save();
                $product->increment('qty', $item['qty']);
                
                // Update Stock (Variants)
                if($vInfo && is_array($vInfo)) {
                    $processedVariants = [];
                    // Handle "Old Format" single variant {variant: "Name"}
                    if(isset($vInfo['variant'])) {
                        $variantName = $vInfo['variant'];
                        $variantQty = $item['qty'];
                        
                        $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                    ->where('name', $variantName)
                                    ->first();
                        if($pVariant && !in_array($pVariant->id, $processedVariants)) {
                            $pVariant->increment('qty', $variantQty);
                            $processedVariants[] = $pVariant->id;
                        }
                    } else {
                        // Handle "New Aggregated Format" {"Name": Qty, "Name2": Qty}
                        foreach($vInfo as $vName => $vQty) {
                            // Robust cleaning for legacy booking data
                            $cleanName = preg_replace('/Color:\s*/i', '', $vName);
                            $cleanName = preg_replace('/Size:\s*/i', '', $cleanName);
                            $cleanName = preg_replace('/\s*-\s*/', ' ', $cleanName);
                            $cleanName = trim($cleanName);

                            $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                        ->where('name', $cleanName)
                                        ->first();
                            if($pVariant && !in_array($pVariant->id, $processedVariants)) {
                                $pVariant->increment('qty', $vQty);
                                $processedVariants[] = $pVariant->id;
                            }
                        }
                    }
                }
            }

            // Add extra costs to total (Directly in system currency from UI)
            $totalAmount += ($purchase->material_cost + $purchase->transport_cost + $purchase->tax);
            
            $purchase->total_amount = $totalAmount;
            $purchase->save();

            // Automate Booking Completion
            if($purchase->booking_id) {
                \App\Models\Booking::where('id', $purchase->booking_id)->update(['status' => 'completed']);
            }

            DB::commit();
            
            Toastr::success('Purchase Created Successfully!');
            return redirect()->route('admin.purchases.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Something went wrong: ' . $e->getMessage());
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
