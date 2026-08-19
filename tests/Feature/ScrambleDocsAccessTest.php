<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

test('api docs are accessible to a logged in administrator outside the local environment', function () {
    $adminRole = Role::create(['name' => 'super-admin']);
    Permission::create(['name' => 'view admin panel']);
    $adminRole->givePermissionTo('view admin panel');
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get('/docs/api')
        ->assertOk();
});

test('api docs are forbidden to a logged in non administrator without a token outside the local environment', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/docs/api')
        ->assertForbidden();
});
