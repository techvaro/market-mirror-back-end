<?php

namespace App\Providers;

use App\Models\Coupon;
use App\Models\Dispute;
use App\Models\FlashSale;
use App\Models\Mapper;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\Task;
use App\Models\User;
use App\Models\VendorApplication;
use App\Models\VendorRegistration;
use App\Models\VerificationQueue;
use App\Policies\AdminResourcePolicy;
use App\Policies\CouponPolicy;
use App\Policies\DisputePolicy;
use App\Policies\FlashSalePolicy;
use App\Policies\OrderPolicy;
use App\Policies\PayoutPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\ShopLocationPolicy;
use App\Policies\ShopPolicy;
use App\Policies\TaskPolicy;
use App\Policies\VendorApplicationPolicy;
use App\Policies\VendorRegistrationPolicy;
use App\Services\AdminDashboardService;
use App\Services\AuthService;
use App\Services\BuildingService;
use App\Services\CategoryService;
use App\Services\DisputeService;
use App\Services\MapperService;
use App\Services\MarketService;
use App\Services\MessageService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ProductService;
use App\Services\ReviewService;
use App\Services\SearchService;
use App\Services\ShopLocationService;
use App\Services\ShopService;
use App\Services\TaskService;
use App\Services\UserService;
use App\Services\VendorApplicationService;
use App\Services\VendorRegistrationService;
use App\Services\VerificationQueueService;
use App\Support\MarketContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MarketContext::class, fn () => new MarketContext);

        $this->app->singleton(AuthService::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(MapperService::class);
        $this->app->singleton(TaskService::class);
        $this->app->singleton(ShopService::class);
        $this->app->singleton(ProductService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(ReviewService::class);
        $this->app->singleton(AdminDashboardService::class);
        $this->app->singleton(SearchService::class);
        $this->app->singleton(MessageService::class);
        $this->app->singleton(DisputeService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(VendorApplicationService::class);
        $this->app->singleton(VendorRegistrationService::class);
        $this->app->singleton(VerificationQueueService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(ShopLocationService::class);
        $this->app->singleton(BuildingService::class);
        $this->app->singleton(CategoryService::class);
        $this->app->singleton(MarketService::class);
    }

    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('password_reset', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        RateLimiter::for('payments', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));

        // Flutterwave webhooks are machine-to-machine and keyed by source IP.
        RateLimiter::for('webhooks', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Shop::class, ShopPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Dispute::class, DisputePolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(ShopLocation::class, ShopLocationPolicy::class);
        Gate::policy(VendorApplication::class, VendorApplicationPolicy::class);
        Gate::policy(VendorRegistration::class, VendorRegistrationPolicy::class);
        Gate::policy(Coupon::class, CouponPolicy::class);
        Gate::policy(FlashSale::class, FlashSalePolicy::class);
        Gate::policy(Payout::class, PayoutPolicy::class);
        Gate::policy(Mapper::class, AdminResourcePolicy::class);
        Gate::policy(VerificationQueue::class, AdminResourcePolicy::class);
        Gate::policy(User::class, AdminResourcePolicy::class);

        Gate::define('access-analytics', fn (User $user) => in_array($user->role, ['admin', 'vendor']));
    }
}
