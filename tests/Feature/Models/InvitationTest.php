<?php

use App\Mail\InviteUser;
use App\Models\Invitation;
use Illuminate\Support\Facades\Mail;

test('sending an invitation blind-copies the configured address', function () {
    Mail::fake();
    config(['mail.invitations.bcc' => 'copies@example.com']);
    $invitation = Invitation::factory()->create();

    $invitation->send();

    Mail::assertSent(InviteUser::class, fn (InviteUser $mail) => $mail->hasTo($invitation->email)
        && $mail->hasBcc('copies@example.com'));
});

test('sending an invitation without a configured bcc sends no blind copy', function () {
    Mail::fake();
    config(['mail.invitations.bcc' => null]);
    $invitation = Invitation::factory()->create();

    $invitation->send();

    Mail::assertSent(InviteUser::class, fn (InviteUser $mail) => $mail->hasTo($invitation->email)
        && $mail->bcc === []);
});

test('queueing an invitation queues the invite email to the invitee and counts the send', function () {
    Mail::fake();
    $invitation = Invitation::factory()->create(['send_ct' => 0]);

    $invitation->send(shouldQueue: true);

    Mail::assertQueued(InviteUser::class, fn (InviteUser $mail) => $mail->hasTo($invitation->email));
    Mail::assertNothingSent();
    expect($invitation->fresh()->send_ct)->toBe(1);
});
