<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('invitations.list')
        ->assertStatus(200);
});
