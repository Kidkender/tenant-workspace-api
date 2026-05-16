<?php

namespace App\Modules\Tenant;

use App\Constants\ErrorCode;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\PlanService;
use App\Modules\Billing\Services\SubscriptionService;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Models\TenantInvitation;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\User\Models\User;
use App\Modules\User\UserService;
use App\Notifications\TenantInvite;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantService
{
    public function __construct(
        private PlanService $planService,
        private SubscriptionService $subscriptionService,
        private BillingService $billingService,
        private UserService $userService,
    ) {
    }

    public function createTenant(array $data, $user): Tenant
    {
        if (!$this->billingService->canCreateTenant($user->id)) {
            throw new BadRequestHttpException(ErrorCode::TENANT_LIMIT_EXCEEDED);
        }

        return DB::transaction(function () use ($data, $user) {
            $slug = $this->generateUniqueSlug($data['name']);

            $freePlan = $this->planService->getFreePlan();

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

            $this->subscriptionService->subscribe($tenant->id, $freePlan->id);

            return $tenant;
        });
    }

    public function updateTenant(Tenant $tenant, array $data): Tenant
    {
        $tenant->update(['name' => $data['name']]);

        return $tenant->fresh();
    }

    public function removeMember(Tenant $tenant, string $userId, User $currentUser): void
    {
        if ($tenant->owner_user_id === $userId) {
            throw new AuthorizationException(ErrorCode::PERMISSION_DENIED);
        }

        $currentTenantUser = TenantUser::where('tenant_id', $tenant->id)
            ->where('user_id', $currentUser->id)
            ->with('role')
            ->firstOrFail();

        if (!in_array($currentTenantUser->role?->name, ['owner', 'admin'])) {
            throw new AuthorizationException(ErrorCode::PERMISSION_DENIED);
        }

        TenantUser::where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->delete();
    }

    public function updateMemberRole(Tenant $tenant, string $userId, int $roleId, User $currentUser): void
    {
        if ($tenant->owner_user_id === $userId) {
            throw new AuthorizationException(ErrorCode::PERMISSION_DENIED);
        }

        if ($tenant->owner_user_id !== $currentUser->id) {
            throw new AuthorizationException(ErrorCode::PERMISSION_DENIED);
        }

        TenantUser::where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->update(['role_id' => $roleId]);
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
        $user = $this->userService->getByEmail($email);
        if (!$user) {
            throw new NotFoundHttpException(ErrorCode::USER_NOT_FOUND);
        }

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

        Notification::route('mail', $email)->notify(new TenantInvite($invitation, $tenant->name));

        return $invitation;
    }

    public function accept(string $token, User $user)
    {
        $invitation = TenantInvitation::where('token', $token)->where('status', 'pending')->firstOrFail();

        if ($invitation->expired_at < now()) {
            throw new BadRequestException('Invitation Expired');
        }

        if (!$this->billingService->canAddMember($invitation->tenant_id)) {
            throw new BadRequestException(ErrorCode::MEMBER_LIMIT_EXCEEDED);
        }

        DB::transaction(function () use ($invitation, $user) {
            TenantUser::create([
                'tenant_id' => $invitation->tenant_id,
                'user_id' => $user->id,
                'role_id' => $invitation->role_id,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $invitation->update([
                'status' => 'accepted',
            ]);
        });
    }


}
