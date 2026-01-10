<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use App\Models\Sale;
use App\Models\SaleDetail;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductRequestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // No strict global middleware here because methods have internal checks
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = ProductRequest::with(['user'])->orderBy('id', 'desc');
        
        // If the user is not an admin, they should only see their own requests.
        // Assuming 'admin' role or permissions. For now, let's filter by user_id if not a super user.
        if (!Auth::user()->hasRole('Admin')) {
            $query->where('user_id', Auth::id());
        }

        $requests = $query->get();
        return view('backend.product-request.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only Outlet Users (or specific permission) can create requests. 
        // Admin usually doesn't request from themselves.
        if (!Auth::user()->can('Create Product Requests') && !Auth::user()->hasRole('Outlet User')) {
            abort(403, 'Only Outlet users can create product requests.');
        }

        $products = Product::where('status', 1)->select('id', 'name', 'sku', 'qty', 'price')->get();
        return view('backend.product-request.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Product Requests') && !Auth::user()->hasRole('Outlet User')) {
            abort(403);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $productRequest = new ProductRequest();
            $productRequest->request_no = 'REQ-' . strtoupper(Str::random(10));
            $productRequest->user_id = Auth::id();
            $productRequest->status = 'pending';
            $productRequest->note = $request->note;
            $productRequest->total_qty = 0; 
            $productRequest->save();

            $totalQty = 0;
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $item['qty'] * $product->price;
                
                $totalQty += $item['qty'];
                $totalAmount += $subtotal;

                ProductRequestItem::create([
                    'product_request_id' => $productRequest->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }

            $productRequest->total_qty = $totalQty;
            $productRequest->total_amount = $totalAmount;
            $productRequest->save();

            DB::commit();
            Toastr::success('Product Request Submitted Successfully!', 'Success');
            return redirect()->route('admin.product-requests.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productRequest = ProductRequest::with(['user', 'items.product'])->findOrFail($id);
        
        // Authorization check
        if (!Auth::user()->hasRole('Admin') && $productRequest->user_id !== Auth::id()) {
            abort(403);
        }

        return view('backend.product-request.show', compact('productRequest'));
    }

    /**
     * Update the status of the request.
     */
    public function updateStatus(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        
        // Only Admin can update status
        if (!Auth::user()->hasRole('Admin')) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,shipped,completed',
            'admin_note' => 'nullable|string'
        ]);

        $oldStatus = $productRequest->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            // Logic: Deduct stock and Create Sale when status changes to 'approved'
            if ($newStatus === 'approved' && $oldStatus !== 'approved' && $oldStatus !== 'shipped' && $oldStatus !== 'completed') {
                
                $sale = new Sale();
                $sale->invoice_no = 'SALE-' . $productRequest->request_no;
                $sale->user_id = Auth::id(); // Admin who approved it
                $sale->outlet_user_id = $productRequest->user_id; // The outlet user who requested it
                $sale->date = date('Y-m-d');
                $sale->note = "Generated from Product Request: " . $productRequest->request_no;
                $sale->total_amount = $productRequest->total_amount;
                $sale->status = 1;
                $sale->save();

                foreach ($productRequest->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    if ($product->qty < $item->qty) {
                        throw new \Exception("Insufficient stock for product: " . $product->name);
                    }
                    
                    // Deduct Stock
                    $product->decrement('qty', $item->qty);

                    // Create Sale Detail using stored prices
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item->product_id,
                        'qty' => $item->qty,
                        'unit_price' => $item->unit_price,
                        'total' => $item->subtotal,
                    ]);
                }
            }
            
            // Logic: Revert stock and Delete Sale if status changes from 'approved' back to 'rejected' or 'pending'
            if ($oldStatus === 'approved' && ($newStatus === 'rejected' || $newStatus === 'pending')) {
                 foreach ($productRequest->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $product->increment('qty', $item['qty']);
                }

                // Delete the linked sale
                $linkedSale = Sale::where('invoice_no', 'SALE-' . $productRequest->request_no)->first();
                if ($linkedSale) {
                    $linkedSale->details()->delete();
                    $linkedSale->delete();
                }
            }

            $productRequest->status = $newStatus;
            $productRequest->admin_note = $request->admin_note;
            $productRequest->save();

            DB::commit();
            Toastr::success('Request Status Updated Successfully and Sale Recorded!', 'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        
        // Authorization
        if (!Auth::user()->hasRole('Admin') && ($productRequest->user_id !== Auth::id() || $productRequest->status !== 'pending')) {
             return response(['status' => 'error', 'message' => 'Unauthorized or request already processed']);
        }

        $productRequest->delete(); // Items deleted by cascade
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
}
