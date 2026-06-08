<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(\App\Http\Middleware\AdminAuth::class)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/boards', [BoardsController::class, 'index'])->name('boards.index');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');

    Route::get('/sites/{trackingId}', [DashboardController::class, 'site'])->name('dashboard.site');

    Route::prefix('sites/{trackingId}')->group(function () {
        Route::get('/charts',      [AnalyticsController::class, 'charts'])->name('analytics.charts');
        Route::get('/replay',      [AnalyticsController::class, 'replay'])->name('analytics.replay');
        Route::get('/heatmap',     [AnalyticsController::class, 'heatmap'])->name('analytics.heatmap');
        Route::get('/feedback',    [AnalyticsController::class, 'feedback'])->name('analytics.feedback');
        Route::get('/visitors',    [AnalyticsController::class, 'visitors'])->name('analytics.visitors');
        Route::get('/events',      [AnalyticsController::class, 'events'])->name('analytics.events');
        Route::get('/integration', [AnalyticsController::class, 'integration'])->name('analytics.integration');
        Route::get('/workspace',   [WorkspaceController::class, 'show'])->name('workspace.show');
        Route::put('/workspace',   [WorkspaceController::class, 'update'])->name('workspace.update');
    });
});
