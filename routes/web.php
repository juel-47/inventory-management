<?php

use App\Http\Controllers\Backend\BookingController;
use App\Http\Controllers\Backend\BrandController;
// use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\SubCategoryController;
use App\Http\Controllers\Backend\ChildCategoryController;
use App\Http\Controllers\Backend\ColorController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\InventoryReportController;
use App\Http\Controllers\Backend\IssueController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProductRequestController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\PurchaseController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\RolesController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\SizeController;
use App\Http\Controllers\Backend\StockLedgerController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth/login');
})->middleware('guest');

// Route::get('/dashboard', function () {
//     return view('backend.dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });


Route::group(['middleware' => ['auth', 'verified', 'check.permission'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::put('users/change-status', [UserController::class, 'changeStatus'])->name('users.change-status');
    Route::resource('role', RolesController::class);
    Route::resource('permission', PermissionController::class);

    /** category routes */
    Route::put('category/change-status', [CategoryController::class, 'changeStatus'])->name('category.change-status');
    Route::resource('category', CategoryController::class);

    /** subcategory routes */
    Route::put('subcategory/change-status', [SubCategoryController::class, 'changeStatus'])->name('subcategory.change-status');
    Route::resource('sub-category', SubCategoryController::class);

    /** child category routes */
    Route::controller(ChildCategoryController::class)->group(function () {
        Route::put('child-category/change-status', 'changeStatus')->name('child-category.change-status');
        Route::get('get-subcategories', 'getSubCategories')->name('get-subCategories');
        Route::get('get-child-categories', 'getChildCategories')->name('get-child-categories');
    });
    Route::resource('child-category', ChildCategoryController::class);
    /* brand controller */
    Route::put('brand/change-status', [BrandController::class, 'changeStatus'])->name('brand.change-status');
    Route::resource('brand', BrandController::class);


    /** vendor */
    Route::get('vendor/get-details', [VendorController::class, 'getVendorDetails'])->name('vendor.get-details');
    Route::put('vendor/change-status', [VendorController::class, 'changeStatus'])->name('vendor.change-status');
    Route::resource('vendor', VendorController::class);

    /** Unit Routes */
    Route::put('units/change-status', [UnitController::class, 'changeStatus'])->name('units.change-status');
    Route::resource('units', UnitController::class);

    /** Color Routes */
    Route::put('colors/change-status', [ColorController::class, 'changeStatus'])->name('colors.change-status');
    Route::resource('colors', ColorController::class);

    /** Size Routes */
    Route::put('sizes/change-status', [SizeController::class, 'changeStatus'])->name('sizes.change-status');
    Route::resource('sizes', SizeController::class);

    /** Product Routes */
    Route::put('products/change-status', [ProductController::class, 'changeStatus'])->name('products.change-status');
    Route::resource('products', ProductController::class);

    /** Booking Routes */
    Route::controller(BookingController::class)->group(function () {
        Route::get('bookings/get-subcategories', 'getSubCategories')->name('bookings.get-subcategories');
        Route::get('bookings/get-childcategories', 'getChildCategories')->name('bookings.get-childcategories');
        Route::get('bookings/view-invoice/{id}', 'viewInvoice')->name('bookings.view-invoice');
        Route::get('bookings/download-pdf/{id}', 'downloadPdf')->name('bookings.download-pdf');
    });
    // New route (Primary)
    Route::put('bookings/status-update', [BookingController::class, 'changeStatus'])->name('bookings.status-update');
    // Legacy route (Fallback to prevent RouteNotFoundException)
    Route::put('bookings/change-status', [BookingController::class, 'changeStatus'])->name('bookings.change-status');
    
    Route::resource('bookings', BookingController::class);

    /** Purchase Routes */
    Route::get('purchases/get-booking-details', [PurchaseController::class, 'getBookingDetails'])->name('purchases.get-booking-details');
    Route::get('purchases/{id}/invoice', [PurchaseController::class, 'viewInvoice'])->name('purchases.view-invoice');
    Route::get('purchases/{id}/download-pdf', [PurchaseController::class, 'downloadPdf'])->name('purchases.download-pdf');
    Route::resource('purchases', PurchaseController::class);

    /** Report Routes */
    Route::controller(ReportController::class)->group(function () {
        Route::get('reports', 'index')->name('reports.index');
        Route::get('reports/stock', 'stockReport')->name('reports.stock');
        Route::get('reports/purchase', 'purchaseReport')->name('reports.purchase');
        Route::get('reports/product-purchase-history', 'productPurchaseHistory')->name('reports.product-purchase-history');
        Route::get('reports/low-stock', 'lowStockReport')->name('reports.low-stock');
        Route::get('reports/profit-loss', 'profitLossReport')->name('reports.profit-loss');
        Route::get('low-stock-check', 'lowStockCheck')->name('low-stock-check'); // AJAX endpoint
        Route::post('low-stock-mark-read', 'markNotificationsRead')->name('low-stock-mark-read');
        Route::get('notifications/all', 'allNotifications')->name('notifications.all');
    });


    /** Product Request Routes */
    Route::get('product-requests/{id}/view-invoice', [ProductRequestController::class, 'viewInvoice'])->name('product-requests.view-invoice');
    Route::get('product-requests/{id}/invoice', [ProductRequestController::class, 'printPdf'])->name('product-requests.download-invoice');
    Route::put('product-requests/update-status/{id}', [ProductRequestController::class, 'updateStatus'])->name('product-requests.update-status');
    Route::resource('product-requests', ProductRequestController::class);

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    /** Inventory Plane Routes */
    Route::get('issues/get-request-items', [IssueController::class, 'getRequestItems'])->name('issues.get-request-items');
    Route::get('issues/{id}/view-invoice', [IssueController::class, 'viewInvoice'])->name('issues.view-invoice');
    Route::get('issues/{id}/invoice', [IssueController::class, 'downloadInvoice'])->name('issues.download-invoice');
    Route::resource('issues', IssueController::class);
    Route::get('stock-ledger', [StockLedgerController::class, 'index'])->name('stock-ledger.index');
    Route::get('inventory-reports', [InventoryReportController::class, 'index'])->name('inventory-reports.index');

    /** profile routes */
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::post('/profile/update', 'updateProfile')->name('profile.update');
        Route::post('/profile/update/password', 'updatePassword')->name('password.update');
    });
});

require __DIR__ . '/auth.php';
