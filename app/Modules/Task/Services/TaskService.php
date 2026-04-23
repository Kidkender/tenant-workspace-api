<?php

namespace App\Modules\Task\Services;

use App\Modules\Task\Models\Task;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function getTasks(string $tenantId): Collection
    {
        return Task::where('tenant_id', $tenantId)->get();
    }

    public function getTask(string $tenantId, string $id): Task
    {
        return Task::where('tenant_id', $tenantId)->findOrFail($id);
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }

    public function createTask(array $data, User $user, Tenant $tenant): Task
    {
        if (! $tenant->hasUser($user->id)) {
            throw new \Exception('User is not a member of this tenant');
        }

        return Task::create([
            'tenant_id' => $tenant->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => 'todo',
            'created_by' => $user->id,
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }
}
