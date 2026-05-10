<?php

namespace Database\Seeders;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\PlanFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        PlanFeature::truncate();
        Plan::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Plan::insert([
            [
                'id' => 1,
                'name' => 'Free',
                'price_monthly' => 0,
                'price_yearly' => 0,
            ],
            [
                'id' => 2,
                'name' => 'Pro',
                'price_monthly' => 299000,
                'price_yearly' => 2990000,
            ],
            [
                'id' => 3,
                'name' => 'Enterprise',
                'price_monthly' => 999000,
                'price_yearly' => 9990000,
            ],
        ]);

        PlanFeature::insert([

            // FREE
            ['plan_id' => 1, 'feature_key' => 'can_create_task', 'value' => 'true'],
            ['plan_id' => 1, 'feature_key' => 'task_limit', 'value' => '50'],
            ['plan_id' => 1, 'feature_key' => 'member_limit', 'value' => '10'],
            ['plan_id' => 1, 'feature_key' => 'tenant_limit', 'value' => '2'],
            ['plan_id' => 1, 'feature_key' => 'storage_mb', 'value' => '100'],
            ['plan_id' => 1, 'feature_key' => 'activity_log', 'value' => 'false'],
            ['plan_id' => 1, 'feature_key' => 'email_invite', 'value' => 'false'],
            ['plan_id' => 1, 'feature_key' => 'custom_roles', 'value' => 'false'],
            ['plan_id' => 1, 'feature_key' => 'analytics', 'value' => 'false'],
            ['plan_id' => 1, 'feature_key' => 'realtime', 'value' => 'false'],

            // PRO
            ['plan_id' => 2, 'feature_key' => 'can_create_task', 'value' => 'true'],
            ['plan_id' => 2, 'feature_key' => 'task_limit', 'value' => '500'],
            ['plan_id' => 2, 'feature_key' => 'member_limit', 'value' => '20'],
            ['plan_id' => 2, 'feature_key' => 'tenant_limit', 'value' => '5'],
            ['plan_id' => 2, 'feature_key' => 'storage_mb', 'value' => '5000'],
            ['plan_id' => 2, 'feature_key' => 'activity_log', 'value' => 'true'],
            ['plan_id' => 2, 'feature_key' => 'email_invite', 'value' => 'true'],
            ['plan_id' => 2, 'feature_key' => 'custom_roles', 'value' => 'false'],
            ['plan_id' => 2, 'feature_key' => 'analytics', 'value' => 'false'],
            ['plan_id' => 2, 'feature_key' => 'realtime', 'value' => 'true'],

            // ENTERPRISE
            ['plan_id' => 3, 'feature_key' => 'can_create_task', 'value' => 'true'],
            ['plan_id' => 3, 'feature_key' => 'task_limit', 'value' => null],
            ['plan_id' => 3, 'feature_key' => 'member_limit', 'value' => null],
            ['plan_id' => 3, 'feature_key' => 'tenant_limit', 'value' => null],
            ['plan_id' => 3, 'feature_key' => 'storage_mb', 'value' => null],
            ['plan_id' => 3, 'feature_key' => 'activity_log', 'value' => 'true'],
            ['plan_id' => 3, 'feature_key' => 'email_invite', 'value' => 'true'],
            ['plan_id' => 3, 'feature_key' => 'custom_roles', 'value' => 'true'],
            ['plan_id' => 3, 'feature_key' => 'analytics', 'value' => 'true'],
            ['plan_id' => 3, 'feature_key' => 'realtime', 'value' => 'true'],
        ]);
    }
}
