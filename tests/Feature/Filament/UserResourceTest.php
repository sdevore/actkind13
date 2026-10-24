<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::create(['name' => 'view admin panel']);
    Role::create(['name' => 'moderator'])->givePermissionTo('view admin panel');
    Role::create(['name' => 'administrator'])->givePermissionTo('view admin panel');
    Role::create(['name' => 'super-admin'])->givePermissionTo('view admin panel');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('administrator');
    $this->actingAs($this->admin);
});

test('an administrator can create a user with a password and a role', function () {
    Notification::fake();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New Member',
            'email' => 'new@example.com',
            'password' => 'a-secure-password',
            'roles' => [Role::findByName('moderator')->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $user = User::where('email', 'new@example.com')->firstOrFail();

    expect($user->name)->toBe('New Member')
        ->and(Hash::check('a-secure-password', $user->password))->toBeTrue()
        ->and($user->hasRole('moderator'))->toBeTrue();

    Notification::assertNothingSent();
});

test('creating a user without a password emails them a set-password link', function () {
    Notification::fake();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Invited Member',
            'email' => 'invited@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $user = User::where('email', 'invited@example.com')->firstOrFail();

    expect($user->password)->not->toBeEmpty();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('editing a user with a blank password keeps the current password', function () {
    $user = User::factory()->create(['password' => 'original-password']);

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => 'Renamed Member',
            'password' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->name)->toBe('Renamed Member')
        ->and(Hash::check('original-password', $user->password))->toBeTrue();
});

test('editing a user with a new password changes it', function () {
    $user = User::factory()->create(['password' => 'original-password']);

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm(['password' => 'replacement-password'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('replacement-password', $user->fresh()->password))->toBeTrue();
});

test('a user cannot be created with an email that is already taken', function () {
    $existing = User::factory()->create();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Duplicate',
            'email' => $existing->email,
            'password' => 'a-secure-password',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('an administrator cannot assign the super-admin role', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Escalated',
            'email' => 'escalated@example.com',
            'password' => 'a-secure-password',
            'roles' => [Role::findByName('super-admin')->id],
        ])
        ->call('create')
        ->assertHasFormErrors(['roles']);

    expect(User::where('email', 'escalated@example.com')->exists())->toBeFalse();
});

test('a super-admin can assign the super-admin role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    $this->actingAs($superAdmin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Second Super Admin',
            'email' => 'second-super@example.com',
            'password' => 'a-secure-password',
            'roles' => [Role::findByName('super-admin')->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::where('email', 'second-super@example.com')->firstOrFail()->hasRole('super-admin'))->toBeTrue();
});

test('an administrator editing a super-admin does not remove their super-admin role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    Livewire::test(EditUser::class, ['record' => $superAdmin->id])
        ->fillForm(['name' => 'Renamed Super Admin'])
        ->call('save')
        ->assertHasNoFormErrors();

    $superAdmin->refresh();

    expect($superAdmin->name)->toBe('Renamed Super Admin')
        ->and($superAdmin->hasRole('super-admin'))->toBeTrue();
});
