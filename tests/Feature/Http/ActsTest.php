<?php

use App\Models\Act;
use App\Models\User;

test('authenticated users can visit their own acts page', function () {
    $user = User::factory()->create();
    $ownAct = Act::factory()->create(['user_id' => $user->id]);
    $otherAct = Act::factory()->create();

    $this->actingAs($user)
        ->get('/acts/mine')
        ->assertOk()
        ->assertSee($ownAct->title)
        ->assertDontSee($otherAct->title);
});

test('guests are redirected from the my acts page to login', function () {
    $this->get('/acts/mine')->assertRedirect(route('login'));
});

test('guests can view the acts feed', function () {
    $act = Act::factory()->create();

    $this->get(route('acts.index'))
        ->assertOk()
        ->assertSee($act->title);
});

test('guests see an act without its author or comments', function () {
    $act = Act::factory()->create();
    $act->comments()->create(['body' => 'A comment only members may read', 'user_id' => User::factory()->create()->id]);

    $this->get(route('acts.show', $act))
        ->assertOk()
        ->assertSee($act->title)
        ->assertDontSee($act->user->name)
        ->assertDontSee('A comment only members may read');
});

test('signed-in users see an acts author and comments', function () {
    $act = Act::factory()->create();
    $act->comments()->create(['body' => 'A comment only members may read', 'user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('acts.show', $act))
        ->assertOk()
        ->assertSee($act->user->name)
        ->assertSee('A comment only members may read');
});
