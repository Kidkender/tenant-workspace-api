<?php

use App\Modules\Access\Models\Permission;
use App\Modules\Access\Models\Role;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\PlanFeature;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makeUser(): User
{
    return User::factory()->create(['email_verified_at' => now()]);
}

function makeTenant(User $owner): Tenant
{
    return Tenant::create([
        'id' => Str::uuid(),
        'name' => fake()->company(),
        'slug' => fake()->unique()->slug(),
        'owner_user_id' => $owner->id,
    ]);
}

function assignRole(User $user, Tenant $tenant, Role $role): void
{
    TenantUser::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'role_id' => $role->id,
        'status' => 'active',
        'joined_at' => now(),
    ]);
}

function seedRBAC(): void
{
    $keys = [
        'task.view',
        'task.create',
        'task.update',
        'task.delete',
        'comment.create',
        'comment.delete',
        'attachment.create',
        'attachment.delete',
    ];

    foreach ($keys as $key) {
        Permission::firstOrCreate(['key' => $key]);
    }

    $all = Permission::pluck('id');

    $owner = Role::firstOrCreate(['name' => 'owner', 'tenant_id' => null]);
    $admin = Role::firstOrCreate(['name' => 'admin', 'tenant_id' => null]);
    $member = Role::firstOrCreate(['name' => 'member', 'tenant_id' => null]);

    $owner->permissions()->sync($all);
    $admin->permissions()->sync($all);
    $member->permissions()->sync(
        Permission::whereIn('key', ['task.view', 'task.create', 'task.update', 'comment.create'])->pluck('id')
    );
}

function seedPlans(): void
{
    Plan::insert([
        ['id' => 1, 'name' => 'Free', 'price_monthly' => 0, 'price_yearly' => 0],
        ['id' => 3, 'name' => 'Enterprise', 'price_monthly' => 999000, 'price_yearly' => 9990000],
    ]);

    PlanFeature::insert([
        ['plan_id' => 1, 'feature_key' => 'custom_roles', 'value' => 'false'],
        ['plan_id' => 3, 'feature_key' => 'custom_roles', 'value' => 'true'],
    ]);
}

function subscribeEnterprise(Tenant $tenant): Subscription
{
    return Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => 3,
        'status' => 'active',
        'started_at' => now(),
        'expired_at' => now()->addMonth(),
    ]);
}

function subscribeFree(Tenant $tenant): Subscription
{
    return Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => 1,
        'status' => 'active',
        'started_at' => now(),
        'expired_at' => now()->addMonth(),
    ]);
}

test('owner can list system and custom roles', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    Role::create(['name' => 'reviewer', 'tenant_id' => $tenant->id]);

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->getJson('/api/roles');

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');
    expect($names)->toContain('owner')
        ->toContain('admin')
        ->toContain('member')
        ->toContain('reviewer');
});

test('authenticated user can list all permissions', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->getJson('/api/permissions');

    $response->assertOk();
    expect($response->json('data'))->not->toBeEmpty();
});

test('owner can create custom role on enterprise plan', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $permissionIds = Permission::whereIn('key', ['task.view', 'task.create'])->pluck('id')->toArray();

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->postJson('/api/roles/custom', [
            'name' => 'reviewer',
            'permissions' => $permissionIds,
        ]);

    $response->assertStatus(201);
    expect($response->json('data.name'))->toBe('reviewer');
    expect($response->json('data.permissions'))->toHaveCount(2);

    $this->assertDatabaseHas('roles', [
        'name' => 'reviewer',
        'tenant_id' => $tenant->id,
    ]);
});

test('cannot create custom role on free plan', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeFree($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $permissionIds = Permission::whereIn('key', ['task.view'])->pluck('id')->toArray();

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->postJson('/api/roles/custom', [
            'name' => 'reviewer',
            'permissions' => $permissionIds,
        ]);

    $response->assertStatus(403);
});

test('member cannot assign permissions they do not have (privilege escalation)', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $member = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);

    $ownerRole = Role::where('name', 'owner')->first();
    $memberRole = Role::where('name', 'member')->first();
    assignRole($owner, $tenant, $ownerRole);
    assignRole($member, $tenant, $memberRole);

    $restrictedPermissionIds = Permission::whereIn('key', ['task.delete'])->pluck('id')->toArray();

    Cache::flush();

    $response = $this->actingAs($member)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->postJson('/api/roles/custom', [
            'name' => 'sneaky-role',
            'permissions' => $restrictedPermissionIds,
        ]);

    $response->assertStatus(403);
});

test('create role fails validation without name', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->postJson('/api/roles/custom', [
            'permissions' => [1],
        ]);

    $response->assertUnprocessable();
});

test('create role fails validation with non-existent permission id', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->postJson('/api/roles/custom', [
            'name' => 'reviewer',
            'permissions' => [99999],
        ]);

    $response->assertUnprocessable();
});

test('owner can update custom role name and permissions', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $role = Role::create(['name' => 'reviewer', 'tenant_id' => $tenant->id]);

    $newPermissionIds = Permission::whereIn('key', ['task.view'])->pluck('id')->toArray();

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->putJson("/api/roles/custom/{$role->id}", [
            'name' => 'viewer',
            'permissions' => $newPermissionIds,
        ]);

    $response->assertOk();
    expect($response->json('data.name'))->toBe('viewer');
    expect($response->json('data.permissions'))->toHaveCount(1);
});

test('cannot update system role', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $adminRole = Role::where('name', 'admin')->first();
    $permissionIds = Permission::whereIn('key', ['task.view'])->pluck('id')->toArray();

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->putJson("/api/roles/custom/{$adminRole->id}", [
            'name' => 'hacked-admin',
            'permissions' => $permissionIds,
        ]);

    $response->assertStatus(403);
});

test('cannot update owner role', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $permissionIds = Permission::whereIn('key', ['task.view'])->pluck('id')->toArray();

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->putJson("/api/roles/custom/{$ownerRole->id}", [
            'name' => 'hacked-owner',
            'permissions' => $permissionIds,
        ]);

    $response->assertStatus(403);
});

test('cannot update role belonging to another tenant', function () {
    seedRBAC();
    seedPlans();

    $ownerA = makeUser();
    $ownerB = makeUser();
    $tenantA = makeTenant($ownerA);
    $tenantB = makeTenant($ownerB);
    subscribeEnterprise($tenantA);
    subscribeEnterprise($tenantB);

    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($ownerA, $tenantA, $ownerRole);
    assignRole($ownerB, $tenantB, $ownerRole);

    $roleB = Role::create(['name' => 'reviewer', 'tenant_id' => $tenantB->id]);
    $permissionIds = Permission::whereIn('key', ['task.view'])->pluck('id')->toArray();

    $response = $this->actingAs($ownerA)
        ->withHeaders(['X-Tenant-ID' => $tenantA->id])
        ->putJson("/api/roles/custom/{$roleB->id}", [
            'name' => 'stolen',
            'permissions' => $permissionIds,
        ]);

    $response->assertStatus(404);
});

test('updating role permissions invalidates cache for assigned users', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $user = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);

    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $customRole = Role::create(['name' => 'reviewer', 'tenant_id' => $tenant->id]);
    assignRole($user, $tenant, $customRole);

    $cacheKey = "perm:{$user->id}:{$tenant->id}";
    Cache::put($cacheKey, ['task.view'], 3600);

    $permissionIds = Permission::whereIn('key', ['task.view', 'task.create'])->pluck('id')->toArray();

    $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->putJson("/api/roles/custom/{$customRole->id}", [
            'permissions' => $permissionIds,
        ]);

    expect(Cache::has($cacheKey))->toBeFalse();
});


test('owner can soft delete custom role not in use', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $role = Role::create(['name' => 'reviewer', 'tenant_id' => $tenant->id]);

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->deleteJson("/api/roles/custom/{$role->id}");

    $response->assertOk();
    $this->assertSoftDeleted('roles', ['id' => $role->id]);
});

test('cannot delete role that is assigned to users', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $user = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $customRole = Role::create(['name' => 'reviewer', 'tenant_id' => $tenant->id]);
    assignRole($user, $tenant, $customRole);

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->deleteJson("/api/roles/custom/{$customRole->id}");

    $response->assertStatus(400);
    expect($response->json())->toHaveKey('message');
    $this->assertDatabaseHas('roles', ['id' => $customRole->id, 'deleted_at' => null]);
});

test('cannot delete system role', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $adminRole = Role::where('name', 'admin')->first();

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->deleteJson("/api/roles/custom/{$adminRole->id}");

    $response->assertStatus(403);
});

test('cannot delete owner role', function () {
    seedRBAC();
    seedPlans();

    $owner = makeUser();
    $tenant = makeTenant($owner);
    subscribeEnterprise($tenant);
    $ownerRole = Role::where('name', 'owner')->first();
    assignRole($owner, $tenant, $ownerRole);

    $response = $this->actingAs($owner)
        ->withHeaders(['X-Tenant-ID' => $tenant->id])
        ->deleteJson("/api/roles/custom/{$ownerRole->id}");

    $response->assertStatus(403);
});
