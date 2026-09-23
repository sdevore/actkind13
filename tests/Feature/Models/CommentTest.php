<?php

use App\Models\Appreciate;
use App\Models\Comment;
use App\Models\Flag;
use App\Models\User;
use App\Notifications\CommentFlagged;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;

test('deleting a comment soft-deletes its flags and removes its appreciations', function () {
    $comment = Comment::factory()->create();
    $appreciate = $comment->appreciates()->create(['user_id' => User::factory()->create()->id]);
    $flag = Flag::factory()->create([
        'flaggable_id' => $comment->id,
        'flaggable_type' => $comment->getMorphClass(),
        'flagged_user_id' => $comment->user_id,
    ]);

    $comment->delete();

    $this->assertSoftDeleted($comment);
    $this->assertSoftDeleted($flag);
    expect(Appreciate::find($appreciate->id))->toBeNull();
});

test('a moderator flagging a comment records the flag and notifies the comments author', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();
    $moderator = User::factory()->create()->assignRole('moderator');
    $comment = Comment::factory()->create();

    $flag = $comment->flag($moderator, 'Rude');

    expect($flag)->toBeInstanceOf(Flag::class)
        ->and($flag->flagged_user_id)->toBe($comment->user_id);
    Notification::assertSentTo($comment->user, CommentFlagged::class);
});

test('flagging the same comment twice does not create a second flag or notification', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $moderator = User::factory()->create()->assignRole('moderator');
    $comment = Comment::factory()->create();
    $comment->flag($moderator, 'Rude');
    Notification::fake();

    expect($comment->flag($moderator, 'Still rude'))->toBeFalse()
        ->and($comment->flags()->count())->toBe(1);
    Notification::assertNothingSent();
});

test('the comment flagged email links the author to the act they commented on', function () {
    $comment = Comment::factory()->create();
    $flag = Flag::factory()->create([
        'flaggable_id' => $comment->id,
        'flaggable_type' => $comment->getMorphClass(),
        'flagged_user_id' => $comment->user_id,
    ]);

    $mail = (new CommentFlagged($flag))->toMail($comment->user);

    expect($mail->actionUrl)->toBe(route('acts.show', $comment->act));
});
