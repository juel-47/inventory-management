<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:Manage Reports'),
        ];
    }

    /**
     * Reports Dashboard
     */
    public function index()
    {
        // 1. Total Stock Value: Sum (Stock Qty * Purchase Price)
        // We join with inventory_stocks to get the actual quantity
        $totalStockValue = DB::table('inventory_stocks')
            ->join('products', 'inventory_stocks.product_id', '=', 'products.id')
            ->where('products.status', 1)
            ->sum(DB::raw('inventory_stocks.quantity * products.purchase_price'));
        
        $totalProducts = Product::where('status', 1)->count();
        
        $lowStockCount = Product::where('status', 1)
            ->withSum('inventoryStocks', 'quantity')
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL')
            ->get()
            ->count();
        
        // 2. Total Revenue: From completed Product Requests
        $totalRevenue = ProductRequest::where('status', 'completed')->sum('total_amount');
        
        // 3. COGS: Estimated from ProductRequestItems for completed requests
        $totalCost = DB::table('product_request_items')
            ->join('product_requests', 'product_request_items.product_request_id', '=', 'product_requests.id')
            ->join('products', 'product_request_items.product_id', '=', 'products.id')
            ->where('product_requests.status', 'completed')
            ->sum(DB::raw('product_request_items.qty * products.purchase_price'));

        $grossProfit = $totalRevenue - $totalCost;

        // Current Month Purchases for Context
        $monthlyPurchases = Purchase::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('total_amount');

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
        $query = Product::with(['category', 'unit', 'brand', 'inventoryStocks'])
            ->where('status', 1);

        // Filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->get();

        // Calculate Summary Stats from filtered results
        $products->map(function($product) {
            $product->stock_qty = $product->inventoryStocks->sum('quantity');
            return $product;
        });

        $totalQty = $products->sum('stock_qty');
        
        $totalValue = $products->sum(function($product) {
            return $product->stock_qty * $product->purchase_price;
        });
        $potentialRevenue = $products->sum(function($product) {
            return $product->stock_qty * $product->price;
        });
        $potentialProfit = $potentialRevenue - $totalValue;
        
        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();
        $settings = GeneralSetting::first();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backend.reports.partials.stock_table_rows', compact('products', 'settings'))->render(),
                'totalQty' => number_format($totalQty),
                'totalValue' => $settings->currency_icon . number_format($totalValue, 2),
                'potentialRevenue' => $settings->currency_icon . number_format($potentialRevenue, 2),
                'potentialProfit' => $settings->currency_icon . number_format($potentialProfit, 2),
            ]);
        }

        return view('backend.reports.stock', compact('products', 'categories', 'brands', 'totalQty', 'totalValue', 'potentialRevenue', 'potentialProfit', 'settings'));
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
        $vendors = Vendor::where('status', 1)->get();

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
            ->withSum('inventoryStocks', 'quantity')
            ->where('status', 1)
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL')
            ->orderBy('inventory_stocks_sum_quantity', 'asc')
            ->get();

        return view('backend.reports.low_stock', compact('products'));
    }

    /**
     * AJAX Endpoint for Combined Alerts
     */
    public function lowStockCheck()
    {
        $data = $this->getNotificationData();
        return response()->json($data);
    }

    /**
     * View all notifications page
     */
    public function allNotifications()
    {
        $data = $this->getNotificationData();
        return view('backend.notifications.all', ['notifications' => $data['notifications']]);
    }

    /**
     * Mark all notifications as read
     */
    public function markNotificationsRead()
    {
        session(['notifications_read_at' => now()]);
        return response()->json(['status' => 'success']);
    }

    /**
     * Helper to gather notification data
     */
    private function getNotificationData()
    {
        $notifications = [];
        $lastReadAt = session('notifications_read_at');
        $unreadCount = 0;
        
        // 1. Fetch Low Stock Products (Threshold 100)
        $lowStockProducts = Product::where('status', 1)
            ->withSum('inventoryStocks', 'quantity')
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL')
            ->orderBy('inventory_stocks_sum_quantity', 'asc')
            ->take(20) // More for the full page
            ->get();

        foreach ($lowStockProducts as $product) {
            $isUnread = !$lastReadAt || $product->updated_at->gt($lastReadAt);
            if ($isUnread) $unreadCount++;

            $notifications[] = [
                'type' => 'lowStock',
                'title' => $product->name,
                'desc' => ($product->inventory_stocks_sum_quantity ?? 0) . ' in stock',
                'time' => $product->updated_at->diffForHumans(),
                'url' => route('admin.reports.low-stock'),
                'icon' => 'fas fa-exclamation-triangle',
                'class' => ($product->inventory_stocks_sum_quantity == 0) ? 'bg-danger' : 'bg-warning',
                'is_unread' => $isUnread
            ];
        }

        // 2. Fetch Pending Product Requests (Admin only)
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->can('Manage Product Requests')) {
            $pendingRequests = ProductRequest::with('user')
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->take(20)
                ->get();

            foreach ($pendingRequests as $req) {
                $isUnread = !$lastReadAt || $req->created_at->gt($lastReadAt);
                if ($isUnread) $unreadCount++;

                $userName = $req->user ? $req->user->name : 'Unknown User';
                $notifications[] = [
                    'type' => 'request',
                    'title' => 'New Request: ' . $req->request_no,
                    'desc' => 'From ' . $userName . ' (Qty: ' . $req->total_qty . ')',
                    'time' => $req->created_at->diffForHumans(),
                    'url' => route('admin.product-requests.index'),
                    'icon' => 'fas fa-box-open',
                    'class' => 'bg-info',
                    'is_unread' => $isUnread
                ];
            }
        }

        return [
            'count' => $unreadCount,
            'notifications' => $notifications
        ];
    }

    /**
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request)
    {
        $revenueQuery = ProductRequest::where('status', 'completed');
        $purchasesQuery = Purchase::query();
        
        if ($request->start_date) {
            $revenueQuery->where('created_at', '>=', $request->start_date . ' 00:00:00');
            $purchasesQuery->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $revenueQuery->where('created_at', '<=', $request->end_date . ' 23:59:59');
            $purchasesQuery->where('date', '<=', $request->end_date);
        }

        // Calculate Revenue
        $totalRevenue = $revenueQuery->sum('total_amount');

        // Calculate COGS for those completed requests
        $completedRequestIds = $revenueQuery->pluck('id');
        
        $totalCost = DB::table('product_request_items')
            ->join('products', 'product_request_items.product_id', '=', 'products.id')
            ->whereIn('product_request_items.product_request_id', $completedRequestIds)
            ->sum(DB::raw('product_request_items.qty * products.purchase_price'));

        // Calculate Profit
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        
        $totalPurchases = $purchasesQuery->sum('total_amount');

        return view('backend.reports.profit_loss', compact(
            'totalRevenue',
            'totalCost',
            'grossProfit',
            'profitMargin',
            'totalPurchases'
        ));
    }

}
