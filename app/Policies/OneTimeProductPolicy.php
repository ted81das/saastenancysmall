<?php

namespace App\Policies;

use App\Models\OneTimeProduct;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OneTimeProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view one time products');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OneTimeProduct $oneTimeProduct): bool
    {
        return $user->hasPermissionTo('view one time products');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create one time products');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OneTimeProduct $oneTimeProduct): bool
    {
        return $user->hasPermissionTo('update one time products');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OneTimeProduct $oneTimeProduct): bool
    {
        return $user->hasPermissionTo('delete one time products');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OneTimeProduct $oneTimeProduct): bool
    {
        return $user->hasPermissionTo('delete one time products');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OneTimeProduct $oneTimeProduct): bool
    {
        return $user->hasPermissionTo('delete one time products');
    }
}
