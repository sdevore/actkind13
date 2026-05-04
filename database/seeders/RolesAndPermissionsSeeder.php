<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $subjects = [
            'acts', 'comments', 'flags', 'appreciates', 'invitations'];
        // create permissions
        foreach ($subjects as $subject) {
            Permission::create(['name' => 'update '.$subject]);
            Permission::create(['name' => 'delete '.$subject]);
            Permission::create(['name' => 'view '.$subject]);
            Permission::create(['name' => 'edit '.$subject]);
        }
        Permission::create(['name' => 'impersonate']);
        Permission::create(['name' => 'view admin panel']);
        Permission::create(['name' => 'flag acts']);
        Permission::create(['name' => 'flag comments']);
        Permission::create(['name' => 'invite users']);

        // create roles and assign created permissions

        $moderator = Role::create(['name' => 'moderator']);
        $moderator->givePermissionTo('view admin panel');
        $moderator->givePermissionTo('flag acts');
        $moderator->givePermissionTo('flag comments');
        $moderator->givePermissionTo('invite users');
        foreach ($subjects as $subject) {
            $moderator->givePermissionTo('view '.$subject);
            $moderator->givePermissionTo('delete '.$subject);
        }

        $role = Role::create(['name' => 'administrator']);
        $role->syncPermissions($moderator->permissions);
        $role->givePermissionTo('impersonate');
        foreach ($subjects as $subject) {
            $role->givePermissionTo('edit '.$subject);
        }
        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}
