<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $limit = min((int) $request->query('limit', 20), 1000);
        return response()->json(Sale::with(['customer', 'prescription', 'user', 'items.medicine'])->latest()->paginate($limit));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'prescription_id' => ['nullable', 'exists:prescriptions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $totalAmount = 0;

        DB::beginTransaction();

        try {
            $sale = Sale::create([
                'customer_id' => $data['customer_id'],
                'prescription_id' => $data['prescription_id'] ?? null,
                'user_id' => $request->user()->id,
                'total_amount' => 0,
            ]);

            foreach ($data['items'] as $item) {
                $quantity = $item['quantity'];
                $medicineId = $item['medicine_id'];

                $available = Stock::where('medicine_id', $medicineId)->sum('quantity');

                if ($available < $quantity) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Stock insuffisant pour le médicament.',
                        'medicine_id' => $medicineId,
                    ], 422);
                }

                $remaining = $quantity;
                $stocks = Stock::where('medicine_id', $medicineId)->where('quantity', '>', 0)->orderBy('expiry_date')->get();

                foreach ($stocks as $stock) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $deduct = min($stock->quantity, $remaining);
                    $stock->decrement('quantity', $deduct);
                    $remaining -= $deduct;
                }

                $amount = $quantity * $item['unit_price'];
                $totalAmount += $amount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'medicine_id' => $medicineId,
                    'quantity' => $quantity,
                    'unit_price' => $item['unit_price'],
                ]);
            }

            $sale->update(['total_amount' => $totalAmount]);
            DB::commit();

            return response()->json($sale->load(['customer', 'prescription', 'items.medicine']), 201);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json(['message' => 'Erreur lors de l’enregistrement de la vente.'], 500);
        }
    }

    public function show(Sale $sale)
    {
        return response()->json($sale->load(['customer', 'prescription', 'items.medicine', 'user']));
    }
}
