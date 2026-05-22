<?php

use App\Models\Act;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $act = Act::factory()->create();
    Livewire::test('acts.appreciate', ['act' => $act])
        ->assertStatus(200);
})->group('components');

it('adds an appreciation to the act when the appreciate button action is called', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();

    Livewire::actingAs($user)
        ->test('acts.appreciate', ['act' => $act])
        ->call('appreciate')
        ->assertStatus(200);

    expect($act->fresh()->appreciates)->toHaveCount(1);

    $this->assertDatabaseHas('appreciates', [
        'user_id' => $user->id,
        'appreciable_type' => Act::class,
        'appreciable_id' => $act->id,
    ]);
})->group('components');
