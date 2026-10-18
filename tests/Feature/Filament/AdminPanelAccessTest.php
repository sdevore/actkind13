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

test('the admin panel has no telescope link in any environment', function (string $environment) {
    Permission::create(['name' => 'view admin panel']);
    $admin = User::factory()->create();
    $admin->givePermissionTo('view admin panel');
    app()->detectEnvironment(fn (): string => $environment);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertDontSee('/telescope', escape: false);
})->with(['production', 'staging', 'local']);
