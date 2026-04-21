<?php
namespace App\Modules\Task\Services;

use App\Modules\Task\Models\Task;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\User\User;
use Illuminate\Support\Str;

class TaskService
{
    public function createTask(array $data, User $user, Tenant $tenant): Task
    {
        if (!$tenant->hasUser($user->id)) {
            throw new \Exception('User is not a member of this tenant');
        }

        $task = Task::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => 'todo',
            'created_by' => $user->id,
            'assigned_by' => $data['assigned_by'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);

        return $task;
    }
}
