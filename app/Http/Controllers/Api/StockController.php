<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => ['required', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'expiry_date' => ['required', 'date', 'after:today'],
            'alert_threshold' => ['required', 'integer', 'min:0'],
        ]);

        return response()->json(Stock::create($data), 201);
    }
}
