<?php

use App\Mail\InviteUser;
use App\Models\Invitation;

test('the invitation email is addressed from the app and replies to the inviter', function () {
    $invitation = Invitation::factory()->create();

    $mail = new InviteUser($invitation);

    $mail->assertFrom(config('mail.from.address'))
        ->assertHasReplyTo($invitation->user->email)
        ->assertHasSubject('You have been invited to join '.config('app.name'));
});

test('the invitation email includes the invitee name, inviter, code and a signup link carrying the code', function () {
    $invitation = Invitation::factory()->create();

    $mail = new InviteUser($invitation);

    $mail->assertSeeInHtml($invitation->name)
        ->assertSeeInHtml($invitation->user->name)
        ->assertSeeInHtml($invitation->code)
        ->assertSeeInHtml(route('register', ['code' => $invitation->code]), false);
});

test('the invitation email links to the support address', function () {
    config(['mail.from.address' => 'help@example.com']);
    $invitation = Invitation::factory()->create();

    (new InviteUser($invitation))->assertSeeInHtml('mailto:help@example.com', false);
});
