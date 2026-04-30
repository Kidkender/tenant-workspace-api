<?php

namespace App\Modules\Tenant;

use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Models\TenantInvitation;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\User\Models\User;
use App\Notifications\TenantInvite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class TenantService
{
    public function createTenant(array $data, $user): Tenant
    {
        return DB::transaction(function () use ($data, $user) {
            $slug = $this->generateUniqueSlug($data['name']);
            $tenant = Tenant::create([
                'id' => Str::uuid(),
                'name' => $data['name'],
                'slug' => $slug,
                'owner_user_id' => $user->id,
            ]);

            $ownerRoleId = Cache::rememberForever('role_id_owner', fn() => DB::table('roles')->where('name', 'owner')->value('id'));

            TenantUser::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'role_id' => $ownerRoleId,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $tenant;
        });
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function invite(Tenant $tenant, string $email, int $roleId)
    {
        $invitation = TenantInvitation::create(
            [
                'tenant_id' => $tenant->id,
                'email' => $email,
                'role_id' => $roleId,
                'token' => Str::uuid(),
                'status' => 'pending',
                'expired_at' => now()->addDays(2),
            ]
        );

        Notification::route('mail', $email)->notify(new TenantInvite($invitation));

        return $invitation;
    }

    public function accept(string $token, User $user)
    {
        $invitation = TenantInvitation::where('token', $token)->where('status', 'pending')->firstOrFail();

        if ($invitation->expired_at < now()) {
            throw new BadRequestException('Invitation Expired');
        }

        DB::transaction(function () use ($invitation, $user) {
            TenantUser::create([
                'tenant_id' => $invitation->tenant_id,
                'user_id' => $user->id,
                'role_id' => $invitation->role_id,
            ]);

            $invitation->update([
                'status' => 'accepted',
            ]);
        });
    }
}
