<?php

use App\Common\Constants\Permission;
use App\Modules\File\TaskAttachmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('{taskId}/attachments')->name('attachments.')->group(function () {
    Route::middleware('permission:'.Permission::TASK_VIEW)->group(function () {
        Route::get('/', [TaskAttachmentController::class, 'index'])->name('index');
        Route::get('/{attachmentId}/download', [TaskAttachmentController::class, 'download'])->name('download');
    });

    Route::middleware('permission:'.Permission::ATTACHMENT_CREATE)->group(function () {
        Route::post('/', [TaskAttachmentController::class, 'store'])->name('store');
    });

    Route::middleware('permission:'.Permission::ATTACHMENT_DELETE)->group(function () {
        Route::delete('/{attachmentId}', [TaskAttachmentController::class, 'destroy'])->name('destroy');
    });
});
