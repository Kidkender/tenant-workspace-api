<?php

use App\Constants\Permission;
use App\Modules\Access\PermissionService;
use App\Modules\Task\Models\Task;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tasks.{taskId}', function ($user, $taskId) {
    $task = Task::find($taskId);

    if (!$task) {
        return false;
    }

    return app(PermissionService::class)
        ->hasPermission($user, Permission::TASK_VIEW, $task->tenant_id);
});
