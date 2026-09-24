<?php

use App\Models\Invitation;
use App\Models\User;

test('users see their invitations page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('invitations.index'))
        ->assertOk();
});

test('users can view their own invitation', function () {
    $invitation = Invitation::factory()->create();

    $this->actingAs($invitation->user)
        ->get(route('invitations.show', $invitation))
        ->assertOk();
});

test('users cannot view another users invitation', function () {
    $invitation = Invitation::factory()->create(['joined_id' => null]);

    $this->actingAs(User::factory()->create())
        ->get(route('invitations.show', $invitation))
        ->assertForbidden();
});
