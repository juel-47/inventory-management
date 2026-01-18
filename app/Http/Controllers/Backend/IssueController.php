<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\InventoryStock;
use App\Models\StockLedger;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

use App\Models\ProductRequest;

class IssueController extends Controller
{
    public function index()
    {
        $issues = Issue::with('outlet')->latest()->get();
        return view('backend.issue.index', compact('issues'));
    }

    public function create(Request $request)
    {
        $products = Product::where('status', 1)
            ->with(['variants.color', 'variants.size', 'variants.inventoryStocks', 'inventoryStocks'])
            ->get();
            
        // Fetch requests that are pending or approved
        $productRequests = ProductRequest::with('user')
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->get();

        $requestId = $request->query('request_id');
            
        return view('backend.issue.create', compact('products', 'productRequests', 'requestId'));
    }

    public function getRequestItems(Request $request)
    {
        $productRequest = ProductRequest::with(['items.product', 'items.variant.color', 'items.variant.size'])->findOrFail($request->request_id);
        
        $items = $productRequest->items->map(function($item) {
            // Get current warehouse stock for validation in UI
            $stock = InventoryStock::where([
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'outlet_id' => 1
            ])->first();

            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'thumb_image' => $item->product->thumb_image,
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant ? $item->variant->name : null,
                'color_name' => $item->variant && $item->variant->color ? $item->variant->color->name : null,
                'size_name' => $item->variant && $item->variant->size ? $item->variant->size->name : null,
                'requested_qty' => $item->qty,
                'available_stock' => $stock ? $stock->quantity : 0,
            ];
        });

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $issue = Issue::create([
                'issue_no' => 'ISS-' . strtoupper(uniqid()),
                'product_request_id' => $request->product_request_id,
                'outlet_id' => 1, // Default outlet for now
                'status' => 'confirmed',
                'total_qty' => collect($request->items)->sum('quantity'),
                'note' => $request->note,
            ]);

            // If this issue is linked to a product request, update its status
            if ($request->product_request_id) {
                $productRequest = ProductRequest::find($request->product_request_id);
                if ($productRequest) {
                    $productRequest->update([
                        'status' => 'completed',
                        'admin_note' => $productRequest->admin_note . "\nStock Issued: " . $issue->issue_no
                    ]);
                }
            }

            foreach ($request->items as $item) {
                // 1. Create Issue Item
                IssueItem::create([
                    'issue_id' => $issue->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?: null,
                    'quantity' => $item['quantity'],
                ]);

                // 2. Update Inventory Stock
                $stock = InventoryStock::firstOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'] ?: null,
                        'outlet_id' => 1
                    ]
                );
                $stock->decrement('quantity', $item['quantity']);

                // 3. (REMOVED) Update Product/Variant Master Qty - Relying on InventoryStock instead
                
                // 4. Create Stock Ledger Entry
                StockLedger::create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'outlet_id' => 1,
                    'reference_type' => 'issue',
                    'reference_id' => $issue->id,
                    'in_qty' => 0,
                    'out_qty' => $item['quantity'],
                    'balance_qty' => $stock->quantity, // Post-decrement balance
                    'date' => now()
                ]);
            }
        });

        return redirect()->route('admin.issues.index')->with('success', 'Stock Issued Successfully!');
    }

    public function show($id)
    {
        $issue = Issue::with(['items.product', 'items.variant.color', 'items.variant.size'])->findOrFail($id);
        return view('backend.issue.show', compact('issue'));
    }
}
