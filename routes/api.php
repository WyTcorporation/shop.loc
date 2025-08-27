<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\OrderController;

//Categories
Route::get('categories', [CategoryController::class,'index']);

// Products
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{slug}', [ProductController::class, 'show']);

// Cart
Route::get('cart', [CartController::class, 'getOrCreate']);
Route::post('cart', [CartController::class, 'store']);
Route::get('cart/{id}', [CartController::class, 'show']);
Route::patch('cart/{cart}/items/{item}', [CartController::class, 'updateItem']);

// Cart items
Route::post('cart/{id}/items', [CartItemController::class, 'store']);
Route::delete('cart/{id}/items/{itemId}', [CartItemController::class, 'destroy']);

// Checkout
Route::post('orders', [OrderController::class, 'store']);
