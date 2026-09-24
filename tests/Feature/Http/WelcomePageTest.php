<?php

use App\Models\Act;
use App\Models\User;

test('guests see recent acts without their authors names', function () {
    $act = Act::factory()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($act->title)
        ->assertDontSee($act->user->name)
        ->assertSee(route('about'), false);
});

test('signed-in users are sent to their dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});
