<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);

Route::get('/shop', fn() => response()->json(config('shop')));

Route::middleware('web')->group(function () {
    Route::get('/session', [AuthController::class, 'session']);
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:8,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::middleware('auth')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:20,1');
        Route::get('/orders/{number}', [OrderController::class, 'show']);
        Route::post('/orders/{number}/cancel', [OrderController::class, 'cancel']);
        Route::patch('/profile', [AuthController::class, 'update']);
        Route::put('/password', [AuthController::class, 'password'])->middleware('throttle:5,1');
    });
});

Route::prefix('admin')->middleware(['web', 'auth', EnsureAdmin::class])->group(function () {
    Route::get('/summary', [App\Http\Controllers\Admin\OrderController::class, 'summary']);
    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index']);
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store']);
    Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update']);
    Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy']);
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index']);
    Route::patch('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'update']);
});
