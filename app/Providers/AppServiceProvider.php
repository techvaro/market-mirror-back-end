<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\UserService;
use App\Services\MapperService;
use App\Services\TaskService;
use App\Services\ShopService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\ReviewService;
use App\Services\AdminDashboardService;
use App\Services\SearchService;
use App\Services\MessageService;
use App\Services\DisputeService;
use App\Services\PaymentService;
use App\Services\VendorApplicationService;
use App\Services\VendorRegistrationService;
use App\Services\VerificationQueueService;
use App\Services\NotificationService;
use App\Services\ShopLocationService;
use App\Services\BuildingService;
use App\Services\CategoryService;
use App\Services\MarketService;
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
        $this->app->singleton(MarketContext::class, fn () => new MarketContext());

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

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('password_reset', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        Gate::policy(\App\Models\Product::class, \App\Policies\ProductPolicy::class);
        Gate::policy(\App\Models\Shop::class, \App\Policies\ShopPolicy::class);
        Gate::policy(\App\Models\Order::class, \App\Policies\OrderPolicy::class);
        Gate::policy(\App\Models\Dispute::class, \App\Policies\DisputePolicy::class);
        Gate::policy(\App\Models\Review::class, \App\Policies\ReviewPolicy::class);
        Gate::policy(\App\Models\Task::class, \App\Policies\TaskPolicy::class);
        Gate::policy(\App\Models\ShopLocation::class, \App\Policies\ShopLocationPolicy::class);
        Gate::policy(\App\Models\VendorApplication::class, \App\Policies\VendorApplicationPolicy::class);
        Gate::policy(\App\Models\VendorRegistration::class, \App\Policies\VendorRegistrationPolicy::class);
        Gate::policy(\App\Models\Coupon::class, \App\Policies\CouponPolicy::class);
        Gate::policy(\App\Models\FlashSale::class, \App\Policies\FlashSalePolicy::class);
        Gate::policy(\App\Models\Payout::class, \App\Policies\PayoutPolicy::class);
        Gate::policy(\App\Models\Mapper::class, \App\Policies\AdminResourcePolicy::class);
        Gate::policy(\App\Models\VerificationQueue::class, \App\Policies\AdminResourcePolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\AdminResourcePolicy::class);

        Gate::define('access-analytics', fn (\App\Models\User $user) => in_array($user->role, ['admin', 'vendor']));
    }
}
