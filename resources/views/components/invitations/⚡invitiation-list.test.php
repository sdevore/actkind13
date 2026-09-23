<?php

use App\Models\Invitation;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('invitations.invitation-list')
        ->assertStatus(200);
});

it('hides resend and delete for invitations that have been used', function () {
    $invitation = Invitation::factory()->joined()->create();

    Livewire::actingAs($invitation->user)
        ->test('invitations.invitation-list')
        ->filterTable('Joined', true)
        ->assertActionHidden(TestAction::make('resend')->table($invitation))
        ->assertActionHidden(TestAction::make('delete')->table($invitation));
});

it('shows resend and delete for unused invitations', function () {
    $invitation = Invitation::factory()->unused()->create();

    Livewire::actingAs($invitation->user)
        ->test('invitations.invitation-list')
        ->assertActionVisible(TestAction::make('resend')->table($invitation))
        ->assertActionVisible(TestAction::make('delete')->table($invitation));
});
