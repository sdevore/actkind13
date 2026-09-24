<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * The Act, Comment and Invitation policies check 'restore {subject}', which was never seeded. Fresh databases
     * have no roles yet and get these permissions from RolesAndPermissionsSeeder instead.
     */
    public function up(): void
    {
        $administratorRoles = Role::query()
            ->whereIn('name', ['administrator', 'super-admin'])
            ->get();

        if ($administratorRoles->isEmpty()) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(['restore acts', 'restore comments', 'restore invitations'])
            ->map(fn (string $name) => Permission::findOrCreate($name));

        $administratorRoles->each(fn (Role $role) => $role->givePermissionTo($permissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
