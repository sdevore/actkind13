<?php

use App\Livewire\ContactUs\ContactForm;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ContactForm::class)
        ->assertStatus(200);
});
