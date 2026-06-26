<?php

use App\Models\Act;
use App\Models\Appreciate;
use App\Models\User;

test('unauthenticated request to api/acts returns at least one act', function () {
    Act::factory()->count(3)->create();

    $this->getJson('/api/acts')
        ->assertOk()
        ->assertJsonPath('data', fn (array $data) => count($data) >= 1);
});

test('unauthenticated request to api/acts does not include user data in any act', function () {
    Act::factory()->count(3)->create();

    $data = $this->getJson('/api/acts')
        ->assertOk()
        ->json('data');

    collect($data)->each(fn (array $act) => expect($act)->not->toHaveKey('user'));
});

test('sanctum authenticated request to api/acts includes user data in every act', function () {
    Act::factory()->count(3)->create();
    $user = User::factory()->create();

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/acts')
        ->assertOk()
        ->json('data');

    collect($data)->each(fn (array $act) => expect($act)->toHaveKey('user'));
});

test('unauthenticated request to api/acts returns correct appreciates_count for acts with appreciates', function () {
    $act = Act::factory()->create();
    Appreciate::factory()->count(2)->create([
        'appreciable_id' => $act->id,
        'appreciable_type' => (new Act)->getMorphClass(),
    ]);

    $data = $this->getJson('/api/acts')
        ->assertOk()
        ->json('data');

    $actData = collect($data)->firstWhere('id', $act->id);

    expect($actData['appreciates_count'])->toBe(2)
        ->and($actData['appreciates'])->toHaveCount(2);
});

test('sanctum authenticated request to api/acts returns correct appreciates_count for acts with appreciates', function () {
    $act = Act::factory()->create();
    Appreciate::factory()->count(2)->create([
        'appreciable_id' => $act->id,
        'appreciable_type' => (new Act)->getMorphClass(),
    ]);
    $user = User::factory()->create();

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/acts')
        ->assertOk()
        ->json('data');

    $actData = collect($data)->firstWhere('id', $act->id);

    expect($actData['appreciates_count'])->toBe(2)
        ->and($actData['appreciates'])->toHaveCount(2);
});

test('unauthenticated post to api/acts returns 401', function () {
    $this->postJson('/api/acts', [
        'title' => 'A kind act',
        'description' => 'Something good happened',
        'type' => 'did',
        'user_id' => User::factory()->create()->id,
    ])->assertUnauthorized();
});

test('sanctum authenticated post to api/acts creates an act attributed to the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/acts', [
            'title' => 'A kind act',
            'description' => 'Something good happened',
            'type' => 'did',
            'user_id' => $user->id,
        ])
        ->assertCreated();

    expect(Act::where(['user_id' => $user->id, 'title' => 'A kind act'])->exists())->toBeTrue();
});
