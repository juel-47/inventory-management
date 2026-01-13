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
        $vendors = Vendor::where('status', 1)->latest()->get();
        $units = Unit::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        // Pass products with details for JS population
        $products = Product::where('status', 1)->with(['variants.color', 'variants.size', 'category', 'subCategory', 'childCategory', 'unit'])->latest()->get(); 
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
        // Common Data
        $commonData = [
            'booking_no' => 'BK-' . strtoupper(Str::random(10)), // Generate one number for the batch? Or separate? Let's use same if batch, or unique? Typically unique is safer for PKs, but for grouping same is nice. But schema has id PK. booking_no is string. Let's make them unique for now to avoid confusion, or same to imply grouping. Let's use unique to be safe.
            'vendor_id' => $request->vendor_id,
            'product_id' => $request->product_id,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'child_category_id' => $request->child_category_id,
            'unit_id' => $request->unit_id,
            'description' => $request->description,
            'barcode' => $request->barcode,
            'custom_fields' => $request->custom_fields,
            'status' => $request->status ?? 'pending',
            'unit_price' => 0,
            'extra_cost' => 0,
            'total_cost' => 0,
            'sale_price' => 0,
        ];

        $product = Product::findOrFail($request->product_id);

        if ($request->has('variant_quantities') && is_array($request->variant_quantities) && count(array_filter($request->variant_quantities)) > 0) {
            
            // Calculate Total Variant Qty & Filter Variants
            $variantSum = 0;
            $variantsData = [];
            foreach ($request->variant_quantities as $variant => $qty) {
                if ($qty > 0) {
                    $variantSum += $qty;
                    $variantsData[$variant] = $qty; // Store as {'Color: Red': 5, ...}
                }
            }

            // Trust $request->qty but ensure it covers variants
            $finalQty = $request->qty;
            if($finalQty < $variantSum) {
                $finalQty = $variantSum; // Enforce minimum
            }

            if($finalQty > 0) {
                $booking = new Booking();
                foreach ($commonData as $key => $value) {
                     $booking->$key = $value;
                }
                $booking->qty = $finalQty;
                // Store the entire array of variants and quantities
                $booking->variant_info = $variantsData; 
                $booking->save();
            }

        } else {
            // Single Booking (No variant selected or simple product)
            $booking = new Booking();
            foreach ($commonData as $key => $value) {
                 $booking->$key = $value;
            }
            $booking->qty = $request->qty;
            $booking->variant_info = $request->variant_info; 
            $booking->save();
        }
        
        // $product->save(); // Already incremented? increment() saves immediately. Remove this if increment() saves. Yes it does.

        Toastr::success('Booking(s) Created Successfully!');
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
        $vendors = Vendor::where('status', 1)->latest()->get();
        $units = Unit::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        $subCategories = SubCategory::where('category_id', $booking->category_id)->where('status', 1)->get();
        $childCategories = ChildCategory::where('sub_category_id', $booking->sub_category_id)->where('status', 1)->get();
        $products = Product::where('status', 1)->with(['variants.color', 'variants.size', 'category', 'subCategory', 'childCategory', 'unit'])->latest()->get();
        return view('backend.booking.edit', compact('booking', 'vendors', 'products', 'units', 'categories', 'subCategories', 'childCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookingUpdateRequest $request, string $id)
    {
        $booking = Booking::findOrFail($id);
        $product = Product::findOrFail($request->product_id);

        // 1. Calculate new Total Qty & Prepare Variant Info
        $variantSum = 0;
        $newVariantInfo = null;

        if ($request->has('variant_quantities') && is_array($request->variant_quantities) && count(array_filter($request->variant_quantities)) > 0) {
            $variantsData = [];
            foreach ($request->variant_quantities as $variant => $qty) {
                if ($qty > 0) {
                    $variantSum += $qty;
                    $variantsData[$variant] = $qty;
                }
            }
            $newVariantInfo = $variantsData; // Array of {'Variant Name': Qty}
        } else {
             $newVariantInfo = $request->variant_info;
        }

        // Use Request Qty (manual override allowed)
        $newTotalQty = $request->qty;
        if($newTotalQty < $variantSum) {
            $newTotalQty = $variantSum; // Enforce minimum
        }
        
        // If product changed:
        if($booking->product_id != $request->product_id) {
             $booking->product_id = $request->product_id;
        }
        
        $booking->category_id = $request->category_id;
        $booking->sub_category_id = $request->sub_category_id;
        $booking->child_category_id = $request->child_category_id;
        $booking->unit_id = $request->unit_id;
        $booking->qty = $newTotalQty;
        
        $booking->description = $request->description;
        $booking->variant_info = $newVariantInfo;
        $booking->barcode = $request->barcode;
        $booking->custom_fields = $request->custom_fields;
        $booking->status = $request->status;
        
        $booking->save();

        Toastr::success('Booking Updated Successfully!');
        return redirect()->route('admin.bookings.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $booking = Booking::findOrFail($request->id);
        $booking->status = $request->status;
        $booking->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
