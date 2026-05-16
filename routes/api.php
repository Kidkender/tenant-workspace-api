<?php

use App\Constants\Permission;
use App\Http\Middleware\ResolveTenant;
use App\Modules\Access\Models\Role;
use App\Modules\Access\RoleController;
use App\Modules\Activity\ActivityLogController;
use App\Modules\Auth\AuthController;
use App\Modules\Billing\PlanController;
use App\Modules\Dashboard\DashboardController;
use App\Modules\File\TaskAttachmentController;
use App\Modules\Task\Http\Controllers\TaskCommentController;
use App\Modules\Task\Http\Controllers\TaskController;
use App\Modules\Tenant\TenantController;
use App\Modules\User\Models\User;
use App\Modules\User\UserController;
use App\Notifications\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/plans', [PlanController::class, 'index']);
Route::get('/plans/{id}', [PlanController::class, 'show']);

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
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

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
    Route::delete('/tenant/members/{userId}', [TenantController::class, 'removeMember']);
    Route::put('/tenant/members/{userId}/role', [TenantController::class, 'updateMemberRole']);
    Route::put('/tenants/settings', [TenantController::class, 'update']);
    Route::post('/tenants/invite', [TenantController::class, 'invite']);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/permissions', [RoleController::class, 'permissions']);
    Route::post('/roles/custom', [RoleController::class, 'store']);
    Route::put('/roles/custom/{roleId}', [RoleController::class, 'update']);
    Route::delete('/roles/custom/{roleId}', [RoleController::class, 'destroy']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    Route::get('/subscription', [PlanController::class, 'subscription']);
    Route::post('/subscription', [PlanController::class, 'subscribe']);

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

        Route::post('/{id}/assign', [TaskController::class, 'assign'])
            ->middleware('permission:' . Permission::TASK_UPDATE);

        Route::prefix('{taskId}/comments')->group(function () {

            Route::get('/', [TaskCommentController::class, 'index'])
                ->middleware('permission:' . Permission::TASK_VIEW);

            Route::post('/', [TaskCommentController::class, 'store'])
                ->middleware('permission:' . Permission::COMMENT_CREATE);

            Route::put('/{commentId}', [TaskCommentController::class, 'update']);

            Route::delete('/{commentId}', [TaskCommentController::class, 'destroy'])
                ->middleware('permission:' . Permission::COMMENT_DELETE);

            Route::post('/{commentId}/attachments', [TaskAttachmentController::class, 'storeComment'])
                ->middleware('permission:' . Permission::ATTACHMENT_CREATE);
        });

        Route::prefix('{taskId}/attachments')->group(function () {

            Route::get('/', [TaskAttachmentController::class, 'index'])
                ->middleware('permission:' . Permission::TASK_VIEW);

            Route::post('/', [TaskAttachmentController::class, 'store'])
                ->middleware('permission:' . Permission::ATTACHMENT_CREATE);

            Route::get('/{attachmentId}/download', [TaskAttachmentController::class, 'download'])
                ->middleware('permission:' . Permission::TASK_VIEW);

            Route::delete('/{attachmentId}', [TaskAttachmentController::class, 'destroy'])
                ->middleware('permission:' . Permission::ATTACHMENT_DELETE);
        });
    });

});
