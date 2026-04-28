<?php

namespace App\Modules\Dashboard;

use App\Http\Controllers\Controller;
use App\Modules\Activity\Models\ActivityLog;
use App\Modules\Task\Models\Task;
use App\Modules\Tenant\Models\TenantUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app('tenant')->id;

        $taskCounts = Task::where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $memberCount = TenantUser::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();

        $recentActivity = ActivityLog::with('user:id,name,email')
            ->where('tenant_id', $tenantId)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'user' => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name] : null,
                'created_at' => $log->created_at,
            ]);

        return $this->success([
            'tasks' => [
                'total' => $taskCounts->sum(),
                'todo' => (int) $taskCounts->get('todo', 0),
                'doing' => (int) $taskCounts->get('doing', 0),
                'completed' => (int) $taskCounts->get('completed', 0),
            ],
            'members' => $memberCount,
            'recent_activity' => $recentActivity,
        ]);
    }
}
