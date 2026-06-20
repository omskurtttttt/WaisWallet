<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\BudgetService;
use App\Services\CardService;
use App\Services\DashboardService;
use App\Services\GoalService;
use App\Services\NotificationService;
use App\Services\RecurringTransactionService;
use App\Services\ReportService;
use App\Services\TransactionService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
                return new User;
            }

            throw new \RuntimeException('Cannot resolve user-scoped service: No authenticated user in this context.');
        };

        $this->app->bind(DashboardService::class, function ($app) use ($resolveUser) {
            return new DashboardService($resolveUser());
        });

        $this->app->bind(TransactionService::class, function ($app) use ($resolveUser) {
            return new TransactionService($resolveUser());
        });

        $this->app->bind(AnalyticsService::class, function ($app) use ($resolveUser) {
            return new AnalyticsService($resolveUser());
        });

        $this->app->bind(ReportService::class, function ($app) use ($resolveUser) {
            return new ReportService($resolveUser());
        });

        $this->app->bind(CardService::class, function ($app) use ($resolveUser) {
            return new CardService($resolveUser());
        });

        $this->app->bind(GoalService::class, function ($app) use ($resolveUser) {
            return new GoalService($resolveUser());
        });

        $this->app->bind(BudgetService::class, function ($app) use ($resolveUser) {
            return new BudgetService($resolveUser());
        });

        $this->app->bind(NotificationService::class, function ($app) use ($resolveUser) {
            return new NotificationService($resolveUser());
        });

        $this->app->bind(RecurringTransactionService::class, function ($app) use ($resolveUser) {
            return new RecurringTransactionService($resolveUser());
        });
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('financial-writes', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
