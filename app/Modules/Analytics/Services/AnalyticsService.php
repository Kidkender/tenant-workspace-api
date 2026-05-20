<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\DTOs\DateRange;
use App\Modules\Task\Models\Task;
use Carbon\CarbonImmutable;

class AnalyticsService
{
    public function completionRate(string $tenantId, DateRange $range): array
    {
        $row = Task::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', ['completed'])
            ->first();

        $total = (int) $row->total;
        $completed = (int) $row->completed;
        $rate = $total > 0 ? round($completed / $total, 4) : 0.0;

        return [
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
            'total' => $total,
            'completed' => $completed,
            'rate' => $rate,
        ];
    }

    public function taskPerUser(string $tenantId, DateRange $range): array
    {
        $data = Task::where('tasks.tenant_id', $tenantId)
            ->whereBetween('tasks.created_at', [$range->from, $range->to])
            ->join('users', 'users.id', '=', 'tasks.assigned_to')
            ->selectRaw('
            tasks.assigned_to,
            users.name,
            COUNT(*) as total,
            SUM(CASE WHEN tasks.status = "todo" THEN 1 ELSE 0 END) as todo,
            SUM(CASE WHEN tasks.status = "doing" THEN 1 ELSE 0 END) as doing,
            SUM(CASE WHEN tasks.status = "completed" THEN 1 ELSE 0 END) as completed')
            ->groupBy('tasks.assigned_to', 'users.name')
            ->get()
            ->map(fn($row) => [
                'user_id' => $row->user_id,
                'name' => $row->name,
                'total' => (int) $row->total,
                'todo' => (int) $row->todo,
                'doing' => (int) $row->doing,
                'completed' => (int) $row->completed,
                'completion_rate' => $row->total > 0
                    ? round($row->completed / $row->total, 4)
                    : 0.0,
            ])->toArray();

        return $data;
    }

    public function overdueRate(string $tenantId): array
    {
        $openTotal = Task::where('tenant_id', $tenantId)
            ->whereIn('status', ['todo', 'doing'])
            ->count();

        $overdue = Task::where('tenant_id', $tenantId)
            ->whereIn('status', ['todo', 'doing'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        $rate = $openTotal > 0 ? round($overdue / $openTotal, 4) : 0.0;

        return [
            'open_total' => $openTotal,
            'overdue' => $overdue,
            'rate' => $rate,
        ];
    }

    public function priorityDistribution(string $tenantId, DateRange $range): array
    {
        $rows = Task::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        return [
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
            'low' => (int) ($rows['low'] ?? 0),
            'medium' => (int) ($rows['medium'] ?? 0),
            'high' => (int) ($rows['high'] ?? 0),
            'urgent' => (int) ($rows['urgent'] ?? 0),
            'total' => (int) $rows->sum(),
        ];
    }

    public function trend(string $tenantId, DateRange $range, string $granularity, string $metric): array
    {
        $tasks = Task::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$range->from, $range->to])
            ->get(['created_at', 'updated_at', 'status']);

        $buckets = [];

        $cursor = $range->from->startOfDay();
        $end = $range->to->endOfDay();

        while ($cursor->lte($end)) {
            $key = $granularity === 'week'
                ? $cursor->startOfWeek()->toDateString()
                : $cursor->toDateString();

            $buckets[$key] = ['bucket' => $key, 'created' => 0, 'completed' => 0];

            $cursor = $granularity === 'week' ? $cursor->addWeek() : $cursor->addDay();
        }

        foreach ($tasks as $task) {
            $createdAt = CarbonImmutable::parse($task->created_at);
            $createdKey = $granularity === 'week'
                ? $createdAt->startOfWeek()->toDateString()
                : $createdAt->toDateString();

            if (isset($buckets[$createdKey]) && $metric !== 'completed') {
                $buckets[$createdKey]['created']++;
            }

            if ($task->status === 'completed' && $metric !== 'created') {
                $completedAt = CarbonImmutable::parse($task->updated_at);
                $completedKey = $granularity === 'week'
                    ? $completedAt->startOfWeek()->toDateString()
                    : $completedAt->toDateString();

                if (isset($buckets[$completedKey])) {
                    $buckets[$completedKey]['completed']++;
                }
            }
        }

        return [
            'granularity' => $granularity,
            'metric' => $metric,
            'points' => array_values($buckets),
        ];
    }
}
