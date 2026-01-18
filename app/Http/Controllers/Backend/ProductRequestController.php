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

        $productRequests = $query->get();
        return view('backend.product-request.index', compact('productRequests'));
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

        $products = Product::where('status', 1)
            ->with(['variants.inventoryStocks', 'inventoryStocks'])
            ->select('id', 'name', 'sku', 'qty', 'price', 'thumb_image')
            ->get();
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
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
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
                    'variant_id' => $item['variant_id'] ?? null,
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
        $productRequest = ProductRequest::with(['user', 'items.product', 'items.variant.color', 'items.variant.size'])->findOrFail($id);
        
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

        DB::beginTransaction();
        try {
            $productRequest->update([
                'status' => $request->status,
                'admin_note' => $request->admin_note
            ]);
            
            DB::commit();
            Toastr::success('Request status updated successfully!', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Something went wrong: ' . $e->getMessage(), 'Error');
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
