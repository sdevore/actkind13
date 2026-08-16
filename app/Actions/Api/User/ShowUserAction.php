<?php

namespace App\Actions\Api\User;

use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('User')]
class ShowUserAction
{
    /**
     * show
     *
     * Show the authenticated API user.
     *
     * @tags User
     *
     * @response User
     */
    public function __invoke(Request $request): User
    {
        return $request->user();
    }
}
