<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Mail\BulkEmailUsers;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::create(['name' => 'view admin panel']);
    Role::create(['name' => 'administrator'])->givePermissionTo('view admin panel');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('administrator');
    $this->actingAs($this->admin);
});

test('the email bulk action sends one email to each selected user', function () {
    Mail::fake();
    $recipients = User::factory()->count(2)->create();
    $notSelected = User::factory()->create();

    Livewire::test(ListUsers::class)
        ->selectTableRecords($recipients)
        ->callAction(TestAction::make('email')->table()->bulk(), data: [
            'subject' => 'Community update',
            'body' => 'Hello everyone',
            'email' => 'team@example.com',
            'name' => 'The Kind Team',
        ])
        ->assertHasNoFormErrors();

    Mail::assertSent(BulkEmailUsers::class, 2);
    $recipients->each(fn (User $user) => Mail::assertSent(BulkEmailUsers::class, fn (BulkEmailUsers $mail) => $mail->hasTo($user->email)));
    Mail::assertNotSent(BulkEmailUsers::class, fn (BulkEmailUsers $mail) => $mail->hasTo($notSelected->email));
});

test('the email bulk action requires a subject and body', function () {
    Mail::fake();

    Livewire::test(ListUsers::class)
        ->selectTableRecords(User::factory()->count(1)->create())
        ->callAction(TestAction::make('email')->table()->bulk(), data: [
            'subject' => '',
            'body' => '',
        ])
        ->assertHasFormErrors(['subject' => 'required', 'body' => 'required']);

    Mail::assertNothingSent();
});
