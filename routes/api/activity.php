<?php

use App\Modules\Activity\ActivityLogController;
use App\Modules\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::get('/activity-logs', [ActivityLogController::class, 'index']);
