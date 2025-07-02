<?php

use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\ProductsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductsController::class, 'search']);
Route::get('/{product}', [ProductsController::class, 'show'])->name('products.show');
Route::get('/proxy-image', [ImageProxyController::class, 'proxyImage']);
Route::get('/products/{product}', [ProductsController::class, 'show'])
    ->name('products.show');
