<?php

use App\Mail\InviteUser;
use App\Models\ContactUs;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('contact us can create and attach an invitation for a specific inviter', function () {
    Mail::fake();

    $inviter = User::factory()->create();
    $contactUs = ContactUs::factory()->create();

    $invitation = $contactUs->sendInvitation($inviter);

    expect($invitation)
        ->toBeInstanceOf(Invitation::class)
        ->and($invitation->user_id)->toBe($inviter->id)
        ->and($invitation->email)->toBe($contactUs->email)
        ->and($invitation->send_ct)->toBe(1)
        ->and($contactUs->fresh()->invitation_id)->toBe($invitation->id);

    Mail::assertQueued(InviteUser::class);
});

test('contact us can resend its existing invitation for a specific inviter', function () {
    Mail::fake();

    $originalInviter = User::factory()->create();
    $resendingUser = User::factory()->create();
    $invitation = Invitation::factory()->create([
        'user_id' => $originalInviter->id,
        'send_ct' => 1,
    ]);

    $contactUs = ContactUs::factory()->create([
        'invitation_id' => $invitation->id,
    ]);

    $resentInvitation = $contactUs->resendInvitation($resendingUser)->fresh();

    expect($resentInvitation)
        ->toBeInstanceOf(Invitation::class)
        ->and($resentInvitation->id)->toBe($invitation->id)
        ->and($resentInvitation->user_id)->toBe($originalInviter->id)
        ->and($resentInvitation->send_ct)->toBe(2);

    Mail::assertQueued(InviteUser::class, 1);
});
