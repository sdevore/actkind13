<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('comments.recents')
        ->assertStatus(200);
});
