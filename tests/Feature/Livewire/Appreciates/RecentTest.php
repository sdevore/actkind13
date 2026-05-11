<?php

use App\Livewire\Appreciates\Recents;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Recents::class)
        ->assertStatus(200);
});
