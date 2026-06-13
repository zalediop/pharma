<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $limit = min((int) $request->query('limit', 20), 1000);
        return response()->json(PurchaseOrder::with(['supplier', 'items.medicine'])->latest()->paginate($limit));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $order = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'status' => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            DB::commit();

            return response()->json($order->load(['supplier', 'items.medicine']), 201);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json(['message' => 'Impossible de créer la commande.'], 500);
        }
    }

    public function show(PurchaseOrder $order)
    {
        return response()->json($order->load(['supplier', 'items.medicine']));
    }

    public function receive(Request $request, PurchaseOrder $order)
    {
        if ($order->status === 'received') {
            return response()->json(['message' => 'Commande déjà réceptionnée.'], 422);
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.expiry_date' => ['required', 'date', 'after:today'],
        ]);

        DB::beginTransaction();

        try {
            foreach ($data['items'] as $item) {
                $stock = Stock::where('medicine_id', $item['medicine_id'])
                    ->where('expiry_date', $item['expiry_date'])
                    ->first();

                if ($stock) {
                    $stock->increment('quantity', $item['quantity']);
                    $stock->update(['alert_threshold' => max($stock->alert_threshold, 0)]);
                } else {
                    Stock::create([
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                        'expiry_date' => $item['expiry_date'],
                        'alert_threshold' => 0,
                    ]);
                }
            }

            $order->update(['status' => 'received', 'received_at' => now()]);
            DB::commit();

            return response()->json($order->load(['supplier', 'items.medicine']));
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json(['message' => 'Erreur lors de la réception de la commande.'], 500);
        }
    }
}
