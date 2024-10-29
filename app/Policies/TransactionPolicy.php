<?php

namespace App\Policies;

use App\Constants\TenancyPermissionConstants;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TenantPermissionManager;
use Filament\Facades\Filament;

class TransactionPolicy
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
        return $user->hasPermissionTo('view transactions') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            Filament::getTenant(),
            $user,
            TenancyPermissionConstants::PERMISSION_VIEW_TRANSACTIONS,
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->hasPermissionTo('view transactions') || $this->tenantPermissionManager->tenantUserHasPermissionTo(
            $transaction->tenant,
            $user,
            TenancyPermissionConstants::PERMISSION_VIEW_TRANSACTIONS,
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
    public function update(User $user, Transaction $transaction): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return true;
    }
}
