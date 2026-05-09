<?php

use App\Mail\ContactUsMailable;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('contact-us.form')
        ->assertStatus(200);
});

it('validates name is required', function () {
    Livewire::test('contact-us.form')
        ->set('name', '')
        ->call('submit')
        ->assertHasErrors(['name' => 'required']);
});

it('validates name is at least 3 characters', function () {
    Livewire::test('contact-us.form')
        ->set('name', 'Jo')
        ->call('submit')
        ->assertHasErrors(['name' => 'min']);
});

it('validates name is at most 255 characters', function () {
    Livewire::test('contact-us.form')
        ->set('name', str_repeat('a', 256))
        ->call('submit')
        ->assertHasErrors(['name' => 'max']);
});

it('validates email is required', function () {
    Livewire::test('contact-us.form')
        ->set('email', '')
        ->call('submit')
        ->assertHasErrors(['email' => 'required']);
});

it('validates email is a valid email', function () {
    Livewire::test('contact-us.form')
        ->set('email', 'not-an-email')
        ->call('submit')
        ->assertHasErrors(['email' => 'email']);
});

it('validates email is at least 5 characters', function () {
    Livewire::test('contact-us.form')
        ->set('email', 'a@b')
        ->call('submit')
        ->assertHasErrors(['email' => 'min']);
});

it('validates email is at most 255 characters', function () {
    Livewire::test('contact-us.form')
        ->set('email', str_repeat('a', 246).'@example.com')
        ->call('submit')
        ->assertHasErrors(['email' => 'max']);
});

it('validates where_from is at most 255 characters', function () {
    Livewire::test('contact-us.form')
        ->set('where_from', str_repeat('a', 256))
        ->call('submit')
        ->assertHasErrors(['where_from' => 'max']);
});

it('validates message is at least 10 characters', function () {
    Livewire::test('contact-us.form')
        ->set('message', 'too short')
        ->call('submit')
        ->assertHasErrors(['message' => 'min']);
});

it('validates message is at most 255 characters', function () {
    Livewire::test('contact-us.form')
        ->set('message', str_repeat('a', 256))
        ->call('submit')
        ->assertHasErrors(['message' => 'max']);
});

it('allows nullable where_from and message', function () {
    Livewire::test('contact-us.form')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('where_from', '')
        ->set('message', '')
        ->call('submit')
        ->assertHasNoErrors(['where_from', 'message']);
});

it('sends an email and redirects to the home route on success', function () {
    Mail::fake();

    Livewire::test('contact-us.form')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('where_from', 'Google')
        ->set('message', 'Hello, I would like to join.')
        ->call('submit')
        ->assertRedirect(route('home'));

    Mail::assertSent(ContactUsMailable::class, function ($mail) {
        return $mail->contactUs->name === 'John Doe' &&
               $mail->contactUs->email === 'john@example.com';
    });
});
