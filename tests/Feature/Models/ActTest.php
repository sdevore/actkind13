<?php

use App\Models\Act;
use App\Models\Appreciate;
use App\Models\Comment;
use App\Models\Flag;
use App\Models\User;

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
