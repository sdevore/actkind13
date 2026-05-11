<?php

namespace App\Policies;

use App\Models\Act;
use App\Models\User;

class ActPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Act $act): bool
    {
        //
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Act $act): bool
    {
        //
        if ($user->id === $act->user_id || $user->can('update acts')) {
            return true;
        }

        return false;
    }

    public function flag(User $user, Act $act): bool
    {
        //
        if ($user->can('flag acts')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Act $act): bool
    {
        //
        if ($user->id === $act->user_id || $user->can('delete acts')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Act $act): bool
    {
        //
        if ($user->id === $act->user_id || $user->can('restore acts')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Act $act): bool
    {
        //
        if ($user->id === $act->user_id || $user->can('delete acts')) {
            return true;
        }

        return false;
    }
}
