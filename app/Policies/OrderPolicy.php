<?php

namespace App\Policies;

use App\Constants\TenancyPermissionConstants;
use App\Models\Order;
use App\Models\User;
use App\Services\TenantPermissionManager;
use Filament\Facades\Filament;

class OrderPolicy
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
        return $user->hasPermissionTo('view orders') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            Filament::getTenant(),
            $user,
            TenancyPermissionConstants::PERMISSION_VIEW_ORDERS,
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('view orders') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            $order->tenant,
            $user,
            TenancyPermissionConstants::PERMISSION_VIEW_ORDERS,
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
    public function update(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('update orders') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            $order->tenant,
            $user,
            TenancyPermissionConstants::PERMISSION_UPDATE_ORDERS,
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('delete orders') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            $order->tenant,
            $user,
            TenancyPermissionConstants::PERMISSION_DELETE_ORDERS,
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}
