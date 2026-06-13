<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = [];

        $stocks = Stock::whereHas('medicine', function ($query) {
            $query->where('archived', false);
        })->with('medicine')->get();

        foreach ($stocks as $stock) {
            $medicineName = optional($stock->medicine)->name ?? 'Médicament inconnu';

            if ($stock->quantity <= $stock->alert_threshold) {
                $alerts[] = [
                    'id' => "low-stock-{$stock->id}",
                    'message' => "Stock bas pour {$medicineName} ({$stock->quantity} restants).",
                ];
            }

            if ($stock->expiry_date && $stock->expiry_date->isBefore(now()->addDays(30))) {
                $alerts[] = [
                    'id' => "expiry-{$stock->id}",
                    'message' => "{$medicineName} expire le {$stock->expiry_date->format('d/m/Y')}.",
                ];
            }
        }

        return response()->json($alerts);
    }
}
