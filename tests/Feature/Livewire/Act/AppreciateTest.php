<?php

use App\Livewire\Acts\Appreciate;
use App\Models\Act;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Appreciate::class, ['act' => Act::factory()->create()])
        ->assertStatus(200);
});
