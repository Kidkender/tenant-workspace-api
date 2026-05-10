<?php

namespace Database\Seeders;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $slug = 'demo-workspace';
        $tenant = Tenant::firstOrCreate(
            ['slug' => $slug],
            [
                'id' => Str::uuid(),
                'name' => 'Demo Workspace',
                'owner_user_id' => $user->id,
            ]
        );

        $ownerRoleId = Cache::rememberForever(
            'role_id_owner',
            fn () => DB::table('roles')->where('name', 'owner')->value('id')
        );

        TenantUser::updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $user->id],
            [
                'role_id' => $ownerRoleId,
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        $freePlan = Plan::where('price_monthly', 0)->firstOrFail();

        Subscription::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'id' => Str::uuid(),
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'started_at' => now(),
                'expired_at' => null,
            ]
        );

        $this->command->info('Account seeded:');
        $this->command->info('  Email   : admin@demo.com');
        $this->command->info('  Password: password');
        $this->command->info('  Tenant  : Demo Workspace');
    }
}
