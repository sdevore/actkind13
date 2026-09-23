<?php

use App\Mail\InviteUser;
use App\Models\Invitation;
use App\Models\User;

test('the invitation email is addressed from the app and replies to the inviter', function () {
    $invitation = Invitation::factory()->create();

    $mail = new InviteUser($invitation);

    $mail->assertFrom(config('mail.from.address'))
        ->assertHasReplyTo($invitation->user->email)
        ->assertHasSubject('You have been invited to join '.config('app.name'));
});

test('the invitation email includes the invitee name, inviter, code and a signup link carrying the code', function () {
    $invitation = Invitation::factory()->create([
        'name' => "Merritt D'Amore",
        'user_id' => User::factory()->create(['name' => "Pat O'Brien"])->id,
    ]);

    $mail = new InviteUser($invitation);

    $mail->assertSeeInHtml("Merritt D'Amore", false)
        ->assertSeeInHtml("Pat O'Brien", false)
        ->assertSeeInHtml($invitation->code)
        ->assertSeeInHtml(route('register', ['code' => $invitation->code]), false);
});

test('the invitation email links to the support address', function () {
    config(['mail.from.address' => 'help@example.com']);
    $invitation = Invitation::factory()->create();

    (new InviteUser($invitation))->assertSeeInHtml('mailto:help@example.com', false);
});

test('the invitation email does not require registering with the invited address', function () {
    $invitation = Invitation::factory()->create();

    (new InviteUser($invitation))->assertDontSeeInText('use the same email');
});
