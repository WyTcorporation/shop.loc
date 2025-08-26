<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\OrderController;

// Products
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{slug}', [ProductController::class, 'show']);

// Cart
Route::post('cart', [CartController::class, 'store']);
Route::get('cart/{id}', [CartController::class, 'show']);

// Cart items
Route::post('cart/{id}/items', [CartItemController::class, 'store']);
Route::delete('cart/{id}/items/{itemId}', [CartItemController::class, 'destroy']);

// Checkout
Route::post('orders', [OrderController::class, 'store']);
