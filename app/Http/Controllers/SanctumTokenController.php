<?php

namespace App\Http\Controllers;

use App\Http\Requests\SanctumTokenRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SanctumTokenController extends Controller
{
    /**
     * @return array{token: string}
     *
     * @throws ValidationException
     */
    public function __invoke(SanctumTokenRequest $request): array
    {
        $user = User::query()
            ->where('email', $request->email())
            ->first();

        if (! $user || ! Hash::check($request->password(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return [
            'token' => $user->createToken($request->deviceName())->plainTextToken,
        ];
    }
}
