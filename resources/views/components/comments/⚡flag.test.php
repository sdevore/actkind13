<?php

/** @noinspection ALL */

use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

it('renders successfully', function () {
    $comment = Comment::factory()->create();
    Livewire::test('comments.flag', ['comment' => $comment])
        ->assertStatus(200);
});

it('shows the correct number of flags on a comment with 1 flag added', function () {
    Notification::fake();
    $user = User::factory()->create();
    Permission::findOrCreate('flag comments');
    $user->givePermissionTo('flag comments');

    $comment = Comment::factory()->create();

    // Directly create a flag or use the flag method
    $comment->flag($user, 'Inappropriate content');

    Livewire::test('comments.flag', ['comment' => $comment])
        ->assertSee('1');
});
