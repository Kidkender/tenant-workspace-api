<?php

namespace App\Modules\Task\Services;

use App\Modules\Activity\ActivityLogService;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskCommentService
{
    public function __construct(private ActivityLogService $activityLogService)
    {
    }

    public function getComments(Task $task): Collection
    {
        return $task->comments()->with('user')->latest()->get();
    }

    public function getComment(Task $task, string $id): TaskComment
    {
        return $task->comments()->findOrFail($id);
    }

    public function createComment(Task $task, User $user, array $data): TaskComment
    {
        $comment = $task->comments()->create([
            'tenant_id' => $task->tenant_id,
            'user_id' => $user->id,
            'content' => $data['content'],
        ]);

        $this->activityLogService->logActivity('create', 'task_comment', $comment->id, $data);

        return $comment;
    }

    public function updateComment(TaskComment $comment, array $data): TaskComment
    {
        $comment->update(['content' => $data['content']]);

        $this->activityLogService->logActivity('update', 'task_comment', $comment->id, $data);

        return $comment;
    }

    public function deleteComment(TaskComment $comment): void
    {
        $comment->delete();

        $this->activityLogService->logActivity('delete', 'task_comment', $comment->id, []);
    }
}
