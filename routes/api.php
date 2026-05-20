<?php

use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\Route;

// Public routes
require __DIR__.'/api/billing.php';
require __DIR__.'/api/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    require __DIR__.'/api/user.php';
    require __DIR__.'/api/tenant.php';

    Route::middleware(ResolveTenant::class)->scopeBindings()->group(function () {
        require __DIR__.'/api/analytics.php';
        require __DIR__.'/api/notification.php';
        require __DIR__.'/api/label.php';
        require __DIR__.'/api/activity.php';
        require __DIR__.'/api/task.php';
    });
});
