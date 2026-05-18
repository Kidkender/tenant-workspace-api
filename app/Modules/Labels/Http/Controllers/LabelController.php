<?php

namespace App\Modules\Labels\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Labels\LabelService;
use App\Modules\Labels\Requests\CreateLabelRequest;
use App\Modules\Labels\Requests\SyncTaskLabelsRequest;
use App\Modules\Labels\Requests\UpdateLabelRequest;
use App\Modules\Task\Services\TaskService;

class LabelController extends Controller
{
    public function __construct(
        private LabelService $labelService,
        private TaskService $taskService,
    ) {}

    public function index()
    {
        $tenant = app('tenant');

        return $this->success($this->labelService->getAllLabels($tenant->id));
    }

    public function store(CreateLabelRequest $request)
    {
        $tenant = app('tenant');

        $label = $this->labelService->createLabel($tenant->id, $request->validated());

        return $this->success($label, [], 'Label created successfully', 201);
    }

    public function update(UpdateLabelRequest $request, string $id)
    {
        $tenant = app('tenant');

        $label = $this->labelService->updateLabel($id, $tenant->id, $request->validated());

        return $this->success($label, [], 'Label updated successfully');
    }

    public function destroy(string $id)
    {
        $tenant = app('tenant');

        $this->labelService->deleteLabel($id, $tenant->id);

        return $this->success(null, [], 'Label deleted successfully');
    }

    public function syncTaskLabels(SyncTaskLabelsRequest $request, string $taskId)
    {
        $tenant = app('tenant');
        $task = $this->taskService->getTask($tenant->id, $taskId);

        $this->labelService->syncLabels($task, $request->validated()['label_ids']);

        return $this->success($task->labels, [], 'Labels synced successfully');
    }
}
