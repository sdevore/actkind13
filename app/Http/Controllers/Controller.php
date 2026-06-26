<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * Cache the user instance to avoid multiple database lookups.
     * false = not checked yet, null = checked and user is a guest.
     */
    private User|null|false $resolvedUser = false;

    /**
     * Retrieve the authenticated user via Sanctum or Session guard.
     */
    protected function getAuthUser(): ?User
    {
        if ($this->resolvedUser !== false) {
            return $this->resolvedUser;
        }

        // Prioritize Sanctum token/cookie, fallback to standard web session
        return $this->resolvedUser = Auth::guard('sanctum')->user() ?? Auth::user();
    }

    /**
     * Quick helper to check if the current visitor is authenticated.
     */
    protected function isAuth(): bool
    {
        return $this->getAuthUser() !== null;
    }
}
