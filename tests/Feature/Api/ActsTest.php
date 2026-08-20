<?php

use App\Models\Act;
use App\Models\Appreciate;
use App\Models\Comment;
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

test('unauthenticated request to api/acts returns correct appreciates_count for acts with appreciates but omits the appreciates array', function () {
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
        ->and($actData)->not->toHaveKey('appreciates');
});

test('unauthenticated request to api/acts does not include appreciates or comments arrays, only counts', function () {
    Act::factory()->count(2)->create();

    $data = $this->getJson('/api/acts')
        ->assertOk()
        ->json('data');

    collect($data)->each(function (array $act) {
        expect($act)->not->toHaveKey('appreciates')
            ->and($act)->not->toHaveKey('comments')
            ->and($act)->toHaveKey('appreciates_count')
            ->and($act)->toHaveKey('comments_count');
    });
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

test('unauthenticated request to api/acts honours per_page', function () {
    Act::factory()->count(15)->create();

    $data = $this->getJson('/api/acts?per_page=5')
        ->assertOk()
        ->json('data');

    expect(count($data))->toBeLessThanOrEqual(5);
});

test('unauthenticated request to api/acts returns the correct second page of results', function () {
    $acts = collect(range(0, 14))
        ->map(fn (int $i) => Act::factory()->create(['created_at' => now()->subMinutes($i)]));

    $expectedIds = $acts->sortByDesc('created_at')->pluck('id')->values();

    $page1 = $this->getJson('/api/acts?per_page=5&page=1')->assertOk()->json('data');
    $page2 = $this->getJson('/api/acts?per_page=5&page=2')->assertOk()->json('data');

    expect(collect($page1)->pluck('id')->all())->toBe($expectedIds->slice(0, 5)->values()->all())
        ->and(collect($page2)->pluck('id')->all())->toBe($expectedIds->slice(5, 5)->values()->all());
});

test('unauthenticated put to api/private/acts returns 401', function () {
    $this->putJson('/api/private/acts', [
        'title' => 'A kind act',
        'description' => 'Something good happened',
        'type' => 'did',
    ])->assertUnauthorized();
});

test('sanctum authenticated put to api/private/acts creates an act attributed to the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/private/acts', [
            'title' => 'A kind act',
            'description' => 'Something good happened',
            'type' => 'did',
        ])
        ->assertCreated();

    expect(Act::where(['user_id' => $user->id, 'title' => 'A kind act'])->exists())->toBeTrue();
});

test('sanctum authenticated put to api/private/acts ignores a spoofed user_id and attributes the act to the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/private/acts', [
            'title' => 'A kind act',
            'description' => 'Something good happened',
            'type' => 'did',
            'user_id' => $otherUser->id,
        ])
        ->assertCreated();

    expect(Act::where(['user_id' => $user->id, 'title' => 'A kind act'])->exists())->toBeTrue()
        ->and(Act::where(['user_id' => $otherUser->id, 'title' => 'A kind act'])->exists())->toBeFalse();
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

test('sanctum authenticated request to api/private/acts/{act} returns correct appreciates_count and comments_count', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();
    Appreciate::factory()->count(3)->create([
        'appreciable_id' => $act->id,
        'appreciable_type' => (new Act)->getMorphClass(),
    ]);
    Comment::factory()->count(2)->create(['act_id' => $act->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/private/acts/{$act->id}")
        ->assertOk()
        ->assertJsonPath('data.appreciates_count', 3)
        ->assertJsonPath('data.comments_count', 2);
});

test('sanctum authenticated request to api/private/acts/{act} includes comment author details and omits deleted_at', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();
    $commenter = User::factory()->create();
    Comment::factory()->create(['act_id' => $act->id, 'user_id' => $commenter->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/private/acts/{$act->id}")
        ->assertOk()
        ->assertJsonPath('data.comments.0.user.id', $commenter->id)
        ->assertJsonPath('data.comments.0.user.name', $commenter->name)
        ->assertJsonMissingPath('data.comments.0.deleted_at');
});

test('sanctum authenticated request to api/private/acts/{act} includes appreciate author details', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();
    $appreciator = User::factory()->create();
    Appreciate::factory()->create([
        'appreciable_id' => $act->id,
        'appreciable_type' => (new Act)->getMorphClass(),
        'user_id' => $appreciator->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/private/acts/{$act->id}")
        ->assertOk()
        ->assertJsonPath('data.appreciates.0.user.id', $appreciator->id);
});

test('sanctum authenticated request to api/private/acts orders newest first', function () {
    $user = User::factory()->create();
    $older = Act::factory()->create(['created_at' => now()->subDays(3)]);
    $newer = Act::factory()->create(['created_at' => now()]);

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/private/acts')
        ->assertOk()
        ->json('data');

    expect($data[0]['id'])->toBe($newer->id)
        ->and($data[1]['id'])->toBe($older->id);
});

test('sanctum authenticated request to api/private/acts honours per_page', function () {
    $user = User::factory()->create();
    Act::factory()->count(15)->create();

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/private/acts?per_page=5')
        ->assertOk()
        ->json('data');

    expect(count($data))->toBeLessThanOrEqual(5);
});

test('sanctum authenticated request to api/private/acts returns the correct second page of results', function () {
    $user = User::factory()->create();
    $acts = collect(range(0, 14))
        ->map(fn (int $i) => Act::factory()->create(['created_at' => now()->subMinutes($i)]));

    $expectedIds = $acts->sortByDesc('created_at')->pluck('id')->values();

    $page1 = $this->actingAs($user, 'sanctum')->getJson('/api/private/acts?per_page=5&page=1')->assertOk()->json('data');
    $page2 = $this->actingAs($user, 'sanctum')->getJson('/api/private/acts?per_page=5&page=2')->assertOk()->json('data');

    expect(collect($page1)->pluck('id')->all())->toBe($expectedIds->slice(0, 5)->values()->all())
        ->and(collect($page2)->pluck('id')->all())->toBe($expectedIds->slice(5, 5)->values()->all());
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

test('sanctum authenticated request to api/private/acts/mine honours per_page', function () {
    $user = User::factory()->create();
    Act::factory()->count(15)->create(['user_id' => $user->id]);

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/private/acts/mine?per_page=5')
        ->assertOk()
        ->json('data');

    expect(count($data))->toBeLessThanOrEqual(5);
});

test('sanctum authenticated request to api/private/acts/mine returns the correct second page of results', function () {
    $user = User::factory()->create();
    $mine = collect(range(0, 14))
        ->map(fn (int $i) => Act::factory()->create(['user_id' => $user->id, 'created_at' => now()->subMinutes($i)]));
    Act::factory()->count(3)->create();

    $expectedIds = $mine->sortByDesc('created_at')->pluck('id')->values();

    $page1 = $this->actingAs($user, 'sanctum')->getJson('/api/private/acts/mine?per_page=5&page=1')->assertOk()->json('data');
    $page2 = $this->actingAs($user, 'sanctum')->getJson('/api/private/acts/mine?per_page=5&page=2')->assertOk()->json('data');

    expect(collect($page1)->pluck('id')->all())->toBe($expectedIds->slice(0, 5)->values()->all())
        ->and(collect($page2)->pluck('id')->all())->toBe($expectedIds->slice(5, 5)->values()->all());
});

test('unauthenticated request to api/private/acts/mine is unauthorized', function () {
    $this->getJson('/api/private/acts/mine')
        ->assertUnauthorized();
});

test('sanctum authenticated post to api/private/acts/{act} updates the owners act', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/private/acts/{$act->id}", [
            'title' => 'Updated title',
            'description' => $act->description,
            'type' => $act->type->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated title');

    expect($act->fresh()->title)->toBe('Updated title');
});

test('sanctum authenticated post to api/private/acts/{act} is forbidden for a non-owner', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/private/acts/{$act->id}", [
            'title' => 'Updated title',
            'description' => $act->description,
            'type' => $act->type->value,
        ])
        ->assertForbidden();
});

test('unauthenticated post to api/private/acts/{act} returns 401', function () {
    $act = Act::factory()->create();

    $this->postJson("/api/private/acts/{$act->id}", [
        'title' => 'Updated title',
        'description' => $act->description,
        'type' => $act->type->value,
    ])->assertUnauthorized();
});

test('sanctum authenticated delete to api/private/acts/{act} deletes the owners act', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/private/acts/{$act->id}")
        ->assertNoContent();

    expect($act->fresh()->trashed())->toBeTrue();
});

test('sanctum authenticated delete to api/private/acts/{act} is forbidden for a non-owner', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/private/acts/{$act->id}")
        ->assertForbidden();
});

test('unauthenticated delete to api/private/acts/{act} returns 401', function () {
    $act = Act::factory()->create();

    $this->deleteJson("/api/private/acts/{$act->id}")
        ->assertUnauthorized();
});
