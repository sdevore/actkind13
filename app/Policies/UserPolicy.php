<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->managesUsers($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->managesUsers($user);
    }

    public function create(User $user): bool
    {
        return $this->managesUsers($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->managesUsers($user);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->managesUsers($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->managesUsers($user);
    }

    private function managesUsers(User $user): bool
    {
        return $user->hasRole(['administrator', 'super-admin']);
    }
}
