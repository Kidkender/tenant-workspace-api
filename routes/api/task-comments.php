<?php

use App\Common\Constants\Permission;
use App\Modules\File\TaskAttachmentController;
use App\Modules\Task\Http\Controllers\TaskCommentController;
use Illuminate\Support\Facades\Route;

Route::prefix('{taskId}/comments')->name('comments.')->group(function () {
    Route::middleware('permission:'.Permission::TASK_VIEW)->group(function () {
        Route::get('/', [TaskCommentController::class, 'index'])->name('index');
    });

    Route::middleware('permission:'.Permission::COMMENT_CREATE)->group(function () {
        Route::post('/', [TaskCommentController::class, 'store'])->name('store');
    });

    Route::put('/{commentId}', [TaskCommentController::class, 'update'])->name('update');

    Route::middleware('permission:'.Permission::COMMENT_DELETE)->group(function () {
        Route::delete('/{commentId}', [TaskCommentController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:'.Permission::ATTACHMENT_CREATE)->group(function () {
        Route::post('/{commentId}/attachments', [TaskAttachmentController::class, 'storeComment'])->name('attachments.store');
    });
});
