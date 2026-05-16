<?php

namespace App\Modules\File\Models;

use App\Modules\Task\Models\Task;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $task_id
 * @property string $tenant_id
 * @property string $uploaded_by
 * @property string $file_name
 * @property string $file_path
 * @property string $mime_type
 * @property int $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Task $task
 * @property-read User $uploader
 * @property string|null $comment_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAttachment whereUploadedBy($value)
 * @mixin \Eloquent
 */
#[Table('task_attachments')]
#[Fillable('task_id', 'comment_id', 'tenant_id', 'uploaded_by', 'file_name', 'file_path', 'mime_type', 'size')]
class TaskAttachment extends Model
{
    use HasFactory, HasUuids;

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
