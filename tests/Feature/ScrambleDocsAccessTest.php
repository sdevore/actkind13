<?php

use App\Models\User;

test('api docs are forbidden without a token outside the local environment', function () {
    $this->get('/docs/api')
        ->assertForbidden();
});

test('api docs are forbidden with an invalid token outside the local environment', function () {
    $this->withHeader('Authorization', 'Bearer not-a-real-token')
        ->get('/docs/api')
        ->assertForbidden();
});

test('api docs are accessible with a valid sanctum token outside the local environment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('docs-access')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->get('/docs/api')
        ->assertOk();
});
