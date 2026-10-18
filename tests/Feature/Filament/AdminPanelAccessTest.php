<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('an administrator can access the admin panel', function () {
    Permission::create(['name' => 'view admin panel']);
    $role = Role::create(['name' => 'administrator']);
    $role->givePermissionTo('view admin panel');

    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});

test('an authenticated user without the view admin panel permission is forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('a guest is redirected to the admin login page', function () {
    $this->get('/admin')
        ->assertRedirect('/admin/login');
});

test('the telescope link is only shown in the local environment', function (string $environment, bool $isShown) {
    Permission::create(['name' => 'view admin panel']);
    $admin = User::factory()->create();
    $admin->givePermissionTo('view admin panel');
    app()->detectEnvironment(fn (): string => $environment);

    $response = $this->actingAs($admin)->get('/admin');

    $isShown
        ? $response->assertSee('/telescope', escape: false)
        : $response->assertDontSee('/telescope', escape: false);
})->with([
    'production' => ['production', false],
    'staging' => ['staging', false],
    'local' => ['local', true],
]);
