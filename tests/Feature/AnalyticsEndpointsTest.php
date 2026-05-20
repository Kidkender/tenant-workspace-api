<?php

namespace Tests\Feature;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\PlanFeature;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Task\Models\Task;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;


uses(RefreshDatabase::class);

test('unauthenticated user cannot access analytics endpoints', function () {
    $responses = [
        $this->getJson('/api/analytics/completion-rate'),
        $this->getJson('/api/analytics/tasks-per-user'),
        $this->getJson('/api/analytics/overdue-rate'),
        $this->getJson('/api/analytics/priority-distribution'),
        $this->getJson('/api/analytics/trend'),
    ];

    foreach ($responses as $response) {
        $response->assertUnauthorized();
    }
});

function analyticsUser(): User
{
    return User::factory()->create(['email_verified_at' => now()]);
}

function analyticsTenant(User $owner): Tenant
{
    return Tenant::create([
        'id' => Str::uuid(),
        'name' => fake()->company(),
        'slug' => fake()->unique()->slug(),
        'owner_user_id' => $owner->id,
    ]);
}

function seedAnalyticsPlan(): void
{
    Plan::insert([
        ["id" => 1, "name" => "Free", "price_monthly" => 0, "price_yearly" => 0],
        ["id" => 2, "name" => "Pro", "price_monthly" => 199000, "price_yearly" => 1990000],
    ]);

    PlanFeature::insert([
        ['plan_id' => 1, 'feature_key' => 'analytics', 'value' => 'false'],
        ['plan_id' => 2, 'feature_key' => 'analytics', 'value' => 'true'],
    ]);
}

function subscribeAnalytics(Tenant $tenant, int $planId): void
{
    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $planId,
        'status' => 'active',
        'started_at' => now(),
        'expired_at' => now()->addMonth(),
    ]);
}

test('tenant without feature gets 403', function () {
    seedAnalyticsPlan();
    $owner = analyticsUser();
    $tenant = analyticsTenant($owner);
    subscribeAnalytics($tenant, 1);

    $headers = ['X-Tenant-ID' => $tenant->id];
    $endpoints = [
        '/api/analytics/completion-rate',
        '/api/analytics/tasks-per-user',
        '/api/analytics/overdue-rate',
        '/api/analytics/priority-distribution',
        '/api/analytics/trend',
    ];

    foreach ($endpoints as $url) {
        $this->actingAs($owner)
            ->withHeaders($headers)
            ->getJson($url)
            ->assertForbidden();
    }
});


test('api work success', function () {
    seedAnalyticsPlan();
    $owner = analyticsUser();
    $tenant = analyticsTenant($owner);
    subscribeAnalytics($tenant, 2);

    $headers = ['X-Tenant-ID' => $tenant->id];
    $endpoints = [
        '/api/analytics/completion-rate',
        '/api/analytics/tasks-per-user',
        '/api/analytics/overdue-rate',
        '/api/analytics/priority-distribution',
        '/api/analytics/trend',
    ];

    foreach ($endpoints as $url) {
        $this->actingAs($owner)
            ->withHeaders($headers)
            ->getJson($url)
            ->assertOk();
    }
});


test('completion rate default is 30 days', function () {
    seedAnalyticsPlan();
    $owner = analyticsUser();
    $tenant = analyticsTenant($owner);
    subscribeAnalytics($tenant, 2);

    $recentTask = Task::create([
        'id' => Str::uuid(),
        'tenant_id' => $tenant->id,
        'title' => 'task a',
        'status' => 'completed',
        'priority' => 'medium',
        'created_by' => $owner->id,
        'assignedTo' => $owner->id,
    ]);

    $oldTask = Task::create([
        'id' => Str::uuid(),
        'tenant_id' => $tenant->id,
        'title' => 'task b',
        'status' => 'completed',
        'priority' => 'high',
        'created_by' => $owner->id,
        'assignedTo' => $owner->id,
    ]);

    $oldTask->created_at = now()->subDays(60);
    $oldTask->saveQuietly();


    $headers = ['X-Tenant-ID' => $tenant->id];
    $url = '/api/analytics/completion-rate';

    $response = $this->actingAs($owner)
        ->withHeaders($headers)
        ->getJson($url);

    $response->assertOk();
    $response->dump();

    expect($response->json('data.total'))->toBe(1);
    expect($response->json('data.completed'))->toBe(1);
});
