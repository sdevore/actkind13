<?php

use App\Models\Appreciate;
use App\Models\Comment;
use App\Models\Flag;
use App\Models\User;

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
