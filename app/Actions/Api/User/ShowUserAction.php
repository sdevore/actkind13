<?php

namespace App\Actions\Api\User;

use App\Http\Resources\User as UserResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
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
     */
    public function __invoke(Request $request): JsonResponse|UserResource
    {
        return UserResource::make($request->user());
    }
}
