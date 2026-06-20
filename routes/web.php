<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\RecurringTransactionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Read operations (Pages)
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/wallet', [CardController::class, 'index'])->name('wallet.index');
    Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
    Route::get('/analytics', AnalyticsController::class)->name('analytics.index');
    Route::get('/reports', ReportController::class)->name('reports.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/transactions/recurring', [RecurringTransactionController::class, 'index'])->name('transactions.recurring.index');

    // Write operations (Throttled)
    Route::middleware('throttle:financial-writes')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::post('/wallet/cards', [CardController::class, 'store'])->name('wallet.cards.store');
        Route::put('/wallet/cards/{card}', [CardController::class, 'update'])->name('wallet.cards.update');
        Route::delete('/wallet/cards/{card}', [CardController::class, 'destroy'])->name('wallet.cards.destroy');
        Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
        Route::post('/goals/{goal}/funds', [GoalController::class, 'addFunds'])->name('goals.funds');
        Route::patch('/goals/{goal}/complete', [GoalController::class, 'markAsComplete'])->name('goals.complete');
        Route::put('/goals/{goal}', [GoalController::class, 'update'])->name('goals.update');
        Route::delete('/goals/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');

        Route::post('/transactions/recurring', [RecurringTransactionController::class, 'store'])->name('transactions.recurring.store');
        Route::put('/transactions/recurring/{recurringTransaction}', [RecurringTransactionController::class, 'update'])->name('transactions.recurring.update');
        Route::delete('/transactions/recurring/{recurringTransaction}', [RecurringTransactionController::class, 'destroy'])->name('transactions.recurring.destroy');

        Route::patch('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
    });
});

require __DIR__.'/auth.php';
