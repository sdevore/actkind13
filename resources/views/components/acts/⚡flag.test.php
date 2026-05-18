<?php

/** @noinspection ALL */

use App\Models\Act;
use App\Models\Flag;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

it('renders successfully', function () {
    Livewire::test('acts.flag')
        ->assertStatus(200);
});

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
});
