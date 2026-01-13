<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductCreateRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\Unit;
use App\Models\Vendor;
use App\Traits\ImageUploadTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    use ImageUploadTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('role:Admin', except: ['index']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ProductDataTable $dataTable)
    {
        return $dataTable->render('backend.product.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $colors = Color::where('status', 1)->get();
        $sizes = Size::where('status', 1)->get();
        return view('backend.product.create', compact('categories', 'brands', 'units', 'colors', 'sizes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateRequest $request)
    {
        $imagePath = $this->upload_image($request, 'image', 'uploads/products');

        $product = new Product();
        $product->thumb_image = $imagePath;
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->category_id = $request->category_id;
        $product->sub_category_id = $request->sub_category_id;
        $product->child_category_id = $request->child_category_id;
        $product->brand_id = $request->brand_id;
        $product->vendor_id = $request->vendor_id;
        $product->unit_id = $request->unit_id;
        $product->product_number = $request->product_number;
        $product->qty = $request->qty ?? 0;
        $product->long_description = $request->long_description;
        $product->purchase_price = $request->purchase_price ?? 0;
        $product->price = $request->price ?? 0;
        $product->barcode = $request->barcode;
        $product->status = $request->status;
        $product->save();

        // Handle Variants
        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                if(!empty($variant['color_id']) || !empty($variant['size_id'])) {
                    $productVariant = new ProductVariant();
                    $productVariant->product_id = $product->id;
                    $productVariant->color_id = $variant['color_id'] ?? null;
                    $productVariant->size_id = $variant['size_id'] ?? null;
                    
                    // Generate name for backward compatibility
                    $colorName = $productVariant->color_id ? Color::find($productVariant->color_id)->name : '';
                    $sizeName = $productVariant->size_id ? Size::find($productVariant->size_id)->name : '';
                    $productVariant->name = trim($colorName . ' ' . $sizeName);
                    
                    $productVariant->qty = $variant['qty'] ?? 0;
                    $productVariant->save();
                }
            }
        }

        Toastr::success('Product Created Successfully!');
        return redirect()->route('admin.products.index');
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
        $product = Product::findOrFail($id);
        $categories = Category::where('status', 1)->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $childCategories = ChildCategory::where('sub_category_id', $product->sub_category_id)->get();
        $brands = Brand::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $colors = Color::where('status', 1)->get();
        $sizes = Size::where('status', 1)->get();
        return view('backend.product.edit', compact('product', 'categories', 'subCategories', 'childCategories', 'brands', 'units', 'colors', 'sizes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $imagePath = $this->update_image($request, 'image', 'uploads/products', $product->thumb_image);

        if ($request->hasFile('image')) {
             $product->thumb_image = $imagePath;
        }

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->category_id = $request->category_id;
        $product->sub_category_id = $request->sub_category_id;
        $product->child_category_id = $request->child_category_id;
        $product->brand_id = $request->brand_id;
        $product->vendor_id = $request->vendor_id;
        $product->unit_id = $request->unit_id;
        $product->product_number = $request->product_number;
        $product->qty = $request->qty ?? 0;
        $product->long_description = $request->long_description;
        $product->purchase_price = $request->purchase_price ?? 0;
        $product->price = $request->price ?? 0;
        $product->barcode = $request->barcode;
        $product->status = $request->status;
        $product->save();

        ProductVariant::where('product_id', $product->id)->delete();
         if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                 if(!empty($variant['color_id']) || !empty($variant['size_id'])) {
                    $productVariant = new ProductVariant();
                    $productVariant->product_id = $product->id;
                    $productVariant->color_id = $variant['color_id'] ?? null;
                    $productVariant->size_id = $variant['size_id'] ?? null;
                    
                    // Generate name for backward compatibility
                    $colorName = $productVariant->color_id ? Color::find($productVariant->color_id)->name : '';
                    $sizeName = $productVariant->size_id ? Size::find($productVariant->size_id)->name : '';
                    $productVariant->name = trim($colorName . ' ' . $sizeName);
                    
                    $productVariant->qty = $variant['qty'] ?? 0;
                    $productVariant->save();
                 }
            }
        }

        Toastr::success('Product Updated Successfully!');
        return redirect()->route('admin.products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $product = Product::findOrFail($id);
         $this->delete_image($product->thumb_image);
         $product->delete(); // Cascade delete variants
         return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->status = $request->status == 'true' ? 1 : 0;
        $product->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
