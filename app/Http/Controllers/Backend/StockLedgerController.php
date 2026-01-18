<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockLedger;

class StockLedgerController extends Controller
{
    public function index()
    {
        $ledgers = StockLedger::with(['product', 'variant'])->latest()->get();
        return view('backend.stock_ledger.index', compact('ledgers'));
    }
}
