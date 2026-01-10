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
            
            return view('backend.dashboard', compact(
                'totalProducts', 
                'totalSales', 
                'pendingRequests', 
                'totalOutlets',
                'recentRequests'
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
