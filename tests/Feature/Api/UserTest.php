<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('unauthenticated request to api/user returns 401', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
});

test('authenticated request to api/user returns the user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonFragment(['email' => $user->email]);
});

test('sanctum token endpoint issues a token for valid credentials', function () {
    $user = User::factory()->create();

    $this->postJson(route('sanctum.token'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-device',
    ])
        ->assertOk()
        ->assertJsonStructure(['token']);
});

test('sanctum token endpoint requires email', function () {
    $this->postJson(route('sanctum.token'), [
        'password' => 'password',
        'device_name' => 'test-device',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('sanctum token endpoint requires a valid email format', function () {
    $this->postJson(route('sanctum.token'), [
        'email' => 'not-an-email',
        'password' => 'password',
        'device_name' => 'test-device',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('sanctum token endpoint requires password', function () {
    $user = User::factory()->create();

    $this->postJson(route('sanctum.token'), [
        'email' => $user->email,
        'device_name' => 'test-device',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('sanctum token endpoint requires device name', function () {
    $user = User::factory()->create();

    $this->postJson(route('sanctum.token'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['device_name']);
});

test('sanctum token endpoint rejects incorrect password', function () {
    $user = User::factory()->create();

    $this->postJson(route('sanctum.token'), [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'test-device',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('sanctum token can be used to authenticate api/user', function () {
    $user = User::factory()->create();

    $token = $this->postJson(route('sanctum.token'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-device',
    ])->json('token');

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonFragment(['email' => $user->email]);
});

test('unauthenticated request to logout returns 401', function () {
    $this->postJson(route('user.logout'))
        ->assertUnauthorized();
});

test('authenticated logout deletes the token and returns success message', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('user.logout'))
        ->assertOk()
        ->assertJson(['message' => 'Logged out successfully.']);

    expect($user->tokens()->count())->toBe(0);
});

test('revoked token can no longer authenticate after logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $this->withToken($token)->postJson(route('user.logout'))->assertOk();

    Auth::forgetGuards();

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertUnauthorized();
});
