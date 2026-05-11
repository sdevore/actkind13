<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('appreciates.recents')
        ->assertStatus(200);
});
