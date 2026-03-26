<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-Commerce API Routes
|--------------------------------------------------------------------------
|
| These routes handle all e-commerce functionality: carts, checkout,
| orders, wallet, profile, disputes, tickets, etc.
|
*/

Route::prefix('ecommerce')->group(function () {

    // Public E-Commerce Routes (No Auth Required)
    Route::get('/products', [\App\Http\Controllers\Api\Ecommerce\ProductController::class, 'index']);
    Route::get('/products/{id}', [\App\Http\Controllers\Api\Ecommerce\ProductController::class, 'show']);
    
    // Guest Cart (Session/Cookie based cart could be handled here if needed, 
    // but usually we force auth for b2b or manage via local storage on frontend)

    // Protected E-Commerce Routes (Auth Required)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Cart
        Route::get('/cart', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'index']);
        Route::post('/cart/items', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'addItem']);
        Route::put('/cart/items/{id}', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'updateItem']);
        Route::delete('/cart/items/{id}', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'removeItem']);
        Route::delete('/cart', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'clear']);

        // Checkout & Orders
        Route::post('/checkout', [\App\Http\Controllers\Api\Ecommerce\CheckoutController::class, 'process']);
        Route::apiResource('orders', \App\Http\Controllers\Api\Ecommerce\EcommerceOrderController::class);

        // Account Profile & Addresses
        Route::get('/profile', [\App\Http\Controllers\Api\Ecommerce\ProfileController::class, 'show']);
        Route::put('/profile', [\App\Http\Controllers\Api\Ecommerce\ProfileController::class, 'update']);
        Route::put('/profile/password', [\App\Http\Controllers\Api\Ecommerce\ProfileController::class, 'updatePassword']);
        Route::put('/profile/pin', [\App\Http\Controllers\Api\Ecommerce\ProfileController::class, 'updatePin']);
        
        Route::apiResource('addresses', \App\Http\Controllers\Api\Ecommerce\AddressController::class);

        // Wallet & Transactions
        Route::get('/wallet', [\App\Http\Controllers\Api\Ecommerce\EcommerceWalletTransactionController::class, 'summary']);
        Route::apiResource('wallet-transactions', \App\Http\Controllers\Api\Ecommerce\EcommerceWalletTransactionController::class);

        // Other E-Commerce Features
        Route::apiResource('quotations', \App\Http\Controllers\Api\Ecommerce\EcommerceQuotationController::class);
        Route::apiResource('memberships', \App\Http\Controllers\Api\Ecommerce\EcommerceMembershipController::class);
        Route::apiResource('disputes', \App\Http\Controllers\Api\Ecommerce\EcommerceDisputeController::class);
        Route::apiResource('tickets', \App\Http\Controllers\Api\Ecommerce\EcommerceTicketController::class);
        Route::apiResource('returns', \App\Http\Controllers\Api\Ecommerce\EcommerceReturnController::class);
        Route::apiResource('gift-cards', \App\Http\Controllers\Api\Ecommerce\EcommerceGiftCardController::class);
        Route::apiResource('reviews', \App\Http\Controllers\Api\Ecommerce\EcommerceReviewController::class);
        Route::apiResource('affiliates', \App\Http\Controllers\Api\Ecommerce\EcommerceAffiliateController::class);
    });

});
