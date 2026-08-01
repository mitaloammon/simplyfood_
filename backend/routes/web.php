<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['token.valid', 'auth.system:ADMIN,MANAGER,OPERATOR'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});
