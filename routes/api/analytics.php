<?php

use App\Common\Constants\Feature;
use App\Modules\Analytics\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('analytics')
    ->middleware('feature:'.Feature::ANALYTICS)
    ->group(function () {
        Route::get('/completion-rate', [AnalyticsController::class, 'completionRate']);
        Route::get('/tasks-per-user', [AnalyticsController::class, 'tasksPerUser']);
        Route::get('/overdue-rate', [AnalyticsController::class, 'overdueRate']);
        Route::get('/priority-distribution', [AnalyticsController::class, 'priorityDistribution']);
        Route::get('/trend', [AnalyticsController::class, 'trend']);
    });
