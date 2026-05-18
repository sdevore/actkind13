<?php

/** @noinspection ALL */

use App\Models\Act;
use App\Models\Comment;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders successfully', function () {
    Livewire::test('comments.recents')
        ->assertStatus(200);
});

it('shows the correct number of recent comments to acts that the current user created', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $act = Act::factory()->create(['user_id' => $owner->id]);

    $comment = Comment::factory()->create([
        'act_id' => $act->id,
        'user_id' => $commenter->id,
        'body' => 'Great act of kindness!',
    ]);

    actingAs($owner);

    Livewire::test('comments.recents')
        ->assertSee($commenter->name)
        ->assertSee($act->title);
});
