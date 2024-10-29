<?php

namespace App\Policies;

use App\Models\PaymentProvider;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentProviderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('update settings');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PaymentProvider $paymentProvider): bool
    {
        return $user->hasPermissionTo('update settings');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('update settings');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PaymentProvider $paymentProvider): bool
    {
        return $user->hasPermissionTo('update settings');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PaymentProvider $paymentProvider): bool
    {
        return $user->hasPermissionTo('update settings');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PaymentProvider $paymentProvider): bool
    {
        return $user->hasPermissionTo('update settings');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PaymentProvider $paymentProvider): bool
    {
        return $user->hasPermissionTo('update settings');
    }
}
