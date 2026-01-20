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
        $purchases = Purchase::with(['vendor', 'user', 'details'])->orderBy('id', 'desc')->get(); // Using get() for simple list first, or DataTable later if requested in plan
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
            'invoice_attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,xlsx,xls|max:5120', // Max 5MB
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

            // Handle Invoice Attachment Upload
            if ($request->hasFile('invoice_attachment')) {
                $file = $request->file('invoice_attachment');
                $filename = 'invoice_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments/purchases', $filename, 'public');
                $purchase->invoice_attachment = $path;
            }

            $purchase->save();

            // Calculate costs in System Currency (Input is Vendor Currency)
            $vendor = Vendor::findOrFail($request->vendor_id);
            $rate = $vendor->currency_rate > 0 ? $vendor->currency_rate : 1;

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $qty = $item['qty'] > 0 ? $item['qty'] : 1;
                // Get cost breakdown components (these are in system currency)
                // Raw material cost acts as the base cost (includes converted vendor cost)
                $rawMaterial = ($item['raw_material_cost'] ?? 0);
                $tax = ($item['tax_cost'] ?? 0);
                $transport = ($item['transport_cost'] ?? 0);
                
                // Landed cost (per unit) = rawMaterial + tax + transport
                $itemUnitCost = $rawMaterial + $tax + $transport;
                
                // Subtotal = (Raw Material + Tax + Transport) * Qty
                $subTotal = $itemUnitCost * $qty;
                $totalAmount += $subTotal;

                // Create Detail
                $detail = new PurchaseDetail();
                $detail->purchase_id = $purchase->id;
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->unit_cost = $itemUnitCost;
                $detail->unit_cost_vendor = $item['unit_cost'] ?? 0;
                $detail->raw_material_cost = $rawMaterial;
                $detail->tax_cost = $tax;
                $detail->transport_cost = $transport;
                $detail->total = $subTotal;
                
                // Save & Standardize Variant Info
                $vInfo = null;
                if(isset($item['variant_info']) && !empty($item['variant_info'])) {
                    $vInfo = is_string($item['variant_info']) ? json_decode($item['variant_info'], true) : $item['variant_info'];
                    $detail->variant_info = $vInfo; 
                }
                
                $detail->save();

                // Update Product Costs and Prices
                $product = Product::findOrFail($item['product_id']);
                
                // Store the total landed cost (system currency) as the purchase price
                $product->purchase_price = $itemUnitCost;
                
                // Update local costs to the product record
                $product->raw_material_cost = $rawMaterial;
                $product->tax = $tax;
                $product->transport_cost = $transport;
                
                if(isset($item['sale_price'])) {
                    $product->price = $item['sale_price'];
                }
                
                if(isset($item['outlet_price'])) {
                    $product->outlet_price = $item['outlet_price'];
                }
                
                $product->save();
                
                // Update main stock
                // REDUNDANT - Handled by InventoryStock
                // $product->increment('qty', $item['qty']);
                
                // Update Stock (Variants)
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
                            // REDUNDANT - Handled by InventoryStock
                            // $pVariant->increment('qty', $variantQty);
                            $processedVariants[] = $pVariant->id;

                            // INV PLANE: InventoryStock
                            $stock = \App\Models\InventoryStock::firstOrCreate([
                                'product_id' => $item['product_id'],
                                'variant_id' => $pVariant->id,
                                'outlet_id' => 1 // Default
                            ]);
                            $stock->increment('quantity', $variantQty);

                            // INV PLANE: StockLedger
                            \App\Models\StockLedger::create([
                                'product_id' => $item['product_id'],
                                'variant_id' => $pVariant->id,
                                'outlet_id' => 1,
                                'reference_type' => 'purchase',
                                'reference_id' => $purchase->id,
                                'in_qty' => $variantQty,
                                'out_qty' => 0,
                                'balance_qty' => $stock->quantity, // Post-increment
                                'date' => $request->date
                            ]);
                            
                            // INV PLANE: Update Detail variant_id
                            $detail->variant_id = $pVariant->id;
                            $detail->save();
                        }
                    } else {
                        // Handle "New Aggregated Format" {"Name": Qty, "Name2": Qty}
                        foreach($vInfo as $vName => $vQty) {
                            // 1. Try Exact Match
                            $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                        ->where('name', trim($vName))
                                        ->first();

                            // 2. Try Cleaning Prefixes (Color:, Size:)
                            if (!$pVariant) {
                                $cleanName = preg_replace('/(Color|Size):\s*/i', '', $vName);
                                $cleanName = trim($cleanName);
                                
                                if ($cleanName !== $vName) {
                                     $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                            ->where('name', $cleanName)
                                            ->first();
                                }
                            }

                            // 3. Try Legacy Cleanup (Hyphens to Spaces) - Only if still not found
                            if (!$pVariant && isset($cleanName)) {
                                 $cleanNameLegacy = preg_replace('/\s*-\s*/', ' ', $cleanName);
                                 $cleanNameLegacy = trim($cleanNameLegacy);
                                 
                                 if ($cleanNameLegacy !== $cleanName) {
                                     $pVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                                            ->where('name', $cleanNameLegacy)
                                            ->first();
                                 }
                            }
                            
                            if($pVariant) {
                                // REDUNDANT - Handled by InventoryStock
                                // $pVariant->increment('qty', $vQty);
                                
                                // INV PLANE: InventoryStock
                                $stock = \App\Models\InventoryStock::firstOrCreate([
                                    'product_id' => $item['product_id'],
                                    'variant_id' => $pVariant->id,
                                    'outlet_id' => 1 // Default
                                ]);
                                $stock->increment('quantity', $vQty);
    
                                // INV PLANE: StockLedger
                                \App\Models\StockLedger::create([
                                    'product_id' => $item['product_id'],
                                    'variant_id' => $pVariant->id,
                                    'outlet_id' => 1,
                                    'reference_type' => 'purchase',
                                    'reference_id' => $purchase->id,
                                    'in_qty' => $vQty,
                                    'out_qty' => 0,
                                    'balance_qty' => $stock->quantity, // Post-increment
                                    'date' => $request->date
                                ]);

                                // Note: PurchaseDetail structure assumes one variant per line often, 
                                // but if aggregated, we might have issues linking single detail to multiple variant ledgers.
                                // For now, we update logic, but ideal structure is 1 line = 1 variant.
                                // If detail->variant_id is single, we can only set one. 
                                // Assuming simplest case: likely one variant dominant or split lines.
                                // We will update variant_id if it's the first one found, for trace.
                                if(!$detail->variant_id) {
                                    $detail->variant_id = $pVariant->id;
                                    $detail->save();
                                }
                            } else {
                                // Fallback: If variant not found by name, assign to main product stock (No Variant)
                                // This ensures stock is not lost if name matching fails
                                $stock = \App\Models\InventoryStock::firstOrCreate([
                                    'product_id' => $item['product_id'],
                                    'variant_id' => null,
                                    'outlet_id' => 1
                                ]);
                                $stock->increment('quantity', $vQty);

                                // Ledger Fallback
                                \App\Models\StockLedger::create([
                                    'product_id' => $item['product_id'],
                                    'variant_id' => null,
                                    'outlet_id' => 1,
                                    'reference_type' => 'purchase',
                                    'reference_id' => $purchase->id,
                                    'in_qty' => $vQty,
                                    'out_qty' => 0,
                                    'balance_qty' => $stock->quantity,
                                    'date' => $request->date
                                ]);
                            }
                        }
                    }
                } else {
                    // No Variant Info - Product Level Stock Logic
                    // INV PLANE: InventoryStock (No Variant)
                    $stock = \App\Models\InventoryStock::firstOrCreate([
                        'product_id' => $item['product_id'],
                        'variant_id' => null,
                        'outlet_id' => 1 // Default
                    ]);
                    $stock->increment('quantity', $item['qty']);

                    // INV PLANE: StockLedger
                    \App\Models\StockLedger::create([
                        'product_id' => $item['product_id'],
                        'variant_id' => null,
                        'outlet_id' => 1,
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'in_qty' => $item['qty'],
                        'out_qty' => 0,
                        'balance_qty' => $stock->quantity,
                        'date' => $request->date
                    ]);
                }
            }

            // The totalAmount already includes material, transport, and tax distributed at row level
            $purchase->total_amount = $totalAmount;
            $purchase->save();

            // Automate Booking Completion
            if($purchase->booking_id) {
                \App\Models\Booking::where('id', $purchase->booking_id)->update(['status' => 'complete']);
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
        try {
            DB::beginTransaction();

            $purchase = Purchase::with('details')->findOrFail($id);

            // Revert Stock
            foreach ($purchase->details as $detail) {
                // Decrement InventoryStock
                $variant_id = $detail->variant_id;
                
                $stock = \App\Models\InventoryStock::where('product_id', $detail->product_id)
                            ->where('variant_id', $variant_id)
                            ->first();

                if ($stock) {
                    $stock->decrement('quantity', $detail->qty);
                    
                    // Add Ledger Entry for Reversal
                    \App\Models\StockLedger::create([
                        'product_id' => $detail->product_id,
                        'variant_id' => $variant_id,
                        'outlet_id' => 1,
                        'reference_type' => 'purchase_delete',
                        'reference_id' => $purchase->id,
                        'in_qty' => 0,
                        'out_qty' => $detail->qty, // Out because we are reversing a purchase (in)
                        'balance_qty' => $stock->quantity,
                        'date' => date('Y-m-d') 
                    ]);
                }
            }

            $purchase->delete();

            DB::commit();

            return response(['status' => 'success', 'message' => 'Purchase Deleted and Stock Reverted Successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['status' => 'error', 'message' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
}
