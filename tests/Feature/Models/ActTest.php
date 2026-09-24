<?php

use App\Models\Act;
use App\Models\Appreciate;
use App\Models\Comment;
use App\Models\Flag;
use App\Models\User;
use App\Notifications\ActFlagged;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\UnauthorizedException;

test('deleting an act soft-deletes its comments and flags and removes its appreciations', function () {
    $act = Act::factory()->create();
    $comment = Comment::factory()->create(['act_id' => $act->id]);
    $appreciate = $act->appreciates()->create(['user_id' => User::factory()->create()->id]);
    $flag = Flag::factory()->create([
        'flaggable_id' => $act->id,
        'flaggable_type' => $act->getMorphClass(),
        'flagged_user_id' => $act->user_id,
    ]);

    $act->delete();

    $this->assertSoftDeleted($act);
    $this->assertSoftDeleted($comment);
    $this->assertSoftDeleted($flag);
    expect(Appreciate::find($appreciate->id))->toBeNull();
});

test('deleting an act leaves other acts content untouched', function () {
    $act = Act::factory()->create();
    $otherComment = Comment::factory()->create();

    $act->delete();

    $this->assertNotSoftDeleted($otherComment);
});

test('a moderator flagging an act records the flag and notifies the acts author', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();
    $moderator = User::factory()->create()->assignRole('moderator');
    $act = Act::factory()->create();

    $flag = $act->flag($moderator, 'Not kind');

    expect($flag)->toBeInstanceOf(Flag::class)
        ->and($flag->reason)->toBe('Not kind')
        ->and($flag->flagged_user_id)->toBe($act->user_id);
    Notification::assertSentTo($act->user, ActFlagged::class);
});

test('flagging the same act twice does not create a second flag or notification', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $moderator = User::factory()->create()->assignRole('moderator');
    $act = Act::factory()->create();
    $act->flag($moderator, 'Not kind');
    Notification::fake();

    expect($act->flag($moderator, 'Still not kind'))->toBeFalse()
        ->and($act->flags()->count())->toBe(1);
    Notification::assertNothingSent();
});

test('users without the flag acts permission cannot flag an act', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();
    $act = Act::factory()->create();

    expect(fn () => $act->flag(User::factory()->create(), 'Not kind'))
        ->toThrow(UnauthorizedException::class, 'You are not authorized to flag acts');

    expect($act->flags()->count())->toBe(0);
    Notification::assertNothingSent();
});

test('the act flagged email links the author to their act', function () {
    $act = Act::factory()->create();
    $flag = Flag::factory()->create([
        'flaggable_id' => $act->id,
        'flaggable_type' => $act->getMorphClass(),
        'flagged_user_id' => $act->user_id,
    ]);

    $mail = (new ActFlagged($flag))->toMail($act->user);

    expect($mail->actionUrl)->toBe(route('acts.show', $act));
});
