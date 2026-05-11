<?php

use App\Livewire\Acts\Flag;
use App\Models\Act;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Flag::class, ['act' => Act::factory()->create()])
        ->assertStatus(200);
});
