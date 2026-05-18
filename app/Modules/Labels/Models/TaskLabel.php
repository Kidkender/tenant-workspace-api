<?php

namespace App\Modules\Labels\Models;

use App\Modules\Task\Models\Task;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $task_id
 * @property string $label_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Modules\Labels\Models\Label $label
 * @property-read Task $task
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereLabelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskLabel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Table('task_labels')]
#[Fillable('task_id', 'label_id')]
class TaskLabel extends Model
{
    public $incrementing = false;

    protected $primaryKey = null;

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function label()
    {
        return $this->belongsTo(Label::class);
    }
}
