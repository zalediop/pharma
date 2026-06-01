<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Sale::whereDate('created_at', today())->count();
        $weekSales = Sale::whereBetween('created_at', [now()->startOfWeek(), now()])->count();
        $monthSales = Sale::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
        $revenue = Sale::sum('total_amount');

        $topMedicines = DB::table('sale_items')
            ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id')
            ->select('medicines.id', 'medicines.name', DB::raw('SUM(sale_items.quantity) as total_sold'))
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $expiringSoon = Stock::whereBetween('expiry_date', [now(), now()->addDays(30)])->distinct('medicine_id')->count('medicine_id');

        return response()->json([
            'today_sales' => $todaySales,
            'week_sales' => $weekSales,
            'month_sales' => $monthSales,
            'revenue' => $revenue,
            'top_medicines' => $topMedicines,
            'expiring_soon' => $expiringSoon,
        ]);
    }
}
