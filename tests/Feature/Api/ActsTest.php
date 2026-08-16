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

test('unauthenticated post to api/private/acts returns 401', function () {
    $this->postJson('/api/private/acts', [
        'title' => 'A kind act',
        'description' => 'Something good happened',
        'type' => 'did',
        'user_id' => User::factory()->create()->id,
    ])->assertUnauthorized();
});

test('sanctum authenticated post to api/private/acts creates an act attributed to the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/private/acts', [
            'title' => 'A kind act',
            'description' => 'Something good happened',
            'type' => 'did',
            'user_id' => $user->id,
        ])
        ->assertCreated();

    expect(Act::where(['user_id' => $user->id, 'title' => 'A kind act'])->exists())->toBeTrue();
});

test('public request to api/acts/{act} returns the act without content bug', function () {
    $act = Act::factory()->create();

    $this->getJson("/api/acts/{$act->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $act->id)
        ->assertJsonPath('data.title', $act->title)
        ->assertJsonPath('data.description', $act->description)
        ->assertJsonMissingPath('data.content')
        ->assertJsonMissingPath('data.user');
});

test('sanctum authenticated request to api/private/acts/{act} includes user data and relations', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/private/acts/{$act->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $act->id)
        ->assertJsonPath('data.title', $act->title)
        ->assertJsonPath('data.user.id', $act->user_id);
});

test('unauthenticated request to api/private/acts/{act} is unauthorized', function () {
    $act = Act::factory()->create();

    $this->getJson("/api/private/acts/{$act->id}")
        ->assertUnauthorized();
});

test('sanctum authenticated request to api/private/acts/mine only returns the authenticated users acts', function () {
    $user = User::factory()->create();
    $mine = Act::factory()->count(2)->create(['user_id' => $user->id]);
    Act::factory()->count(3)->create();

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/private/acts/mine')
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('id')->sort()->values()->all())
        ->toBe($mine->pluck('id')->sort()->values()->all());
});

test('unauthenticated request to api/private/acts/mine is unauthorized', function () {
    $this->getJson('/api/private/acts/mine')
        ->assertUnauthorized();
});
