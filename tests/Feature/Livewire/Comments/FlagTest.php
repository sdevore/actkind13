<?php

use App\Livewire\Comments\Flag;
use App\Models\Comment;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Flag::class, ['comment' => Comment::factory()->create()])
        ->assertStatus(200);
});
