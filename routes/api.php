<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AddressController,
    AuthController,
    CategoryController,
    PaymentController,
    ProductController,
    CartController,
    OrderController,
    ProfilePointsController,
    OrderMessageController,
    ReviewController,
    SearchController,
    TwoFactorController,
    VerifyEmailController,
    WishlistController};

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'update']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->post('email/resend', [AuthController::class, 'resendEmailVerification']);

Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('api.email.verify');

//Categories
Route::get('categories', [CategoryController::class,'index']);

Route::get('search/suggestions', [SearchController::class, 'suggestions']);

$productsAndOrders = function () {
    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/facets', [ProductController::class, 'facets']);
    Route::get('products/{slug}', [ProductController::class, 'show']);
    Route::get('products/{id}/reviews', [ReviewController::class, 'index']);
    Route::middleware('auth:sanctum')->post('products/{id}/reviews', [ReviewController::class, 'store']);

    Route::get('seller/{vendor}/products', [ProductController::class, 'sellerProducts'])
        ->whereNumber('vendor');

    // Checkout
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{number}', [OrderController::class, 'show']);
};

$productsAndOrders();

Route::pattern('currency', '[A-Za-z]{3}');

Route::group([
    'prefix' => '{currency}',
    'where' => ['currency' => '[A-Za-z]{3}'],
], $productsAndOrders);

// Cart
Route::get('cart', [CartController::class, 'getOrCreate']);
Route::get('cart/{id}', [CartController::class, 'show']);
Route::post('cart/{id}/items', [CartController::class, 'addItem']);
Route::patch('cart/{id}/items/{item}', [CartController::class, 'updateItem']);
Route::delete('cart/{id}/items/{item}', [CartController::class, 'removeItem']);
Route::post('cart/apply-coupon', [CartController::class, 'applyCoupon']);
Route::post('cart/apply-points', [CartController::class, 'applyPoints']);

Route::post('/payments/intent', [PaymentController::class, 'intent']);
Route::post('/payment/refresh/{number}', [PaymentController::class, 'refreshStatus']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile/orders', [OrderController::class, 'index']);
    Route::apiResource('profile/addresses', AddressController::class);
    Route::get('profile/wishlist', [WishlistController::class, 'index']);
    Route::post('profile/wishlist/{product}', [WishlistController::class, 'store']);
    Route::delete('profile/wishlist/{product}', [WishlistController::class, 'destroy']);
    Route::get('profile/points', [ProfilePointsController::class, 'index']);
    Route::get('orders/{order}/messages', [OrderMessageController::class, 'index']);
    Route::post('orders/{order}/messages', [OrderMessageController::class, 'store']);
    Route::get('profile/two-factor', [TwoFactorController::class, 'show']);
    Route::post('profile/two-factor', [TwoFactorController::class, 'store']);
    Route::post('profile/two-factor/confirm', [TwoFactorController::class, 'confirm']);
    Route::delete('profile/two-factor', [TwoFactorController::class, 'destroy']);
});
