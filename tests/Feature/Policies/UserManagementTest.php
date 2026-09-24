<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('administrators can manage users in the admin panel', function (string $role) {
    $this->actingAs(User::factory()->create()->assignRole($role));

    $this->get(ListUsers::getUrl())->assertOk();
    $this->get(EditUser::getUrl(['record' => User::factory()->create()]))->assertOk();
})->with(['administrator', 'super-admin']);

test('moderators cannot list or edit users in the admin panel', function () {
    $this->actingAs(User::factory()->create()->assignRole('moderator'));

    $this->get(ListUsers::getUrl())->assertForbidden();
    $this->get(EditUser::getUrl(['record' => User::factory()->create()]))->assertForbidden();
});
