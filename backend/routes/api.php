<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardMetricsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RestaurantTableController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/customers', [CustomerController::class, 'store']);



Route::middleware(['token.valid', 'auth.system:ADMIN,MANAGER,OPERATOR'])->group(function () {
    Route::get('/dashboard/metrics', DashboardMetricsController::class);

    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'get']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::patch('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'deleted']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/quick-create/options', [ProductController::class, 'quickCreateOptions']);
        Route::get('/', [ProductController::class, 'get']);
        Route::get('/active', [ProductController::class, 'getActive']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'post']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::patch('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'deleted']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/management', [OrderController::class, 'management']);
        Route::get('/', [OrderController::class, 'get']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::get('/{id}/timeline', [OrderController::class, 'timeline']);
        Route::post('/', [OrderController::class, 'post']);
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::patch('/{id}', [OrderController::class, 'update']);
        Route::patch('/{id}/associate-customer', [OrderController::class, 'associateCustomer']);
        Route::patch('/{id}/status', [OrderController::class, 'changeStatus']);
        Route::delete('/{id}', [OrderController::class, 'deleted']);
    });

    Route::prefix('cash')->group(function () {
        Route::post('/open', [CashRegisterController::class, 'open']);
        Route::post('/transaction', [CashRegisterController::class, 'transaction']);
        Route::post('/close', [CashRegisterController::class, 'close']);
        Route::get('/history', [CashRegisterController::class, 'history']);
        Route::get('/current', [CashRegisterController::class, 'current']);
    });

    Route::prefix('tables')->group(function () {
        Route::get('/', [RestaurantTableController::class, 'index']);
        Route::post('/', [RestaurantTableController::class, 'store']);
        Route::patch('/{id}/status', [RestaurantTableController::class, 'updateStatus']);
    });

    Route::prefix('commands')->group(function () {
        Route::get('/', [CommandController::class, 'index']);
        Route::post('/', [CommandController::class, 'store']);
        Route::patch('/{id}/status', [CommandController::class, 'updateStatus']);
    });

    Route::prefix('recipes')->group(function () {
        Route::get('/', [RecipeController::class, 'listRecipes']);
        Route::post('/', [RecipeController::class, 'storeRecipe']);
        Route::post('/{recipeId}/items', [RecipeController::class, 'addRecipeItem']);
        Route::post('/{recipeId}/consume', [RecipeController::class, 'consume']);
    });

    Route::prefix('ingredients')->group(function () {
        Route::get('/', [RecipeController::class, 'listIngredients']);
        Route::post('/', [RecipeController::class, 'storeIngredient']);
    });
});
