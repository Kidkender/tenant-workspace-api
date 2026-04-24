<?php

namespace App\Modules\Activity;

use App\Modules\Activity\Models\ActivityLog;

class ActivityLogService
{
    public function logActivity($action, $entityType, $entityId, $metadata)
    {
        ActivityLog::create([
            'tenant_id' => app('tenant')->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => $metadata,
        ]);
    }

    public function getLogs($filters)
    {
        return ActivityLog::query()
            ->where('tenant_id', app('tenant')->id)
            ->where('entity_type', $filters['entity_type'])
            ->where('entity_id', $filters['entity_id'])
            ->where('action', $filters['action'])
            ->latest()
            ->limit($filters['limit'])
            ->paginate();
    }
}
