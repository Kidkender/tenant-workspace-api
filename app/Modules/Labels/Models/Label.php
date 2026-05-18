<?php

namespace App\Modules\Labels\Models;

use App\Modules\Task\Models\Task;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read Tenant $tenant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Label whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Table('labels')]
#[Fillable('tenant_id', 'name', 'color')]
class Label extends Model
{
    use HasUuids;

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_labels', 'label_id', 'task_id');
    }
}
