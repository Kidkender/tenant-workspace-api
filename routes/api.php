<?php

use App\Constants\Permission;
use App\Http\Middleware\ResolveTenant;
use App\Modules\Access\Models\Role;
use App\Modules\Activity\ActivityLogController;
use App\Modules\Auth\AuthController;
use App\Modules\Dashboard\DashboardController;
use App\Modules\Task\Http\Controllers\TaskCommentController;
use App\Modules\Task\Http\Controllers\TaskController;
use App\Modules\Tenant\TenantController;
use App\Modules\User\Models\User;
use App\Modules\User\UserController;
use App\Notifications\NotificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);
    if (!hash_equals(sha1($user->email), $hash)) {
        abort(403, 'Invalid verification hash.');
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return redirect(env('FRONTEND_URL') . '/email-verified');
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/verify/resend', [AuthController::class, 'resendEmailVerification']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/tenants', [TenantController::class, 'store']);
    Route::post('/tenants/accept/{token}', [TenantController::class, 'accept']);
    Route::get('/me', [UserController::class, 'getMe']);
    Route::put('/me', [UserController::class, 'updateMe']);
});

Route::middleware(['auth:sanctum', ResolveTenant::class])->group(function () {
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
});

Route::middleware(['auth:sanctum', ResolveTenant::class])->group(function () {
    Route::get('/tenant/members', [TenantController::class, 'members']);
    Route::post('/tenants/invite', [TenantController::class, 'invite']);
    Route::get('/roles', fn() => response()->json(['data' => Role::whereIn('name', ['admin', 'member'])->get(['id', 'name'])]));
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    Route::prefix('/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])
            ->middleware('permission:' . Permission::TASK_VIEW);

        Route::get('/{id}', [TaskController::class, 'show'])
            ->middleware('permission:' . Permission::TASK_VIEW);

        Route::post('/', [TaskController::class, 'store'])
            ->middleware('permission:' . Permission::TASK_CREATE);

        Route::put('/{id}', [TaskController::class, 'update'])
            ->middleware('permission:' . Permission::TASK_UPDATE);

        Route::delete('/{id}', [TaskController::class, 'destroy'])
            ->middleware('permission:' . Permission::TASK_DELETE);

        Route::prefix('{taskId}/comments')->group(function () {

            Route::get('/', [TaskCommentController::class, 'index'])
                ->middleware('permission:' . Permission::TASK_VIEW);

            Route::post('/', [TaskCommentController::class, 'store'])
                ->middleware('permission:' . Permission::COMMENT_CREATE);

            Route::put('/{commentId}', [TaskCommentController::class, 'update']);

            Route::delete('/{commentId}', [TaskCommentController::class, 'destroy'])
                ->middleware('permission:' . Permission::COMMENT_DELETE);
        });
    });

});
