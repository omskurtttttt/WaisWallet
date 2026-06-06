<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\DashboardService::class, function ($app) {
            return new \App\Services\DashboardService(auth()->user() ?? new \App\Models\User());
        });

        $this->app->bind(\App\Services\TransactionService::class, function ($app) {
            return new \App\Services\TransactionService(auth()->user() ?? new \App\Models\User());
        });

        $this->app->bind(\App\Services\AnalyticsService::class, function ($app) {
            return new \App\Services\AnalyticsService(auth()->user() ?? new \App\Models\User());
        });

        $this->app->bind(\App\Services\ReportService::class, function ($app) {
            return new \App\Services\ReportService(auth()->user() ?? new \App\Models\User());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
