<?php

use App\Livewire\Comments\CommentsList;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CommentsList::class)
        ->assertStatus(200);
});
