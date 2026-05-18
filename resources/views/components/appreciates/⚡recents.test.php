<?php

/** @noinspection ALL */

use App\Models\Act;
use App\Models\Appreciate;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders successfully', function () {
    Livewire::test('appreciates.recents')
        ->assertStatus(200);
});

it('shows the correct number of appreciations for the current user', function () {
    $owner = User::factory()->create();
    $appreciator = User::factory()->create();
    $act = Act::factory()->create(['user_id' => $owner->id]);

    // Create an appreciation
    Appreciate::create([
        'user_id' => $appreciator->id,
        'appreciable_type' => Act::class,
        'appreciable_id' => $act->id,
    ]);

    actingAs($owner);

    Livewire::test('appreciates.recents')
        ->assertSee($appreciator->name)
        ->assertSee($act->title);
});
