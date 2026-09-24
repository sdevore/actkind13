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

test('the roles seeder lets administrators restore deleted content but not moderators', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    foreach (['acts', 'comments', 'invitations'] as $subject) {
        expect(Role::findByName('administrator')->hasPermissionTo("restore {$subject}"))->toBeTrue()
            ->and(Role::findByName('super-admin')->hasPermissionTo("restore {$subject}"))->toBeTrue()
            ->and(Role::findByName('moderator')->hasPermissionTo("restore {$subject}"))->toBeFalse();
    }
});

test('the restore migration grants restore permissions to existing administrator roles', function () {
    $administrator = Role::create(['name' => 'administrator']);
    $moderator = Role::create(['name' => 'moderator']);

    (require database_path('migrations/2026_09_23_232625_add_restore_permissions_for_administrators.php'))->up();

    expect($administrator->fresh()->hasPermissionTo('restore acts'))->toBeTrue()
        ->and($moderator->fresh()->hasPermissionTo('restore acts'))->toBeFalse();
});

test('the restore migration leaves fresh databases to the roles seeder', function () {
    (require database_path('migrations/2026_09_23_232625_add_restore_permissions_for_administrators.php'))->up();

    expect(Permission::where('name', 'like', 'restore %')->exists())->toBeFalse();

    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::findByName('administrator')->hasPermissionTo('restore acts'))->toBeTrue();
});
