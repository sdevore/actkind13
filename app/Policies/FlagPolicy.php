<?php

namespace App\Policies;

use App\Models\Flag;
use App\Models\User;

class FlagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view flags');
    }

    public function view(User $user, Flag $flag): bool
    {
        return $user->can('view flags');
    }

    /**
     * Flags are raised from the site through Act::flag() and Comment::flag(), never created in the admin panel.
     */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Flag $flag): bool
    {
        return $user->can('edit flags');
    }

    public function delete(User $user, Flag $flag): bool
    {
        return $user->can('delete flags');
    }
}
