<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('User')]
class UserController extends Controller
{
    /**
     * show
     *
     * Show the authenticated API user.
     */
    public function show(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }
}
