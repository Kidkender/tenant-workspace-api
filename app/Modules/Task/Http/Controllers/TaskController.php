<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|uuid',
            'due_date' => 'nullable|date',
        ]);
        Log::info('Validated task data', $data);

        $user = $request->user();

        $tenant = app('tenant');

        $task = $this->taskService->createTask($data, $user, $tenant);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }
}
