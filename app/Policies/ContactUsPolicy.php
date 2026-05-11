<?php

namespace App\Policies;

use App\Models\ContactUs;
use App\Models\User;

class ContactUsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['administrator', 'super-admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ContactUs $contactUs): bool
    {
        //
        return $user->hasRole(['administrator', 'super-admin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
        return $user->hasRole(['administrator', 'super-admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContactUs $contactUs): bool
    {
        //
        return $user->hasRole(['administrator', 'super-admin']) || $user->id === $contactUs->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContactUs $contactUs): bool
    {
        //
        return $user->hasRole(['administrator', 'super-admin']) || $user->id === $contactUs->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContactUs $contactUs): bool
    {
        //
        return $user->hasRole(['administrator', 'super-admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContactUs $contactUs): bool
    {
        //
        return $user->hasRole(['administrator', 'super-admin']) || $user->id === $contactUs->user_id;
    }
}
