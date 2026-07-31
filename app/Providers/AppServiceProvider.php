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
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
    }
}
