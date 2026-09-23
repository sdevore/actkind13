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
