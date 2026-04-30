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
 * @mixin \Eloquent
 */
#[Table('tenant_invitations')]
#[Fillable(['tenant_id', 'role_id', 'token', 'status', 'expired_at'])]
class TenantInvitation extends Model
{
    use Notifiable;
}
