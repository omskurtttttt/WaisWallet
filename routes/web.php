<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/transactions', function () {
    return Inertia::render('Transactions/Index');
})->name('transactions');

Route::get('/wallet', function () {
    return Inertia::render('Wallet/Index');
})->name('wallet');

Route::get('/goals', function () {
    return Inertia::render('Goals/Index');
})->name('goals');

Route::get('/analytics', function () {
    return Inertia::render('Analytics/Index');
})->name('analytics');

Route::get('/reports', function () {
    return Inertia::render('Reports/Index');
})->name('reports');
