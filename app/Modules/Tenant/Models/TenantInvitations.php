<?php

namespace App\Modules\Tenant\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('tenant_invitations')]
#[Fillable(['tenant_id', 'role_id', 'token', 'status', 'expired_at'])]
class TenantInvitation extends Model
{

}
