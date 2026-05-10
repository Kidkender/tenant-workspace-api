<?php

namespace App\Modules\File;

use App\Constants\ErrorCode;
use App\Modules\File\Models\TaskAttachment;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TaskAttachmentService
{
    public function getAttachments(Task $task): Collection
    {
        return $task->attachments()->with('uploader')->latest()->get();
    }

    public function getAttachment(Task $task, string $id): TaskAttachment
    {
        $attachment = $task->attachments()->find($id);

        if (!$attachment) {
            throw new NotFoundHttpException(ErrorCode::ATTACHMENT_NOT_FOUND);
        }

        return $attachment;
    }

    public function upload(Task $task, User $user, UploadedFile $file): TaskAttachment
    {
        $path = $file->store(
            "attachments/{$task->tenant_id}/{$task->id}",
            'local'
        );

        if ($path === false) {
            throw new UnprocessableEntityHttpException(ErrorCode::ATTACHMENT_UPLOAD_FAILED);
        }

        return TaskAttachment::create([
            'task_id' => $task->id,
            'tenant_id' => $task->tenant_id,
            'uploaded_by' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function delete(TaskAttachment $attachment): void
    {
        Storage::disk('local')->delete($attachment->file_path);
        $attachment->delete();
    }

    public function buildDownloadResponse(TaskAttachment $attachment): StreamedResponse
    {
        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->file_name,
            ['Content-Type' => $attachment->mime_type]
        );
    }

    public function uploadComment(Task $task, TaskComment $comment, User $user, UploadedFile $file): TaskAttachment
    {
        $path = $file->store(
            "attachments/{$task->tenant_id}/{$task->id}",
            'local'
        );

        if ($path === false) {
            throw new UnprocessableEntityHttpException(ErrorCode::ATTACHMENT_UPLOAD_FAILED);
        }

        return TaskAttachment::create([
            'task_id' => $task->id,
            'comment_id' => $comment->id,
            'tenant_id' => $task->tenant_id,
            'uploaded_by' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}
