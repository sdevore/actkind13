<?php

namespace App\Actions\Fortify;

use App\Http\Requests\CreateNewUserRequest;
use App\Models\User;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input, ?CreateNewUserRequest $request = null): User
    {
        if ($request === null) {
            $request = app(CreateNewUserRequest::class);
        }

        $request->validateResolved();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }
}
