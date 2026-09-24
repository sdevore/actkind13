<?php

namespace App\Actions\Fortify;

use App\Http\Requests\CreateNewUserRequest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user, consuming their invitation.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input, ?CreateNewUserRequest $request = null): User
    {
        if ($request === null) {
            $request = app(CreateNewUserRequest::class);
        }

        $request->validateResolved();

        return DB::transaction(function () use ($input): User {
            $invitation = Invitation::query()
                ->where('code', $input['code'])
                ->whereNull('joined_id')
                ->lockForUpdate()
                ->first();

            if (! $invitation) {
                throw ValidationException::withMessages([
                    'code' => __('This invitation code is invalid or has already been used.'),
                ]);
            }

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $invitation->update([
                'joined_id' => $user->id,
                'joined_at' => now(),
            ]);

            return $user;
        });
    }
}
