<?php

namespace App\Modules\Labels;

use App\Modules\Labels\Models\Label;
use App\Modules\Task\Models\Task;

class LabelService
{
    public function getAllLabels(string $tenantId)
    {
        return Label::where('tenant_id', $tenantId)->orderBy('name')->get();
    }

    public function createLabel(string $tenantId, array $data): Label
    {
        return Label::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'color' => $data['color'] ?? '#6366f1',
        ]);
    }

    public function updateLabel(string $labelId, string $tenantId, array $data): Label
    {
        $label = Label::where('tenant_id', $tenantId)->findOrFail($labelId);
        $label->update($data);

        return $label->fresh();
    }

    public function deleteLabel(string $labelId, string $tenantId): void
    {
        $label = Label::where('tenant_id', $tenantId)->findOrFail($labelId);
        $label->delete();
    }

    public function getLabelsForTask(Task $task)
    {
        return $task->labels;
    }

    public function syncLabels(Task $task, array $labelIds): void
    {
        $task->labels()->sync($labelIds);
    }
}
