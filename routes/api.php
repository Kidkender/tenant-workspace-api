<?php

use App\Constants\Permission;
use App\Http\Middleware\ResolveTenant;
use App\Modules\Activity\ActivityLogController;
use App\Modules\Auth\AuthController;
use App\Modules\Dashboard\DashboardController;
use App\Modules\Task\Http\Controllers\TaskCommentController;
use App\Modules\Task\Http\Controllers\TaskController;
use App\Modules\Tenant\TenantController;
use App\Modules\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/tenants', [TenantController::class, 'store']);
    Route::get('/me', [UserController::class, 'getMe']);
    Route::put('/me', [UserController::class, 'updateMe']);
});

Route::middleware(['auth:sanctum', ResolveTenant::class])->group(function () {
    Route::get('/tenant/members', [TenantController::class, 'members']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    Route::prefix('/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])
            ->middleware('permission:'.Permission::TASK_VIEW);

        Route::get('/{id}', [TaskController::class, 'show'])
            ->middleware('permission:'.Permission::TASK_VIEW);

        Route::post('/', [TaskController::class, 'store'])
            ->middleware('permission:'.Permission::TASK_CREATE);

        Route::put('/{id}', [TaskController::class, 'update'])
            ->middleware('permission:'.Permission::TASK_UPDATE);

        Route::delete('/{id}', [TaskController::class, 'destroy'])
            ->middleware('permission:'.Permission::TASK_DELETE);

        Route::prefix('{taskId}/comments')->group(function () {

            Route::get('/', [TaskCommentController::class, 'index'])
                ->middleware('permission:'.Permission::TASK_VIEW);

            Route::post('/', [TaskCommentController::class, 'store'])
                ->middleware('permission:'.Permission::COMMENT_CREATE);

            Route::put('/{commentId}', [TaskCommentController::class, 'update']);

            Route::delete('/{commentId}', [TaskCommentController::class, 'destroy'])
                ->middleware('permission:'.Permission::COMMENT_DELETE);
        });
    });

});
