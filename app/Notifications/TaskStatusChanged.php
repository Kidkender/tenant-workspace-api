<?php

namespace App\Notifications;

use App\Modules\Task\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected string $oldStatus,
        protected string $newStatus
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'tenant_id' => $this->task->tenant_id,
            'message' => "Task \"{$this->task->title}\" status changed from {$this->oldStatus} to {$this->newStatus}",
        ];
    }
}
