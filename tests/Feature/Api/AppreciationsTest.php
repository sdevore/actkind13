<?php

use App\Models\Act;
use App\Models\Appreciate;
use App\Models\User;

test('sanctum authenticated put to api/private/acts/{act}/appreciations creates an appreciation attributed to the authenticated user', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/private/acts/{$act->id}/appreciations")
        ->assertCreated()
        ->assertJsonPath('data.user.id', $user->id);

    expect(Appreciate::where([
        'appreciable_id' => $act->id,
        'appreciable_type' => (new Act)->getMorphClass(),
        'user_id' => $user->id,
    ])->exists())->toBeTrue();
});

test('sanctum authenticated put to api/private/acts/{act}/appreciations is forbidden for the acts owner', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/private/acts/{$act->id}/appreciations")
        ->assertForbidden();
});

test('sanctum authenticated put to api/private/acts/{act}/appreciations is idempotent and does not create a duplicate', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/private/acts/{$act->id}/appreciations")
        ->assertCreated();

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/private/acts/{$act->id}/appreciations")
        ->assertOk();

    expect(Appreciate::where([
        'appreciable_id' => $act->id,
        'appreciable_type' => (new Act)->getMorphClass(),
        'user_id' => $user->id,
    ])->count())->toBe(1);
});

test('unauthenticated put to api/private/acts/{act}/appreciations returns 401', function () {
    $act = Act::factory()->create();

    $this->putJson("/api/private/acts/{$act->id}/appreciations")
        ->assertUnauthorized();
});

test('sanctum authenticated delete to api/private/appreciations/{appreciation} deletes the owners appreciation', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();
    $appreciation = Appreciate::factory()->create([
        'user_id' => $user->id,
        'appreciable_id' => $act->id,
        'appreciable_type' => $act->getMorphClass(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/private/appreciations/{$appreciation->id}")
        ->assertNoContent();

    expect(Appreciate::find($appreciation->id))->toBeNull();
});

test('sanctum authenticated delete to api/private/appreciations/{appreciation} is forbidden for a non-owner', function () {
    $user = User::factory()->create();
    $appreciation = Appreciate::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/private/appreciations/{$appreciation->id}")
        ->assertForbidden();
});

test('unauthenticated delete to api/private/appreciations/{appreciation} returns 401', function () {
    $appreciation = Appreciate::factory()->create();

    $this->deleteJson("/api/private/appreciations/{$appreciation->id}")
        ->assertUnauthorized();
});
