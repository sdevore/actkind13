<?php

use App\Models\Invitation;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('registration screen prefills the invitation code from the signup link', function () {
    $this->get(route('register', ['code' => 'INVITE123']))
        ->assertOk()
        ->assertSee('value="INVITE123"', false);
});

function registrationData(string $code): array
{
    return [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
        'code' => $code,
    ];
}

test('new users can register with an unused invitation code', function () {
    $invitation = Invitation::factory()->unused()->create();

    $response = $this->post(route('register.store'), registrationData($invitation->code));

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('registering consumes the invitation', function () {
    $invitation = Invitation::factory()->unused()->create();

    $this->post(route('register.store'), registrationData($invitation->code));

    $invitation->refresh();
    $user = User::firstWhere('email', 'test@example.com');

    expect($invitation->joined_id)->toBe($user->id)
        ->and($invitation->joined_at)->not->toBeNull();
});

test('registration accepts an email different from the invited address', function () {
    $invitation = Invitation::factory()->unused()->create(['email' => 'invited@example.com']);

    $this->post(route('register.store'), registrationData($invitation->code))
        ->assertSessionHasNoErrors();

    $this->assertAuthenticated();
    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

test('registration is rejected for a code that matches no invitation', function () {
    $this->post(route('register.store'), registrationData('NOT-A-CODE'))
        ->assertSessionHasErrors('code');

    $this->assertGuest();
    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
});

test('registration is rejected for an invitation code that has already been used', function () {
    $invitation = Invitation::factory()->joined()->create();

    $this->post(route('register.store'), registrationData($invitation->code))
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('registration is rejected for a deleted invitation', function () {
    $invitation = Invitation::factory()->unused()->create();
    $invitation->delete();

    $this->post(route('register.store'), registrationData($invitation->code))
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});
