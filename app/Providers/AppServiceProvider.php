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
        $resolveUser = function () {
            if (auth()->check()) {
                return auth()->user();
            }

            if (app()->runningUnitTests()) {
                return new \App\Models\User();
            }

            throw new \RuntimeException("Cannot resolve user-scoped service: No authenticated user in this context.");
        };

        $this->app->bind(\App\Services\DashboardService::class, function ($app) use ($resolveUser) {
            return new \App\Services\DashboardService($resolveUser());
        });

        $this->app->bind(\App\Services\TransactionService::class, function ($app) use ($resolveUser) {
            return new \App\Services\TransactionService($resolveUser());
        });

        $this->app->bind(\App\Services\AnalyticsService::class, function ($app) use ($resolveUser) {
            return new \App\Services\AnalyticsService($resolveUser());
        });

        $this->app->bind(\App\Services\ReportService::class, function ($app) use ($resolveUser) {
            return new \App\Services\ReportService($resolveUser());
        });

        $this->app->bind(\App\Services\CardService::class, function ($app) use ($resolveUser) {
            return new \App\Services\CardService($resolveUser());
        });

        $this->app->bind(\App\Services\GoalService::class, function ($app) use ($resolveUser) {
            return new \App\Services\GoalService($resolveUser());
        });

        $this->app->bind(\App\Services\BudgetService::class, function ($app) use ($resolveUser) {
            return new \App\Services\BudgetService($resolveUser());
        });

        $this->app->bind(\App\Services\NotificationService::class, function ($app) use ($resolveUser) {
            return new \App\Services\NotificationService($resolveUser());
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
