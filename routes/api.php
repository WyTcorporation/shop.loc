<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    CategoryController,
    ProductController,
    CartController,
    OrderController
};

//Categories
Route::get('categories', [CategoryController::class,'index']);

// Products
Route::get('products', [ProductController::class, 'index']);
Route::get('products/facets', [ProductController::class, 'facets']);
Route::get('products/{slug}', [ProductController::class, 'show']);

// Cart
Route::get('cart', [CartController::class, 'getOrCreate']);
Route::get('cart/{id}', [CartController::class, 'show']);
Route::post('cart/{id}/items', [CartController::class, 'addItem']);
Route::patch('cart/{id}/items/{item}', [CartController::class, 'updateItem']);
Route::delete('cart/{id}/items/{item}', [CartController::class, 'removeItem']);

// Checkout
Route::post('orders', [OrderController::class, 'store']);
Route::get('/orders/{number}', [OrderController::class, 'show']);
