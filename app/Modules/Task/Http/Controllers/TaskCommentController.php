<?php

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Models\TaskComment;
use App\Modules\Task\Requests\CreateCommentRequest;
use App\Modules\Task\Requests\UpdateCommentRequest;
use App\Modules\Task\Services\TaskCommentService;
use App\Modules\Task\Services\TaskService;

class TaskCommentController extends Controller
{
    public function __construct(
        private TaskService $taskService,
        private TaskCommentService $commentService
    ) {
    }

    public function index(string $taskId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);

        $comments = $this->commentService->getComments($task);

        return $this->success($comments, [], 'Comments retrieved successfully', 200);
    }

    public function store(CreateCommentRequest $request, string $taskId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);

        $this->authorize('create', [TaskComment::class, $task]);

        $comment = $this->commentService->createComment($task, $request->user(), $request->validated());

        return $this->success($comment, [], 'Comment created successfully', 201);
    }

    public function update(UpdateCommentRequest $request, string $taskId, string $commentId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);
        $comment = $this->commentService->getComment($task, $commentId);

        $this->authorize('update', $comment);

        $updated = $this->commentService->updateComment($comment, $request->validated());

        return $this->success($updated, [], 'Comment updated successfully', 200);
    }

    public function destroy(string $taskId, string $commentId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);
        $comment = $this->commentService->getComment($task, $commentId);

        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return $this->success(null, [], 'Comment deleted successfully', 200);
    }
}
