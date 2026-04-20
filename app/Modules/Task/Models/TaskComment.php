<?php

namespace App\Modules\Task\Models;

use App\Modules\Tenant\Tenant;
use App\Modules\User\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

#[Table('task_comments')]
#[Fillable('task_id', 'tenant_id', 'user_id', 'content')]
class TaskComment extends Model
{
    use HasFactory, HasUuids;

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
