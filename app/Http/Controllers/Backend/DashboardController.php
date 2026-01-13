<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            // Admin Global Stats
            $totalProducts = Product::where('status', 1)->count();
            $totalSales = Sale::sum('total_amount');
            $pendingRequests = ProductRequest::where('status', 'pending')->count();
            $totalOutlets = User::role('Outlet User')->count();
            
            // Recent Requests for Admin
            $recentRequests = ProductRequest::with('user')->orderBy('id', 'desc')->take(5)->get();

            // Chart Data: Monthly Sales (Last 12 Months)
            $monthlySales = Sale::select(
                DB::raw('SUM(total_amount) as total'),
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month_year"),
                DB::raw("DATE_FORMAT(date, '%M') as month_name")
            )
            ->where('date', '>=', now()->subMonths(11))
            ->groupBy('month_year', 'month_name')
            ->orderBy('month_year')
            ->get();

            $salesLabels = $monthlySales->pluck('month_name');
            $salesData = $monthlySales->pluck('total');

            // Chart Data: Request Status Distribution
            $requestStatus = ProductRequest::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');
            
            // Ensure all statuses are present for consistent coloring
            $statuses = ['pending', 'approved', 'rejected'];
            $statusData = [];
            foreach ($statuses as $status) {
                $statusData[] = $requestStatus[$status] ?? 0;
            }
            
            return view('backend.dashboard', compact(
                'totalProducts', 
                'totalSales', 
                'pendingRequests', 
                'totalOutlets',
                'recentRequests',
                'salesLabels',
                'salesData',
                'statusData'
            ));
        } else {
            // Outlet Specific Stats
            $myTotalRequests = ProductRequest::where('user_id', $user->id)->count();
            $myPendingRequests = ProductRequest::where('user_id', $user->id)->where('status', 'pending')->count();
            $myTotalSpent = ProductRequest::where('user_id', $user->id)
                ->where('status', 'approved') // Or however we define 'charged'
                ->sum('total_amount');
            
            $recentRequests = ProductRequest::where('user_id', $user->id)->orderBy('id', 'desc')->take(5)->get();

            return view('backend.dashboard', compact(
                'myTotalRequests',
                'myPendingRequests',
                'myTotalSpent',
                'recentRequests'
            ));
        }
    }
}
