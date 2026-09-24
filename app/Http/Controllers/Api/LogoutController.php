<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Auth')]
class LogoutController extends Controller
{
    /**
     * logout
     *
     * Revoke the current Sanctum access token.
     *
     * @response array{message: string}
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->user('sanctum')->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
