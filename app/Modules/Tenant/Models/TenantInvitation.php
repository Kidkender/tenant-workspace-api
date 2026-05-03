<?php

namespace App\Modules\Tenant\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation query()
 * @property int $id
 * @property string $tenant_id
 * @property int $role_id
 * @property string $token
 * @property string $status
 * @property string $expired_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantInvitation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Table('tenant_invitations')]
#[Fillable(['tenant_id', 'role_id', 'token', 'status', 'expired_at'])]
class TenantInvitation extends Model
{
    use Notifiable;
}
