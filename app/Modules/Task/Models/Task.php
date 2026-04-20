<?php

namespace App\Modules\Task\Models;

use App\Modules\Tenant\Tenant;
use App\Modules\User\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('tasks')]
#[Fillable('tenant_id', 'title', 'description', 'due_date', 'status', 'assigned_to', 'created_by', 'updated_by')]
#[WithoutIncrementing]
class Task extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            "due_date" => "date",
        ];
    }


    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'done';
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }

}
