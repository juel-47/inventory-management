<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'thumb_image',
        'category_id',
        'sub_category_id',
        'child_category_id',
        'brand_id',
        'unit_id',
        'product_number',
        'sku',
        'qty',
        'long_description',
        'purchase_price',
        'price',
        'outlet_price',
        'barcode',
        'status',
        'self_number',
        'raw_material_cost',
        'transport_cost',
        'tax',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function childCategory()
    {
        return $this->belongsTo(ChildCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function getTotalAttribute()
    {
        // Return latest purchase total safely using collection
        return optional($this->purchaseDetails->last())->total ?? 0;
    }

    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class, 'variant_id');
    }

    public function getInventoryStockAttribute()
    {
        return $this->inventoryStocks->sum('quantity');
    }
}
