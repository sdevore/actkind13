<?php

use App\Models\Act;
use App\Models\Comment;
use App\Models\User;

test('sanctum authenticated put to api/private/acts/{act}/comments creates a comment attributed to the authenticated user', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/private/acts/{$act->id}/comments", [
            'body' => 'What a kind thing to do',
        ])
        ->assertCreated()
        ->assertJsonPath('data.body', 'What a kind thing to do')
        ->assertJsonPath('data.user.id', $user->id);

    expect(Comment::where(['act_id' => $act->id, 'user_id' => $user->id, 'body' => 'What a kind thing to do'])->exists())->toBeTrue();
});

test('sanctum authenticated put to api/private/acts/{act}/comments requires a body', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/private/acts/{$act->id}/comments", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('body');
});

test('unauthenticated put to api/private/acts/{act}/comments returns 401', function () {
    $act = Act::factory()->create();

    $this->putJson("/api/private/acts/{$act->id}/comments", [
        'body' => 'What a kind thing to do',
    ])->assertUnauthorized();
});

test('sanctum authenticated post to api/private/comments/{comment} updates the owners comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/private/comments/{$comment->id}", [
            'body' => 'Updated comment body',
        ])
        ->assertOk()
        ->assertJsonPath('data.body', 'Updated comment body');

    expect($comment->fresh()->body)->toBe('Updated comment body');
});

test('sanctum authenticated post to api/private/comments/{comment} is forbidden for a non-owner', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/private/comments/{$comment->id}", [
            'body' => 'Updated comment body',
        ])
        ->assertForbidden();
});

test('unauthenticated post to api/private/comments/{comment} returns 401', function () {
    $comment = Comment::factory()->create();

    $this->postJson("/api/private/comments/{$comment->id}", [
        'body' => 'Updated comment body',
    ])->assertUnauthorized();
});

test('sanctum authenticated delete to api/private/comments/{comment} deletes the owners comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/private/comments/{$comment->id}")
        ->assertNoContent();

    expect($comment->fresh()->trashed())->toBeTrue();
});

test('sanctum authenticated delete to api/private/comments/{comment} is forbidden for a non-owner', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/private/comments/{$comment->id}")
        ->assertForbidden();
});

test('unauthenticated delete to api/private/comments/{comment} returns 401', function () {
    $comment = Comment::factory()->create();

    $this->deleteJson("/api/private/comments/{$comment->id}")
        ->assertUnauthorized();
});
