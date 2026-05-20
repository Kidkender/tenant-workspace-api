<?php

namespace App\Policies;

use App\Common\Constants\Permission;
use App\Modules\Access\Services\PermissionService;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use App\Modules\User\Models\User;

class TaskCommentPolicy
{
    public function __construct(private PermissionService $permissionService)
    {
    }

    public function create(User $user, Task $task): bool
    {
        return $this->permissionService->hasPermission($user, Permission::COMMENT_CREATE, $task->tenant_id);
    }

    public function update(User $user, TaskComment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        if (!$this->permissionService->hasPermission($user, Permission::COMMENT_DELETE, $comment->tenant_id)) {
            return false;
        }

        if ($this->permissionService->hasPermission($user, Permission::COMMENT_DELETE_ANY, $comment->tenant_id)) {
            return true;
        }

        return $comment->user_id === $user->id;
    }
}
