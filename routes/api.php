<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{CategoryController,
    PaymentController,
    ProductController,
    CartController,
    OrderController,
    ReviewController,
    WishlistController};

//Categories
Route::get('categories', [CategoryController::class,'index']);

// Products
Route::get('products', [ProductController::class, 'index']);
Route::get('products/facets', [ProductController::class, 'facets']);
Route::get('products/{slug}', [ProductController::class, 'show']);
Route::get('products/{id}/reviews', [ReviewController::class, 'index']);
Route::middleware('auth:sanctum')->post('products/{id}/reviews', [ReviewController::class, 'store']);

// Cart
Route::get('cart', [CartController::class, 'getOrCreate']);
Route::get('cart/{id}', [CartController::class, 'show']);
Route::post('cart/{id}/items', [CartController::class, 'addItem']);
Route::patch('cart/{id}/items/{item}', [CartController::class, 'updateItem']);
Route::delete('cart/{id}/items/{item}', [CartController::class, 'removeItem']);

// Checkout
Route::post('orders', [OrderController::class, 'store']);
Route::get('/orders/{number}', [OrderController::class, 'show']);

Route::post('/payments/intent', [PaymentController::class, 'intent']);
Route::post('/payment/refresh/{number}', [PaymentController::class, 'refreshStatus']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile/wishlist', [WishlistController::class, 'index']);
    Route::post('profile/wishlist/{product}', [WishlistController::class, 'store']);
    Route::delete('profile/wishlist/{product}', [WishlistController::class, 'destroy']);
});
