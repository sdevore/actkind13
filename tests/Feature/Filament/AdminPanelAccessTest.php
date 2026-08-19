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
