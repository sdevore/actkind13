<?php

use App\Livewire\Acts\CreateAct;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CreateAct::class)
        ->assertStatus(200);
});
