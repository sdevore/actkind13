<?php

use App\Livewire\Acts\AddComment;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(AddComment::class)
        ->assertStatus(200);
});
