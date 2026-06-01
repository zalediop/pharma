<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = [];

        $stocks = Stock::with('medicine')->get();

        foreach ($stocks as $stock) {
            if ($stock->quantity <= $stock->alert_threshold) {
                $alerts[] = [
                    'id' => "low-stock-{$stock->id}",
                    'message' => "Stock bas pour {$stock->medicine->name} ({$stock->quantity} restants).",
                ];
            }

            if ($stock->expiry_date && $stock->expiry_date->isBefore(now()->addDays(30))) {
                $alerts[] = [
                    'id' => "expiry-{$stock->id}",
                    'message' => "{$stock->medicine->name} expire le {$stock->expiry_date->format('d/m/Y')}.",
                ];
            }
        }

        return response()->json($alerts);
    }
}
