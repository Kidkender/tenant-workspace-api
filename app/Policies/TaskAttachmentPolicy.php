<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Modules\Access\PermissionService;
use App\Modules\File\Models\TaskAttachment;
use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;

class TaskAttachmentPolicy
{
    public function __construct(private PermissionService $permissionService) {}

    public function create(User $user, Task $task): bool
    {
        return $this->permissionService->hasPermission($user, Permission::ATTACHMENT_CREATE, $task->tenant_id);
    }

    public function delete(User $user, TaskAttachment $attachment): bool
    {
        if (! $this->permissionService->hasPermission($user, Permission::ATTACHMENT_DELETE, $attachment->tenant_id)) {
            return false;
        }

        if ($this->permissionService->hasPermission($user, Permission::ATTACHMENT_DELETE_ANY, $attachment->tenant_id)) {
            return true;
        }

        return $attachment->uploaded_by === $user->id;
    }
}
