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
    Route::get('/categories', [\App\Http\Controllers\Api\Ecommerce\CategoryController::class, 'index']);
    Route::get('/products', [\App\Http\Controllers\Api\Ecommerce\ProductController::class, 'index']);
    Route::get('/products/{product}', [\App\Http\Controllers\Api\Ecommerce\ProductController::class, 'show']);
    Route::post('/order-submissions', [\App\Http\Controllers\Api\Ecommerce\PublicOrderSubmissionController::class, 'store']);
    
    // Guest Cart (Session/Cookie based cart could be handled here if needed, 
    // but usually we force auth for b2b or manage via local storage on frontend)

    // Protected E-Commerce Routes (Auth Required)
    Route::middleware(['central.auth', 'auth:sanctum'])->group(function () {
        
        // Cart
        Route::get('/cart', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'index']);
        Route::post('/cart/items', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'addItem']);
        Route::put('/cart/items/{id}', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'updateItem']);
        Route::delete('/cart/items/{id}', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'removeItem']);
        Route::delete('/cart', [\App\Http\Controllers\Api\Ecommerce\CartController::class, 'clear']);

        // Checkout, Payments & Orders
        Route::post('/checkout', [\App\Http\Controllers\Api\Ecommerce\CheckoutController::class, 'process']);
        Route::post('/payment/process', [\App\Http\Controllers\Api\Ecommerce\PaymentController::class, 'process']);
        Route::match(['get', 'post'], '/payment/paypal/success', [\App\Http\Controllers\Api\Ecommerce\PaymentController::class, 'paypalSuccess']);
        Route::match(['get', 'post'], '/payment/paypal/cancel', [\App\Http\Controllers\Api\Ecommerce\PaymentController::class, 'paypalCancel']);
        
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

        // Order Proofs
        Route::get('/orders/{orderId}/proofs', [\App\Http\Controllers\Api\Ecommerce\OrderProofController::class, 'getByOrder']);
        Route::post('/proofs/{id}/approve', [\App\Http\Controllers\Api\Ecommerce\OrderProofController::class, 'approve']);
        Route::post('/proofs/{id}/reject', [\App\Http\Controllers\Api\Ecommerce\OrderProofController::class, 'reject']);
    });

    // Admin E-Commerce Routes (Auth + Admin Role Required)
    Route::middleware(['auth:sanctum', 'admin.only'])->group(function () {
        // Orders Management
        Route::get('/admin/orders', [\App\Http\Controllers\Api\Admin\AdminOrderController::class, 'index']);
        Route::get('/admin/orders/{order}', [\App\Http\Controllers\Api\Admin\AdminOrderController::class, 'show']);
        Route::put('/admin/orders/{order}/status', [\App\Http\Controllers\Api\Admin\AdminOrderController::class, 'updateStatus']);
        Route::delete('/admin/orders/{order}', [\App\Http\Controllers\Api\Admin\AdminOrderController::class, 'destroy']);

        // Reviews Management
        Route::get('/admin/reviews', [\App\Http\Controllers\Api\Admin\AdminReviewController::class, 'index']);
        Route::get('/admin/reviews/{review}', [\App\Http\Controllers\Api\Admin\AdminReviewController::class, 'show']);
        Route::put('/admin/reviews/{review}', [\App\Http\Controllers\Api\Admin\AdminReviewController::class, 'approve']);
        Route::delete('/admin/reviews/{review}', [\App\Http\Controllers\Api\Admin\AdminReviewController::class, 'destroy']);

        // Returns Management
        Route::get('/admin/returns', [\App\Http\Controllers\Api\Admin\AdminReturnController::class, 'index']);
        Route::get('/admin/returns/{return}', [\App\Http\Controllers\Api\Admin\AdminReturnController::class, 'show']);
        Route::put('/admin/returns/{return}/status', [\App\Http\Controllers\Api\Admin\AdminReturnController::class, 'updateStatus']);
        Route::post('/admin/returns/{return}/approve', [\App\Http\Controllers\Api\Admin\AdminReturnController::class, 'approve']);

        // Quotations Management
        Route::get('/admin/quotations', [\App\Http\Controllers\Api\Admin\AdminQuotationController::class, 'index']);
        Route::get('/admin/quotations/{quotation}', [\App\Http\Controllers\Api\Admin\AdminQuotationController::class, 'show']);
        Route::put('/admin/quotations/{quotation}/status', [\App\Http\Controllers\Api\Admin\AdminQuotationController::class, 'updateStatus']);
        Route::post('/admin/quotations/{quotation}/send-quote', [\App\Http\Controllers\Api\Admin\AdminQuotationController::class, 'sendQuote']);

        // Support Tickets Management
        Route::get('/admin/tickets', [\App\Http\Controllers\Api\Admin\AdminTicketController::class, 'index']);
        Route::get('/admin/tickets/{ticket}', [\App\Http\Controllers\Api\Admin\AdminTicketController::class, 'show']);
        Route::put('/admin/tickets/{ticket}/status', [\App\Http\Controllers\Api\Admin\AdminTicketController::class, 'updateStatus']);
        Route::post('/admin/tickets/{ticket}/reply', [\App\Http\Controllers\Api\Admin\AdminTicketController::class, 'addReply']);
        Route::post('/admin/tickets/{ticket}/close', [\App\Http\Controllers\Api\Admin\AdminTicketController::class, 'close']);

        // Wallet Management
        Route::get('/admin/wallet', [\App\Http\Controllers\Api\Admin\AdminWalletController::class, 'index']);
        Route::get('/admin/wallet/{user}', [\App\Http\Controllers\Api\Admin\AdminWalletController::class, 'getUserWallet']);
        Route::post('/admin/wallet/{user}/credit', [\App\Http\Controllers\Api\Admin\AdminWalletController::class, 'creditWallet']);
        Route::post('/admin/wallet/{user}/debit', [\App\Http\Controllers\Api\Admin\AdminWalletController::class, 'debitWallet']);

        // Disputes Management
        Route::get('/admin/disputes', [\App\Http\Controllers\Api\Ecommerce\EcommerceDisputeController::class, 'index']);
        Route::get('/admin/disputes/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceDisputeController::class, 'show']);
        Route::put('/admin/disputes/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceDisputeController::class, 'update']);
        Route::delete('/admin/disputes/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceDisputeController::class, 'destroy']);

        // Memberships Management
        Route::get('/admin/memberships', [\App\Http\Controllers\Api\Ecommerce\EcommerceMembershipController::class, 'index']);
        Route::get('/admin/memberships/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceMembershipController::class, 'show']);
        Route::put('/admin/memberships/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceMembershipController::class, 'update']);
        Route::delete('/admin/memberships/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceMembershipController::class, 'destroy']);

        // Affiliates Management
        Route::get('/admin/affiliates', [\App\Http\Controllers\Api\Ecommerce\EcommerceAffiliateController::class, 'index']);
        Route::get('/admin/affiliates/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceAffiliateController::class, 'show']);
        Route::put('/admin/affiliates/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceAffiliateController::class, 'update']);
        Route::delete('/admin/affiliates/{id}', [\App\Http\Controllers\Api\Ecommerce\EcommerceAffiliateController::class, 'destroy']);
    });

});
