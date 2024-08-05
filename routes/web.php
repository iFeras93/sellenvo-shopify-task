<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products-list', [ProductController::class, 'getProductsList']);
Route::get('/products-store', [ProductController::class, 'handleStoringProduct']);
