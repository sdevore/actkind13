<?php

use App\Livewire\Comments\Recents;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Recents::class)
        ->assertStatus(200);
});
