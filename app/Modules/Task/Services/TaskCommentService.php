<?php

namespace App\Modules\Task\Services;

use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskCommentService
{
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
        return $task->comments()->create([
            'tenant_id' => $task->tenant_id,
            'user_id' => $user->id,
            'content' => $data['content'],
        ]);
    }

    public function updateComment(TaskComment $comment, array $data): TaskComment
    {
        $comment->update(['content' => $data['content']]);

        return $comment;
    }

    public function deleteComment(TaskComment $comment): void
    {
        $comment->delete();
    }
}
