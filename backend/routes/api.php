<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TableController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'show']);

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::middleware(['throttle:60,1', 'role:ADMIN,MANAGER,CASHIER,WAITER'])->group(function () {
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);

        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);

        Route::get('/tables', [TableController::class, 'index']);
        Route::get('/commands', [CommandController::class, 'index']);
        Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/items', [OrderController::class, 'addItem']);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'removeItem']);
    });

    Route::middleware(['throttle:60,1', 'role:ADMIN,MANAGER'])->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    Route::middleware(['throttle:60,1', 'role:ADMIN,MANAGER,WAITER'])->group(function () {
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::patch('/customers/{customer}', [CustomerController::class, 'update']);
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);

        Route::post('/tables', [TableController::class, 'store']);
        Route::patch('/tables/{table}', [TableController::class, 'update']);
        Route::patch('/tables/{table}/status', [TableController::class, 'updateStatus']);

        Route::post('/commands', [CommandController::class, 'store']);
        Route::patch('/commands/{command}/status', [CommandController::class, 'updateStatus']);
    });

    Route::middleware(['throttle:60,1', 'role:ADMIN,MANAGER,CASHIER'])->group(function () {
        Route::post('/cash/open', [CashController::class, 'open']);
        Route::get('/cash/current', [CashController::class, 'current']);
        Route::post('/cash/movements', [CashController::class, 'movement']);
        Route::get('/cash/history', [CashController::class, 'history']);
        Route::post('/cash/close', [CashController::class, 'close']);

        Route::post('/orders/{order}/payments', [PaymentController::class, 'store']);
    });
});
