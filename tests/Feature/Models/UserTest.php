<?php

use App\Mail\InviteUser;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('sending an invitation records the user as the inviter and queues the email', function () {
    Mail::fake();
    $user = User::factory()->create();
    $invitation = Invitation::factory()->make();

    $sent = $user->sendInvitation($invitation);

    expect($sent->exists)->toBeTrue()
        ->and($sent->user_id)->toBe($user->id);
    Mail::assertQueued(InviteUser::class);
});

test('sending an invitation on behalf of another user records them as the inviter', function () {
    Mail::fake();
    $admin = User::factory()->create();
    $inviter = User::factory()->create();

    $sent = $admin->sendInvitation(Invitation::factory()->make(), $inviter->id);

    expect($sent->user_id)->toBe($inviter->id);
});
