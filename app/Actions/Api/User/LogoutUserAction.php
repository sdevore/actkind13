<?php

namespace App\Actions\Api\User;

use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Auth')]
class LogoutUserAction
{
    /**
     * logout
     *
     * Revoke the current Sanctum access token.
     *
     * @tags User
     *
     * @response array{message: string}
     */
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->user('sanctum')->currentAccessToken();
        $token->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
