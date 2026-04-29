<?php

namespace App\Notifications;

use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCommented extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected TaskComment $comment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'comment_id' => $this->comment->id,
            'commented_by' => $this->comment->user_id,
            'tenant_id' => $this->task->tenant_id,
            'message' => 'New comment on task: '.$this->task->title,
        ];
    }
}
