<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductQuickCreatePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['token.valid', 'auth.system:ADMIN,MANAGER,OPERATOR'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/products/quick-create', ProductQuickCreatePageController::class)->name('products.quick-create');
});
