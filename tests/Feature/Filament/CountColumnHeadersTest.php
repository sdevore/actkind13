<?php

use App\Filament\Resources\Acts\Pages\ListActs;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->actingAs(User::factory()->create()->assignRole('administrator'));
});

dataset('icon count columns', [
    'acts appreciates' => [ListActs::class, 'Appreciates'],
    'acts comments' => [ListActs::class, 'Comments'],
    'acts flags' => [ListActs::class, 'Flags'],
    'users comments' => [ListUsers::class, 'Comments'],
    'users flags' => [ListUsers::class, 'Flags'],
]);

test('icon-only count column headers name their icon for screen readers', function (string $page, string $label) {
    Livewire::test($page)
        ->assertSeeHtml("role=\"img\" aria-label=\"{$label}\"");
})->with('icon count columns');
