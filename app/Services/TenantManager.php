<?php

namespace App\Services;

use App\Constants\InvitationStatus;
use App\Constants\PlanType;
use App\Events\Tenant\UserInvitedToTenant;
use App\Events\Tenant\UserJoinedTenant;
use App\Events\Tenant\UserRemovedFromTenant;
use App\Models\Invitation;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantManager
{
    public function __construct(
        private TenantPermissionManager $tenantPermissionManager,
        private TenantSubscriptionManager $tenantSubscriptionManager,
    ) {

    }

    public function acceptInvitation(Invitation $invitation, User $user): bool
    {
        if ($invitation->status !== InvitationStatus::PENDING->value) {
            return false;
        }

        if ($this->doTenantSubscriptionsAllowAddingUser($invitation->tenant) === false) {
            return false;
        }

        $tenantSubscriptions = $this->tenantSubscriptionManager->getTenantSubscriptions($invitation->tenant);
        $tenantUserCount = $invitation->tenant->users->count();
        $tenantLockKey = $this->getTenantLockName($invitation->tenant);

        $lock = Cache::lock($tenantLockKey, 30);

        try {
            if ($lock->block(30)) {  // use a lock to avoid race conditions
                foreach ($tenantSubscriptions as $subscription) {
                    if ($subscription->plan->type === PlanType::SEAT_BASED->value &&
                        $subscription->quantity < $tenantUserCount + 1
                    ) {
                        $result = $this->tenantSubscriptionManager->updateSubscriptionQuantity($subscription, $tenantUserCount + 1);

                        if ($result === false) {
                            return false;
                        }
                    }
                }

                DB::transaction(function () use ($invitation, $user) {
                    // add the user to the tenant
                    $invitation->tenant->users()->attach($user);

                    $allUserTenantIds = $user->tenants->pluck('id');
                    $user->tenants()->updateExistingPivot($allUserTenantIds, ['is_default' => false]);

                    // set the default tenant for the user to this tenant
                    $user->tenants()->updateExistingPivot($invitation->tenant->id, ['is_default' => true]);

                    $roleName = $invitation->role;

                    if ($roleName) {
                        $this->tenantPermissionManager->assignTenantUserRole($invitation->tenant, $user, $roleName);
                    }

                    $invitation->update([
                        'status' => InvitationStatus::ACCEPTED,
                        'accepted_at' => now(),
                    ]);
                });

                UserJoinedTenant::dispatch($user, $invitation->tenant);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        } finally {
            $lock?->release();
        }

        return true;
    }

    public function addUserToTenant(Tenant $tenant, User $user, ?string $roleName = null): bool
    {
        if ($this->doTenantSubscriptionsAllowAddingUser($tenant) === false) {
            return false;
        }

        $tenantSubscriptions = $this->tenantSubscriptionManager->getTenantSubscriptions($tenant);
        $tenantUserCount = $tenant->users->count();
        $tenantLockKey = $this->getTenantLockName($tenant);

        $lock = Cache::lock($tenantLockKey, 30);

        try {
            if ($lock->block(30)) {  // use a lock to avoid race conditions
                foreach ($tenantSubscriptions as $subscription) {
                    if ($subscription->plan->type === PlanType::SEAT_BASED->value &&
                        $subscription->quantity < $tenantUserCount + 1
                    ) {
                        $result = $this->tenantSubscriptionManager->updateSubscriptionQuantity($subscription, $tenantUserCount + 1);

                        if ($result === false) {
                            return false;
                        }
                    }
                }

                DB::transaction(function () use ($tenant, $user, $roleName) {
                    // add the user to the tenant
                    $tenant->users()->attach($user);

                    $allUserTenantIds = $user->tenants->pluck('id');
                    $user->tenants()->updateExistingPivot($allUserTenantIds, ['is_default' => false]);

                    // set the default tenant for the user to this tenant
                    $user->tenants()->updateExistingPivot($tenant->id, ['is_default' => true]);

                    if ($roleName) {
                        $this->tenantPermissionManager->assignTenantUserRole($tenant, $user, $roleName);
                    }
                });

                UserJoinedTenant::dispatch($user, $tenant);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        } finally {
            $lock?->release();
        }

        return true;

    }

    public function syncSubscriptionQuantity(Subscription $subscription)
    {
        $tenant = $subscription->tenant;
        $tenantUserCount = $tenant->users->count();

        $tenantLockKey = $this->getTenantLockName($tenant);

        $lock = Cache::lock($tenantLockKey, 30);

        try {
            if ($lock->block(30)) {  // use

                if ($subscription->plan->type === PlanType::SEAT_BASED->value &&
                    $subscription->quantity != $tenantUserCount
                ) {
                    return $this->tenantSubscriptionManager->updateSubscriptionQuantity($subscription, $tenantUserCount);
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        } finally {
            $lock?->release();
        }

        return true;
    }

    public function handleAfterInvitationCreated(Invitation $invitation): void
    {
        UserInvitedToTenant::dispatch($invitation);
    }

    public function rejectInvitation(Invitation $invitation, User $user): bool
    {
        if ($invitation->status !== InvitationStatus::PENDING->value) {
            return false;
        }

        $invitation->update([
            'status' => InvitationStatus::REJECTED,
        ]);

        return true;
    }

    public function getUserInvitations(User $user)
    {
        return Invitation::where('email', $user->email)
            ->where('expires_at', '>=', now())
            ->where('status', InvitationStatus::PENDING->value)
            ->with('tenant')
            ->get();
    }

    public function getUserInvitationCount(User $user)
    {
        return Invitation::where('email', $user->email)
            ->where('expires_at', '>=', now())
            ->where('status', InvitationStatus::PENDING->value)
            ->count();
    }

    public function canRemoveUser(Tenant $tenant, User $user): bool
    {
        if (auth()->user() === null || auth()->user()->is($user)) {
            return false;
        }

        if ($tenant->users->count() === 1) {
            return false;
        }

        return $tenant->users->contains($user);
    }

    public function removeUser(Tenant $tenant, User $user): bool
    {
        if (! $this->canRemoveUser($tenant, $user)) {
            return false;
        }

        $tenantLockKey = $this->getTenantLockName($tenant);
        $lock = Cache::lock($tenantLockKey, 30);
        $tenantSubscriptions = $this->tenantSubscriptionManager->getTenantSubscriptions($tenant);
        $tenantUserCount = $tenant->users->count();

        try {

            if ($lock->block(30)) {  // use a lock to avoid race conditions

                foreach ($tenantSubscriptions as $subscription) {
                    if ($subscription->plan->type === PlanType::SEAT_BASED->value &&
                        $subscription->quantity != $tenantUserCount - 1
                    ) {
                        $result = $this->tenantSubscriptionManager->updateSubscriptionQuantity($subscription, $tenantUserCount - 1);

                        if ($result === false) {
                            return false;
                        }
                    }
                }

                DB::transaction(function () use ($tenant, $user) {
                    $this->tenantPermissionManager->removeAllTenantUserRoles($tenant, $user);
                    $tenant->users()->detach($user);
                });

                UserRemovedFromTenant::dispatch($user, $tenant);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        } finally {
            $lock?->release();
        }

        return true;
    }

    public function canInviteUser(Tenant $tenant, User $user): bool
    {
        return config('app.allow_tenant_invitations', false) && $this->doTenantSubscriptionsAllowAddingUser($tenant);
    }

    public function getTenantByUuid(string $uuid): Tenant
    {
        return Tenant::where('uuid', $uuid)->firstOrFail();
    }

    public function updateTenantName(Tenant $tenant, string $name): bool
    {
        return $tenant->update([
            'name' => $name,
        ]);
    }

    private function doTenantSubscriptionsAllowAddingUser(Tenant $tenant): bool
    {
        $tenantSubscriptions = $tenant->subscriptions()->with('plan')->get();
        $tenantUserCount = $tenant->users->count();

        foreach ($tenantSubscriptions as $subscription) {
            if ($subscription->plan->type === PlanType::SEAT_BASED->value &&
                $subscription->plan->max_users_per_tenant !== 0 &&
                $tenantUserCount >= $subscription->plan->max_users_per_tenant) {
                return false;
            }
        }

        return true;
    }

    private function getTenantLockName(Tenant $tenant): string
    {
        return 'tenant_'.$tenant->id;
    }
}
