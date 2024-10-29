<?php

namespace App\Policies;

use App\Models\BlogPostCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BlogPostCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view blog post categories');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->hasPermissionTo('view blog post categories');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create blog post categories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->hasPermissionTo('update blog post categories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->hasPermissionTo('delete blog post categories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->hasPermissionTo('delete blog post categories');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->hasPermissionTo('delete blog post categories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete blog post categories');
    }
}
