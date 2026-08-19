<?php

use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ActiveMarketController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\HelpController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\MarketingController;
use App\Http\Controllers\Api\MapperController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ShopLocationController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorApplicationController;
use App\Http\Controllers\Api\VendorRegistrationController;
use App\Http\Controllers\Api\VerificationQueueController;
use Illuminate\Support\Facades\Route;

// ─── Health check (no auth required) ─────────────────────────────
Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'timestamp' => now()->toIso8601String(),
    'service' => 'market-mirror-api',
]));

// ─── Public routes ───────────────────────────────────────────────
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:auth');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/auth/refresh', [AuthController::class, 'refreshFromRefreshToken']);
Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgotPassword'])->middleware('throttle:password_reset');
Route::post('/auth/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:password_reset');

Route::get('/buildings', [BuildingController::class, 'index']);
Route::get('/markets', [MarketController::class, 'index']);
Route::get('/markets/{id}', [MarketController::class, 'show']);
Route::get('/faqs', [HelpController::class, 'faqs']);

// Buyer discovery routes are market-aware (X-Market-Id header or ?market_id=)
Route::middleware(['market'])->group(function () {
    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{id}', [ShopController::class, 'show']);
    Route::get('/shops/{id}/reviews', [ShopController::class, 'reviews']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/search', [SearchController::class, 'index']);
});

// ─── Authenticated routes (any role) ─────────────────────────────
Route::middleware(['auth:api'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/profile/avatar', [AuthController::class, 'uploadAvatar']);

    // Persistent active-market preference
    Route::get('/user/active-market', [ActiveMarketController::class, 'show']);
    Route::post('/user/active-market', [ActiveMarketController::class, 'store']);

    // Orders (market-aware checkout: validates items against the active market)
    Route::middleware(['market'])->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::put('/orders/{id}', [OrderController::class, 'update']);
        Route::get('/orders/{id}/tracking', [OrderController::class, 'tracking']);
    });
    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/reviews/{id}/reply', [ReviewController::class, 'reply']);
    Route::post('/reviews/{id}/images', [ReviewController::class, 'uploadImage']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Disputes
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::post('/disputes', [DisputeController::class, 'store']);
    Route::put('/disputes/{id}', [DisputeController::class, 'update']);
    // Vendor Applications
    Route::get('/vendor-applications', [VendorApplicationController::class, 'index']);
    Route::post('/vendor-applications', [VendorApplicationController::class, 'store']);
    Route::put('/vendor-applications/{id}', [VendorApplicationController::class, 'update']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}', [NotificationController::class, 'update']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);

    // Cart (market-aware: checkout validates items against the active market)
    Route::middleware(['market'])->group(function () {
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::put('/cart/{productId}', [CartController::class, 'update']);
        Route::delete('/cart/{productId}', [CartController::class, 'destroy']);
        Route::delete('/cart', [CartController::class, 'clear']);
    });

    // Shops (authenticated)
    Route::post('/shops', [ShopController::class, 'store']);
    Route::put('/shops/{id}', [ShopController::class, 'update']);
    Route::delete('/shops/{id}', [ShopController::class, 'destroy']);
    Route::post('/shops/{id}/images', [ShopController::class, 'uploadImage']);
    Route::delete('/shop-images/{imageId}', [ShopController::class, 'removeImage']);

    // Products (authenticated)
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/{id}/image', [ProductController::class, 'uploadImage']);
    Route::delete('/products/{id}/image', [ProductController::class, 'removeImage']);

    // Analytics (vendor)
    Route::get('/analytics/vendor', [AnalyticsController::class, 'vendor']);
    Route::get('/analytics/vendor/sales', [AnalyticsController::class, 'vendorSales']);

    // Payouts (vendor)
    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::post('/payouts', [PayoutController::class, 'store']);
    Route::get('/payouts/bank-details', [PayoutController::class, 'bankDetails']);
    Route::put('/payouts/bank-details', [PayoutController::class, 'updateBankDetails']);
    Route::post('/payouts/bank-account/verify', [PayoutController::class, 'verifyBankAccount']);
    Route::get('/payouts/{id}', [PayoutController::class, 'show']);

    // Marketing (vendor)
    Route::get('/marketing/coupons', [MarketingController::class, 'coupons']);
    Route::post('/marketing/coupons', [MarketingController::class, 'storeCoupon']);
    Route::put('/marketing/coupons/{id}', [MarketingController::class, 'updateCoupon']);
    Route::delete('/marketing/coupons/{id}', [MarketingController::class, 'destroyCoupon']);
    Route::get('/marketing/flash-sales', [MarketingController::class, 'flashSales']);
    Route::post('/marketing/flash-sales', [MarketingController::class, 'storeFlashSale']);
    Route::put('/marketing/flash-sales/{id}', [MarketingController::class, 'updateFlashSale']);
    Route::delete('/marketing/flash-sales/{id}', [MarketingController::class, 'destroyFlashSale']);

    // Help/Support
    Route::get('/help/tickets', [HelpController::class, 'tickets']);
    Route::post('/help/tickets', [HelpController::class, 'storeTicket']);

    // Customers (vendor)
    Route::get('/customers', [CustomerController::class, 'index']);
});

// ─── Mapper-only routes ──────────────────────────────────────────
Route::middleware(['auth:api', 'mapper'])->group(function () {
    Route::get('/mapper/dashboard', [MapperController::class, 'dashboard']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);

    // Shop Locations
    Route::get('/shop-locations', [ShopLocationController::class, 'index']);
    Route::post('/shop-locations', [ShopLocationController::class, 'store']);
    Route::put('/shop-locations/{id}', [ShopLocationController::class, 'update']);
    Route::post('/shop-locations/{id}/photos', [ShopLocationController::class, 'uploadPhoto']);

    // Vendor Registrations
    Route::get('/vendor-registrations', [VendorRegistrationController::class, 'index']);
    Route::post('/vendor-registrations', [VendorRegistrationController::class, 'store']);
    Route::put('/vendor-registrations/{id}', [VendorRegistrationController::class, 'update']);
    Route::post('/vendor-registrations/{id}/photos', [VendorRegistrationController::class, 'uploadPhoto']);
});

// ─── Admin-only routes ───────────────────────────────────────────
Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
    // Verification Queue
    Route::get('/verification-queue', [VerificationQueueController::class, 'index']);
    Route::put('/verification-queue/{id}', [VerificationQueueController::class, 'update']);

    // User Management
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);

    // Mapper Management
    Route::get('/mappers', [MapperController::class, 'index']);
    Route::get('/mappers/{id}', [MapperController::class, 'show']);
    Route::post('/mappers', [MapperController::class, 'store']);
    Route::put('/mappers/{id}', [MapperController::class, 'update']);
    Route::delete('/mappers/{id}', [MapperController::class, 'destroy']);
    Route::post('/mappers/assign-task', [MapperController::class, 'assignTask']);
    Route::get('/mappers-dashboard', [MapperController::class, 'dashboard']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::post('/disputes/{id}/resolve', [DisputeController::class, 'resolve']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
});
