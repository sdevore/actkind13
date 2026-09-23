<?php

use App\Filament\Resources\Flags\Pages\CreateFlag;
use App\Filament\Resources\Flags\Pages\EditFlag;
use App\Filament\Resources\Flags\Pages\ListFlags;
use App\Models\Flag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('moderators can review flags but not edit them', function () {
    $this->actingAs(User::factory()->create()->assignRole('moderator'));
    $flag = Flag::factory()->create();

    $this->get(ListFlags::getUrl())->assertOk();
    $this->get(EditFlag::getUrl(['record' => $flag]))->assertForbidden();
});

test('administrators can edit flags', function () {
    $this->actingAs(User::factory()->create()->assignRole('administrator'));

    $this->get(EditFlag::getUrl(['record' => Flag::factory()->create()]))->assertOk();
});

test('flags cannot be created in the admin panel because they come from the site', function (string $role) {
    $this->actingAs(User::factory()->create()->assignRole($role));

    $this->get(CreateFlag::getUrl())->assertForbidden();
})->with(['moderator', 'administrator', 'super-admin']);
