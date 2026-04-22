<?php

namespace App\Modules\Tenant;

use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Models\TenantUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            $ownerRoleId = Cache::rememberForever('role_id_owner', fn () => DB::table('roles')->where('name', 'owner')->value('id'));

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
}
