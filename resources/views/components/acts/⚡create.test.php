<?php

/** @noinspection ALL */

use App\Enums\ActType;
use App\Models\Act;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

it('renders successfully', function () {
    Livewire::test('acts.create')
        ->assertStatus(200);
});

it('can create an act', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test('acts.create')
        ->fillForm([
            'title' => 'Test Act',
            'type' => ActType::DID->value,
            'description' => 'This is a test act description',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Act::class, [
        'title' => 'Test Act',
        'type' => ActType::DID->value,
        'description' => 'This is a test act description',
        'user_id' => $user->id,
    ]);
});
