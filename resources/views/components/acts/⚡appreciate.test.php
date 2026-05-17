<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('acts.appreciate')
        ->assertStatus(200);
});
