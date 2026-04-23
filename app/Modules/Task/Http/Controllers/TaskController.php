<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Requests\CreateTaskRequest;
use App\Modules\Task\Requests\UpdateTaskRequest;
use App\Modules\Task\Services\TaskService;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index()
    {
        $tenant = app('tenant');
        $this->authorize('viewAny', [Task::class, $tenant->id]);

        $tasks = $this->taskService->getTasks($tenant->id);

        return response()->json([
            'tasks' => $tasks,
        ], 200);
    }

    public function show($id)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $id);

        $this->authorize('view', $task);

        return response()->json([
            'task' => $task,
        ], 200);
    }

    public function store(CreateTaskRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();

        $tenant = app('tenant');
        $this->authorize('create', [Task::class, $tenant]);

        $task = $this->taskService->createTask($data, $user, $tenant);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $data = $request->validated();
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $id);

        $this->authorize('update', $task);

        $newTask = $this->taskService->updateTask($task, $data);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $newTask,
        ], 200);
    }

    public function destroy($id)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $id);

        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
