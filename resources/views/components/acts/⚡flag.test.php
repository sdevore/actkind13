<?php

/** @noinspection ALL */

use App\Models\Act;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

it('renders successfully', function () {
    $act = Act::factory()->create();
    Livewire::test('acts.flag', ['act' => $act])
        ->assertStatus(200);
})->group('components');

it('shows the correct number of flags on an act with 1 flag added', function () {
    Notification::fake();

    $user = User::factory()->create();
    Permission::findOrCreate('flag acts');
    $user->givePermissionTo('flag acts');

    $act = Act::factory()->create();

    // Directly create a flag or use the flag method
    $act->flag($user, 'Inappropriate content');

    Livewire::test('acts.flag', ['act' => $act])
        ->assertSee('1');
})->group('components');

it('does not let members flag a act', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $act = Act::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('acts.flag', ['act' => $act])
        ->set('reason', 'This is not kind at all')
        ->call('save')
        ->assertForbidden();

    expect($act->flags()->count())->toBe(0);
});

it('lets moderators flag a act with a reason', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();
    $act = Act::factory()->create();

    Livewire::actingAs(User::factory()->create()->assignRole('moderator'))
        ->test('acts.flag', ['act' => $act])
        ->set('reason', 'This is not kind at all')
        ->call('save')
        ->assertHasNoErrors();

    expect($act->flags()->sole()->reason)->toBe('This is not kind at all');
});
