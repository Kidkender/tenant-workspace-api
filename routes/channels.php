<?php

use App\Common\Constants\Permission;
use App\Modules\Access\Services\PermissionService;
use App\Modules\Task\Models\Task;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum', \App\Http\Middleware\ResolveTenant::class]]);

Broadcast::channel('App.Models.User.{id}', fn($user, $id) => (int) $user->id === (int) $id);

Broadcast::channel('tasks.{taskId}', function ($user, $taskId) {
    $task = Task::find($taskId);

    if (!$task) {
        return false;
    }

    return app(PermissionService::class)
        ->hasPermission($user, Permission::TASK_VIEW, $task->tenant_id);
});
