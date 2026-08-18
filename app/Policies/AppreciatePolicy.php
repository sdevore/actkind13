<?php

namespace App\Policies;

use App\Models\Act;
use App\Models\Appreciate;
use App\Models\User;

class AppreciatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appreciate $appreciate): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create a model for the given act.
     */
    public function create(User $user, Act $act): bool
    {
        return $user->id !== $act->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appreciate $appreciate): bool
    {
        if ($user->id === $appreciate->user_id || $user->can('delete appreciates')) {
            return true;
        }

        return false;
    }
}
