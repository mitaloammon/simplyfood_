<?php
use Illuminate\Support\Facades\Route;
Route::get('/', fn () => ['status' => 'success', 'data' => ['app' => 'SimplyFood'], 'message' => 'OK']);
