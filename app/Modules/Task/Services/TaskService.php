<?php

namespace App\Modules\Task\Services;

use App\Common\Constants\ErrorCode;
use App\Common\Enumeration\TaskPriority;
use App\Modules\Activity\ActivityLogService;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Requests\TaskFilterRequest;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\User\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskStatusChanged;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class TaskService
{
    public function __construct(
        private ActivityLogService $activityLogService,
        private BillingService $billingService,
    ) {}

    public function getTasks(TaskFilterRequest $request): LengthAwarePaginator
    {
        $limit = $request->input('limit', 10);

        return Task::query()
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->assigned_to, fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->created_by, fn ($q) => $q->where('created_by', $request->created_by))
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate($limit);
    }

    public function getTask(string $tenantId, string $id): Task
    {
        return Task::where('tenant_id', $tenantId)->with('labels')->findOrFail($id);
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }

    public function createTask(array $data, User $user, Tenant $tenant): Task
    {
        if (! $tenant->hasUser($user->id)) {
            throw new UnauthorizedHttpException(ErrorCode::TENANT_NOT_MEMBER);
        }

        if (! $this->billingService->canCreateTask($tenant->id)) {
            throw new BadRequestException(ErrorCode::TASK_LIMIT_EXCEEDED);
        }

        $task = Task::create([
            'tenant_id' => $tenant->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => 'todo',
            'priority' => $data['priority'] ?? TaskPriority::Low,
            'created_by' => $user->id,
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);

        $this->activityLogService->logActivity('create', 'task', $task->id, $data);

        if (isset($data['assigned_to'])) {
            $assignee = User::find($data['assigned_to']);
            $assignee?->notify(new TaskAssigned($task));
        }

        return $task;
    }

    public function updateTask(Task $task, array $data): Task
    {
        $oldStatus = $task->status;

        $task->update($data);

        $this->activityLogService->logActivity('update', 'task', $task->id, $data);

        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $creator = User::find($task->created_by);
            $creator?->notify(new TaskStatusChanged($task, $oldStatus, $data['status']));
        }

        return $task;
    }

    public function assign(Task $task, Tenant $tenant, string $userId): Task
    {
        $user = User::findOrFail($userId);

        if (! $tenant->hasUser($user->id)) {
            throw new AuthorizationException('User is not a member of this tenant');
        }

        $task->update(['assigned_to' => $userId]);
        $task->refresh();

        $this->activityLogService->logActivity('assign', 'task', $task->id, ['assigned_to' => $userId]);

        $user->notify(new TaskAssigned($task));

        return $task;
    }
}
