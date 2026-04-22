<?php

use App\Constants\Permission;
use App\Http\Middleware\ResolveTenant;
use App\Modules\Auth\AuthController;
use App\Modules\Task\Http\Controllers\TaskController;
use App\Modules\Tenant\TenantController;
use Illuminate\Support\Facades\Route;

Route::post('/tasks', [TaskController::class, 'store']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', ResolveTenant::class])->group(function () {

    Route::post('/tenants', [TenantController::class, 'store']);

    Route::middleware(['permission:' . Permission::TASK_CREATE])->group(function () {
        Route::post('/tasks', [TaskController::class, 'store']);
    });

});
