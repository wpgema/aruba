<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ProductStockController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SaleDetailController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthController;

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::apiResource('users', UserController::class);
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    
    // Master data
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('employees', EmployeeController::class);
    
    // Product stock management
    Route::apiResource('product-stocks', ProductStockController::class);
    Route::get('products/{product_id}/stock', [ProductStockController::class, 'index']);
    
    // Sales management
    Route::apiResource('sales', SaleController::class);
    Route::get('sales-report', [SaleController::class, 'getSalesReport']);
    
    // Sale details management
    Route::apiResource('sale-details', SaleDetailController::class);
});