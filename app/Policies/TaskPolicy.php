<?php

namespace App\Policies;

use App\Common\Constants\Permission;
use App\Modules\Access\Services\PermissionService;
use App\Modules\Task\Models\Task;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\User\Models\User;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct(private PermissionService $permissionService)
    {
        //
    }

    /**
     * View any task.
     */
    public function viewAny(User $user, string $tenantId): bool
    {
        return $this->permissionService->hasPermission($user, Permission::TASK_VIEW, $tenantId);
    }

    public function view(User $user, Task $task): bool
    {
        return $this->permissionService->hasPermission($user, Permission::TASK_VIEW, $task->tenant_id);
    }

    /**
     * Create a new task.
     */
    public function create(User $user, Tenant $tenant): bool
    {
        return $this->permissionService->hasPermission($user, Permission::TASK_CREATE, $tenant->id);
    }

    /**
     * Update task
     */
    public function update(User $user, Task $task): bool
    {
        if (!$this->permissionService->hasPermission($user, Permission::TASK_UPDATE, $task->tenant_id)) {
            return false;
        }

        return $task->created_by === $user->id;
    }

    /**
     * Delete a task.
     */
    public function delete(User $user, Task $task): bool
    {
        if (!$this->permissionService->hasPermission($user, Permission::TASK_DELETE, $task->tenant_id)) {
            return false;
        }

        if (!$this->permissionService->hasPermission($user, Permission::TASK_DELETE_ANY, $task->tenant_id)) {
            return true;
        }

        return $task->created_by === $user->id;
    }
}
