<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('medicines', MedicineController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('prescriptions', PrescriptionController::class);
    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show']);
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
    Route::post('orders/{order}/receive', [OrderController::class, 'receive']);
    Route::get('stocks', [StockController::class, 'index']);
    Route::post('stocks', [StockController::class, 'store']);
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('alerts', [AlertController::class, 'index']);
});
