<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BookingDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\BookingStoreRequest;
use App\Http\Requests\Booking\BookingUpdateRequest;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Unit;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BookingDataTable $dataTable)
    {
        return $dataTable->render('backend.booking.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = Vendor::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        // Pass products with details for JS population
        $products = Product::where('status', 1)->with(['variants', 'category', 'subCategory', 'childCategory', 'unit', 'vendor'])->get(); 
        return view('backend.booking.create', compact('vendors', 'products', 'units', 'categories'));
    }

    /**
     * Get sub categories based on category (AJAX).
     */
    public function getSubCategories(Request $request)
    {
        $subCategories = SubCategory::where('category_id', $request->id)->where('status', 1)->get();
        return response()->json($subCategories);
    }

    /**
     * Get child categories based on sub category (AJAX).
     */
    public function getChildCategories(Request $request)
    {
        $childCategories = ChildCategory::where('sub_category_id', $request->id)->where('status', 1)->get();
        return response()->json($childCategories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookingStoreRequest $request)
    {
        $booking = new Booking();
        $booking->booking_no = 'BK-' . strtoupper(Str::random(10));
        $booking->vendor_id = $request->vendor_id;
        $booking->product_id = $request->product_id;
        $booking->category_id = $request->category_id;
        $booking->sub_category_id = $request->sub_category_id;
        $booking->child_category_id = $request->child_category_id;
        $booking->unit_id = $request->unit_id;
        $booking->qty = $request->qty;
        $booking->unit_price = $request->unit_price ?? 0;
        $booking->extra_cost = $request->extra_cost ?? 0;
        
        $total_cost = ($booking->qty * $booking->unit_price) + $booking->extra_cost;
        $booking->total_cost = $total_cost;
        
        $booking->sale_price = $request->sale_price;
        $booking->description = $request->description;
        
        $booking->min_inventory_qty = $request->min_inventory_qty;
        $booking->min_sale_qty = $request->min_sale_qty;
        $booking->min_purchase_price = $request->min_purchase_price;
        
        $booking->variant_info = $request->variant_info; 
        $booking->barcode = $request->barcode;
        $booking->custom_fields = $request->custom_fields; 
        
        $booking->status = $request->status ?? 'pending';
        $booking->save();

        // Update Product Stock
        $product = Product::findOrFail($request->product_id);
        $product->increment('qty', $request->qty);

        if($request->has('sale_price') && $request->sale_price > 0){
             $product->price = $request->sale_price;
             $product->save();
        }

        Toastr::success('Booking Created Successfully!');
        return redirect()->route('admin.bookings.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $booking = Booking::findOrFail($id);
        $vendors = Vendor::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        $subCategories = SubCategory::where('category_id', $booking->category_id)->where('status', 1)->get();
        $childCategories = ChildCategory::where('sub_category_id', $booking->sub_category_id)->where('status', 1)->get();
        $products = Product::where('status', 1)->with(['variants', 'category', 'subCategory', 'childCategory', 'unit', 'vendor'])->get();
        return view('backend.booking.edit', compact('booking', 'vendors', 'products', 'units', 'categories', 'subCategories', 'childCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookingUpdateRequest $request, string $id)
    {
        $booking = Booking::findOrFail($id);
        
        // Revert old stock? Diff calculation
        $diff = $request->qty - $booking->qty;
        
        $booking->vendor_id = $request->vendor_id;
        $booking->product_id = $request->product_id;
        $booking->category_id = $request->category_id;
        $booking->sub_category_id = $request->sub_category_id;
        $booking->child_category_id = $request->child_category_id;
        $booking->unit_id = $request->unit_id;
        $booking->qty = $request->qty;
        $booking->unit_price = $request->unit_price ?? 0;
        $booking->extra_cost = $request->extra_cost ?? 0;
        
        $total_cost = ($booking->qty * $booking->unit_price) + $booking->extra_cost;
        $booking->total_cost = $total_cost;
        
        $booking->sale_price = $request->sale_price;
        $booking->description = $request->description;
        
        $booking->min_inventory_qty = $request->min_inventory_qty;
        $booking->min_sale_qty = $request->min_sale_qty;
        $booking->min_purchase_price = $request->min_purchase_price;
        
        $booking->variant_info = $request->variant_info;
        $booking->barcode = $request->barcode;
        $booking->custom_fields = $request->custom_fields;
        
        $booking->status = $request->status;
        $booking->save();

        // Update Product Stock
        $product = Product::findOrFail($request->product_id);
        if($diff != 0) {
            $product->qty = $product->qty + $diff;
            $product->save();
        }
        
        Toastr::success('Booking Updated Successfully!');
        return redirect()->route('admin.bookings.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        // Decrease stock?
        $product = Product::find($booking->product_id);
        if($product) {
            $product->decrement('qty', $booking->qty);
        }
        $booking->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $booking = Booking::findOrFail($request->id);
        $booking->status = $request->status == 'true' ? 1 : 0;
        $booking->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
