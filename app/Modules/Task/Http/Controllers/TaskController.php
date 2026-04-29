<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Requests\CreateTaskRequest;
use App\Modules\Task\Requests\TaskFilterRequest;
use App\Modules\Task\Requests\UpdateTaskRequest;
use App\Modules\Task\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index(TaskFilterRequest $request)
    {
        $tenant = app('tenant');
        $this->authorize('viewAny', [Task::class, $tenant->id]);

        $tasks = $this->taskService->getTasks($request);

        return $this->success($tasks);
    }

    public function show($id)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $id);

        $this->authorize('view', $task);

        return $this->success($task);
    }

    public function store(CreateTaskRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();

        $tenant = app('tenant');
        $this->authorize('create', [Task::class, $tenant]);

        $task = $this->taskService->createTask($data, $user, $tenant);

        return $this->success($task, [], 'Task created successfully', 201);
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $data = $request->validated();
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $id);

        $this->authorize('update', $task);

        $newTask = $this->taskService->updateTask($task, $data);

        return $this->success($newTask, [], 'Task updated successfully', 200);
    }

    public function destroy($id)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $id);

        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return $this->success(null, [], 'Task deleted successfully', 200);
    }

    public function assign(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|uuid|exists:users,id']);

        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $id);

        $this->authorize('update', $task);

        return $this->success(
            $this->taskService->assign($task, $tenant, $request->input('user_id')),
            [],
            'Task assigned successfully',
            200
        );
    }
}
