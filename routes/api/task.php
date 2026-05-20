<?php

use App\Common\Constants\Permission;
use App\Modules\Task\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('tasks')
    ->name('tasks.')
    ->group(function () {

        Route::middleware('permission:'.Permission::TASK_VIEW)
            ->group(function () {

                Route::get('/', [TaskController::class, 'index'])
                    ->name('index');

                Route::get('/{task}', [TaskController::class, 'show'])
                    ->name('show');
            });

        Route::middleware('permission:'.Permission::TASK_CREATE)
            ->group(function () {

                Route::post('/', [TaskController::class, 'store'])
                    ->name('store');
            });

        Route::middleware('permission:'.Permission::TASK_UPDATE)
            ->group(function () {

                Route::put('/{task}', [TaskController::class, 'update'])
                    ->name('update');

                Route::post('/{task}/assign', [TaskController::class, 'assign'])
                    ->name('assign');
            });

        Route::middleware('permission:'.Permission::TASK_DELETE)
            ->group(function () {

                Route::delete('/{task}', [TaskController::class, 'destroy'])
                    ->name('destroy');
            });

        require __DIR__.'/task-comments.php';
        require __DIR__.'/task-attachments.php';
    });
