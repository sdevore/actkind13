<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('invitations.invitation-list')
        ->assertStatus(200);
});
