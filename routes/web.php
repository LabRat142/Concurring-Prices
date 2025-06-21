<?php

use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\ProductsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductsController::class, 'search']);
Route::get('/proxy-image', [ImageProxyController::class, 'proxyImage']);
