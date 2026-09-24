<?php

use App\Models\Act;
use App\Models\Invitation;
use App\Models\User;

const INJECTED = 'Hello **kind** <img src=x onerror="alert(1)"> [link](javascript:alert(2))';

test('act descriptions render markdown but escape raw HTML and drop javascript links', function () {
    $act = Act::factory()->create(['description' => INJECTED]);

    $this->get(route('acts.show', $act))
        ->assertOk()
        ->assertSee('<strong>kind</strong>', false)
        ->assertDontSee('<img src=x onerror', false)
        ->assertDontSee('href="javascript:', false);
});

test('comments render markdown but escape raw HTML and drop javascript links', function () {
    $act = Act::factory()->create();
    $act->comments()->create(['body' => INJECTED, 'user_id' => $act->user_id]);

    $this->actingAs(User::factory()->create())
        ->get(route('acts.show', $act))
        ->assertOk()
        ->assertSee('<strong>kind</strong>', false)
        ->assertDontSee('<img src=x onerror', false)
        ->assertDontSee('href="javascript:', false);
});

test('invitation messages render markdown but escape raw HTML and drop javascript links', function () {
    $invitation = Invitation::factory()->create(['message' => INJECTED]);

    $this->actingAs($invitation->user)
        ->get(route('invitations.show', $invitation))
        ->assertOk()
        ->assertSee('<strong>kind</strong>', false)
        ->assertDontSee('<img src=x onerror', false)
        ->assertDontSee('href="javascript:', false);
});
