<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('the roles seeder does not create the unused update permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Permission::where('name', 'like', 'update %')->exists())->toBeFalse();
});

test('the cleanup migration removes existing update permissions and keeps the rest', function () {
    $role = Role::create(['name' => 'super-admin']);
    $role->givePermissionTo(Permission::create(['name' => 'update acts']), Permission::create(['name' => 'edit acts']));

    (require database_path('migrations/2026_09_23_232624_remove_unused_update_permissions.php'))->up();

    expect(Permission::where('name', 'update acts')->exists())->toBeFalse()
        ->and($role->fresh()->hasPermissionTo('edit acts'))->toBeTrue();
});
