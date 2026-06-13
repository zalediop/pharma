<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::whereHas('medicine', fn ($q) => $q->where('archived', false))
            ->with(['medicine'])
            ->latest();

        if ($search = $request->query('search')) {
            $query->whereHas('medicine', fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
            );
        }

        $limit = min((int) $request->query('limit', 20), 1000);
        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => ['required', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'expiry_date' => ['required', 'date', 'after:today'],
            'alert_threshold' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['alert_threshold'] = $data['alert_threshold'] ?? 10;

        return response()->json(Stock::create($data), 201);
    }
}
