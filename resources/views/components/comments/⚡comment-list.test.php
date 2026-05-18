<?php

/** @noinspection ALL */

use App\Models\Act;
use App\Models\Comment;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders successfully', function () {
    $act = Act::factory()->create();
    Livewire::test('comments.comment-list', ['act' => $act])
        ->assertStatus(200);
});

it('shows the correct number of comments on an act', function () {
    $user = User::factory()->create();
    $act = Act::factory()->create();
    $comments = Comment::factory()->count(3)->create([
        'act_id' => $act->id,
        'user_id' => $user->id,
    ]);

    actingAs($user);

    $test = Livewire::test('comments.comment-list', ['act' => $act]);

    foreach ($comments as $comment) {
        $test->assertSee($comment->body);
    }
});
