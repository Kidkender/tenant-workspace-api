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
            ->with('user:id,name,email')
            ->where('tenant_id', app('tenant')->id)
            ->when(! empty($filters['entity_type']), fn ($q) => $q->where('entity_type', $filters['entity_type']))
            ->when(! empty($filters['entity_id']), fn ($q) => $q->where('entity_id', $filters['entity_id']))
            ->when(! empty($filters['user_id']), fn ($q) => $q->where('user_id', $filters['user_id']))
            ->when(! empty($filters['action']), fn ($q) => $q->where('action', $filters['action']))
            ->latest()
            ->paginate($filters['limit'] ?? 20);
    }
}
