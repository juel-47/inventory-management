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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingNotification;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $booking_no = 'BK-' . strtoupper(Str::random(10));
        $bookings_saved = [];

        foreach ($request->items as $item) {
            $booking = new Booking();
            $booking->booking_no = $booking_no;
            $booking->vendor_id = $request->vendor_id;
            $booking->product_id = $item['product_id'];
            
            // Fetch product to get category/unit defaults
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $booking->category_id = $product->category_id;
            $booking->sub_category_id = $product->sub_category_id;
            $booking->child_category_id = $product->child_category_id;
            $booking->unit_id = $item['unit_id'] ?? $product->unit_id;
            
            $booking->qty = $item['qty'];
            
            // Handle Variant Info
            if (isset($item['variant_quantities']) && is_array($item['variant_quantities']) && count(array_filter($item['variant_quantities'])) > 0) {
                $variantSum = 0;
                $variantsData = [];
                foreach ($item['variant_quantities'] as $variant => $qty) {
                    if ($qty > 0) {
                        $variantSum += $qty;
                        $variantsData[$variant] = $qty;
                    }
                }
                $booking->variant_info = $variantsData;
                if ($booking->qty < $variantSum) {
                    $booking->qty = $variantSum;
                }
            } else {
                $booking->variant_info = $item['variant_info'] ?? null;
            }

            $booking->description = $request->description;
            $booking->custom_fields = $request->custom_fields;
            $booking->status = $request->status ?? 'pending';
            
            $booking->unit_price = 0;
            $booking->extra_cost = 0;
            $booking->total_cost = 0;
            $booking->sale_price = 0;
            
            $booking->save();
            $bookings_saved[] = $booking;
        }

        if (count($bookings_saved) > 0) {
            $vendor = Vendor::find($request->vendor_id);
            if ($vendor && $vendor->email) {
                dispatch(function () use ($bookings_saved, $vendor) {
                    Mail::to($vendor->email)->send(new BookingNotification($bookings_saved[0]));
                })->afterResponse();
            }
        }

        Toastr::success('Order(s) Placed Successfully!');
        return redirect()->route('admin.bookings.index');
    }

    public function viewInvoice(string $id)
    {
        $targetBooking = Booking::findOrFail($id);
        $orderGroup = Booking::where('booking_no', $targetBooking->booking_no)
            ->with(['product.variants.color', 'product.variants.size', 'vendor', 'unit'])
            ->get();
        
        $settings = \App\Models\GeneralSetting::first();

        return view('backend.booking.invoice', compact('orderGroup', 'targetBooking', 'settings'));
    }

    public function downloadPdf(string $id)
    {
        $targetBooking = Booking::findOrFail($id);
        $orderGroup = Booking::where('booking_no', $targetBooking->booking_no)
            ->with(['product.variants.color', 'product.variants.size', 'vendor', 'unit'])
            ->get();
        
        $settings = \App\Models\GeneralSetting::first();

        $pdf = Pdf::loadView('backend.booking.print_pdf', compact('orderGroup', 'targetBooking', 'settings'));
        return $pdf->download('Booking_'.$targetBooking->booking_no.'.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $targetBooking = Booking::findOrFail($id);
        // Fetch all bookings with the same booking_no
        $orderGroup = Booking::where('booking_no', $targetBooking->booking_no)->with(['product.variants.color', 'product.variants.size'])->get();
        
        $vendors = Vendor::where('status', 1)->latest()->get();
        $units = Unit::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        
        // Match create fields: products for selection
        $products = Product::where('status', 1)->with(['variants.color', 'variants.size', 'category', 'subCategory', 'childCategory', 'unit'])->latest()->get();

        return view('backend.booking.edit', compact('orderGroup', 'targetBooking', 'vendors', 'units', 'categories', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookingUpdateRequest $request, string $id)
    {
        $targetBooking = Booking::findOrFail($id);
        $bookingNo = $targetBooking->booking_no;

        DB::beginTransaction();
        try {
            // Delete existing group records (to re-sync batch)
            Booking::where('booking_no', $bookingNo)->delete();

            // Re-insert new/updated items
            foreach ($request->items as $item) {
                $booking = new Booking();
                $booking->booking_no = $bookingNo;
                $booking->vendor_id = $request->vendor_id;
                $booking->product_id = $item['product_id'];
                
                // Categorization
                $product = Product::find($item['product_id']);
                $booking->category_id = $product->category_id;
                $booking->sub_category_id = $product->sub_category_id;
                $booking->child_category_id = $product->child_category_id;
                
                $booking->unit_id = $item['unit_id'] ?? $product->unit_id;
                $booking->qty = $item['qty'];

                // Handle Variant Info
                if (isset($item['variant_quantities']) && is_array($item['variant_quantities']) && count(array_filter($item['variant_quantities'])) > 0) {
                    $variantSum = 0;
                    $variantsData = [];
                    foreach ($item['variant_quantities'] as $variant => $qty) {
                        if ($qty > 0) {
                            $variantSum += $qty;
                            $variantsData[$variant] = $qty;
                        }
                    }
                    $booking->variant_info = $variantsData;
                    if ($booking->qty < $variantSum) {
                        $booking->qty = $variantSum;
                    }
                }

                $booking->description = $request->description;
                $booking->custom_fields = $request->custom_fields;
                $booking->status = $request->status ?? 'pending';
                
                $booking->unit_price = 0;
                $booking->extra_cost = 0;
                $booking->total_cost = 0;
                $booking->sale_price = 0;
                
                $booking->save();
            }

            DB::commit();
            Toastr::success('Order Updated Successfully!');
            return redirect()->route('admin.bookings.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            Booking::where('booking_no', $booking->booking_no)->delete();
            return response(['status' => 'success', 'message' => 'Order Deleted Successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function changeStatus(Request $request)
    {
        $bookingNo = $request->booking_no;
        Booking::where('booking_no', $bookingNo)->update(['status' => $request->status]);
        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
