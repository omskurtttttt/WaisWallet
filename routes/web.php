<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/transactions', function () { return Inertia::render('Transactions/Index'); })->name('transactions.index');
    Route::get('/wallet', function () { return Inertia::render('Wallet/Index'); })->name('wallet.index');
    Route::get('/goals', function () { return Inertia::render('Goals/Index'); })->name('goals.index');
    Route::get('/analytics', function () { return Inertia::render('Analytics/Index'); })->name('analytics.index');
    Route::get('/reports', function () { return Inertia::render('Reports/Index'); })->name('reports.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
