<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Policies authorize edits with 'edit {subject}', so the 'update {subject}' permissions are never checked.
     */
    public function up(): void
    {
        Permission::query()
            ->whereIn('name', [
                'update acts',
                'update comments',
                'update flags',
                'update appreciates',
                'update invitations',
            ])
            ->get()
            ->each(fn (Permission $permission) => $permission->delete());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
