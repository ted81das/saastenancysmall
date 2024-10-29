<?php

namespace App\Policies;

use App\Models\RoadmapItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoadmapItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view roadmap items');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RoadmapItem $roadmapItem): bool
    {
        return $user->hasPermissionTo('view roadmap items');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create roadmap items');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RoadmapItem $roadmapItem): bool
    {
        return $user->hasPermissionTo('update roadmap items');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RoadmapItem $roadmapItem): bool
    {
        return $user->hasPermissionTo('delete roadmap items');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RoadmapItem $roadmapItem): bool
    {
        return $user->hasPermissionTo('delete roadmap items');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RoadmapItem $roadmapItem): bool
    {
        return $user->hasPermissionTo('delete roadmap items');
    }
}
