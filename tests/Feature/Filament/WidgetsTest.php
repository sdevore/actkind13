<?php

use App\Filament\Widgets\ActsWidget;
use App\Filament\Widgets\CommentsWidget;
use App\Filament\Widgets\FlagsWidget;
use App\Models\Act;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');
    $this->actingAs($moderator);
});

test('dashboard chart widgets render for every period filter', function (string $widget, string $filter) {
    Act::factory()->count(2)->create();

    Livewire::test($widget)
        ->set('filter', $filter)
        ->assertOk();
})->with([
    ActsWidget::class,
    CommentsWidget::class,
    FlagsWidget::class,
])->with(['week', 'month', '3months']);
