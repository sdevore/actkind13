<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('invitations.create')
        ->assertStatus(200);
})->group('components');
