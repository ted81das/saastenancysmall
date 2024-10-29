<?php

namespace App\Policies;

use App\Constants\TenancyPermissionConstants;
use App\Models\Subscription;
use App\Models\User;
use App\Services\TenantPermissionManager;
use Filament\Facades\Filament;

class SubscriptionPolicy
{
    public function __construct(
        private TenantPermissionManager $tenantPermissionManager
    ) {

    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view subscriptions') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            Filament::getTenant(),
            $user,
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->hasPermissionTo('view subscriptions') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            $subscription->tenant,
            $user,
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $user->hasPermissionTo('update subscriptions') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            $subscription->tenant,
            $user,
            TenancyPermissionConstants::PERMISSION_UPDATE_SUBSCRIPTIONS,
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->hasPermissionTo('delete subscriptions') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            $subscription->tenant,
            $user,
            TenancyPermissionConstants::PERMISSION_DELETE_SUBSCRIPTIONS,
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Subscription $subscription): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return false;
    }
}
