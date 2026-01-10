<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:Admin'),
        ];
    }

    /**
     * Reports Dashboard
     */
    public function index()
    {
        // Summary Stats for Dashboard
        $totalStockValue = Product::where('status', 1)
            ->sum(DB::raw('qty * purchase_price'));
        
        $totalProducts = Product::where('status', 1)->count();
        
        $lowStockCount = Product::where('status', 1)
            ->whereColumn('qty', '<', 'min_inventory_qty')
            ->count();
        
        $monthlyPurchases = Purchase::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('total_amount');

        $totalRevenue = Sale::sum('total_amount');
        $totalCost = Purchase::sum('total_amount');
        $grossProfit = $totalRevenue - $totalCost;

        return view('backend.reports.index', compact(
            'totalStockValue',
            'totalProducts',
            'lowStockCount',
            'monthlyPurchases',
            'totalRevenue',
            'grossProfit'
        ));
    }

    /**
     * Stock Valuation Report
     */
    public function stockReport(Request $request)
    {
        $query = Product::with(['category', 'unit', 'brand'])
            ->where('status', 1);

        // Filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->get();
        
        $categories = \App\Models\Category::where('status', 1)->get();
        $brands = \App\Models\Brand::where('status', 1)->get();

        return view('backend.reports.stock', compact('products', 'categories', 'brands'));
    }

    /**
     * Purchase History Report
     */
    public function purchaseReport(Request $request)
    {
        $query = Purchase::with(['vendor', 'user', 'details']);

        // Date Range Filter
        if ($request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }

        // Vendor Filter
        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $purchases = $query->orderBy('date', 'desc')->get();
        $vendors = \App\Models\Vendor::where('status', 1)->get();

        return view('backend.reports.purchase', compact('purchases', 'vendors'));
    }

    /**
     * Product-wise Purchase History (Track same product from different vendors)
     */
    public function productPurchaseHistory(Request $request)
    {
        $query = PurchaseDetail::with(['product', 'purchase.vendor', 'purchase.user']);

        // Product Filter
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        $details = $query->orderBy('id', 'desc')->get();
        $products = Product::where('status', 1)->get();

        return view('backend.reports.product_purchase_history', compact('details', 'products'));
    }

    /**
     * Low Stock Alert Report
     */
    public function lowStockReport()
    {
        $products = Product::with(['category', 'unit'])
            ->where('status', 1)
            ->whereColumn('qty', '<', 'min_inventory_qty')
            ->orderBy('qty', 'asc')
            ->get();

        return view('backend.reports.low_stock', compact('products'));
    }

    /**
     * AJAX Endpoint for Low Stock Check
     */
    public function lowStockCheck()
    {
        $count = Product::where('status', 1)
            ->whereColumn('qty', '<', 'min_inventory_qty')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request)
    {
        // Calculate Revenue from Sales
        $salesQuery = \App\Models\Sale::query();
        if ($request->start_date) {
            $salesQuery->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $salesQuery->where('date', '<=', $request->end_date);
        }
        $totalRevenue = $salesQuery->sum('total_amount');

        // Calculate Cost from Purchases
        $purchasesQuery = Purchase::query();
        if ($request->start_date) {
            $purchasesQuery->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $purchasesQuery->where('date', '<=', $request->end_date);
        }
        $totalCost = $purchasesQuery->sum('total_amount');

        // Calculate Profit
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return view('backend.reports.profit_loss', compact(
            'totalRevenue',
            'totalCost',
            'grossProfit',
            'profitMargin'
        ));
    }
}
