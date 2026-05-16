<?php

namespace App\Modules\File;

use App\Http\Controllers\Controller;
use App\Modules\File\Models\TaskAttachment;
use App\Modules\File\Requests\StoreAttachmentRequest;
use App\Modules\File\TaskAttachmentService;
use App\Modules\Task\Services\TaskCommentService;
use App\Modules\Task\Services\TaskService;


class TaskAttachmentController extends Controller
{
    public function __construct(
        private TaskService $taskService,
        private TaskCommentService $taskCommentService,
        private TaskAttachmentService $attachmentService
    ) {
    }

    public function index(string $taskId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);

        $attachments = $this->attachmentService->getAttachments($task);

        return $this->success($attachments, [], 'Attachments retrieved successfully');
    }

    public function store(StoreAttachmentRequest $request, string $taskId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);

        $this->authorize('create', [TaskAttachment::class, $task]);

        $attachment = $this->attachmentService->upload($tenant, $task, $request->user(), $request->file('file'));

        return $this->success($attachment->load('uploader'), [], 'Attachment uploaded successfully', 201);
    }

    public function download(string $taskId, string $attachmentId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);
        $attachment = $this->attachmentService->getAttachment($task, $attachmentId);

        return $this->attachmentService->buildDownloadResponse($attachment);
    }

    public function destroy(string $taskId, string $attachmentId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);
        $attachment = $this->attachmentService->getAttachment($task, $attachmentId);

        $this->authorize('delete', $attachment);

        $this->attachmentService->delete($attachment);

        return $this->success(null, [], 'Attachment deleted successfully');
    }

    public function storeComment(StoreAttachmentRequest $request, string $taskId, string $commentId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);
        $comment = $this->taskCommentService->getComment($task, $commentId);

        $this->authorize('create', [TaskAttachment::class, $task]);

        $attachment = $this->attachmentService->uploadComment($tenant, $task, $comment, $request->user(), $request->file('file'));

        return $this->success($attachment->load('uploader'), [], 'Attachment uploaded successfully', 201);
    }
}
