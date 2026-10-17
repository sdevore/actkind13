<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Mail\BulkEmailUsers;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Gate;
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

test('the email bulk action queues one email to each selected user', function () {
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

    Mail::assertQueued(BulkEmailUsers::class, 2);
    $recipients->each(fn (User $user) => Mail::assertQueued(BulkEmailUsers::class, fn (BulkEmailUsers $mail) => $mail->hasTo($user->email)));
    Mail::assertNotQueued(BulkEmailUsers::class, fn (BulkEmailUsers $mail) => $mail->hasTo($notSelected->email));
    Mail::assertNothingSent();
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

test('the email bulk action is hidden without the emailAny ability', function () {
    Gate::before(fn (User $user, string $ability) => $ability === 'emailAny' ? false : null);

    Livewire::test(ListUsers::class)
        ->assertActionVisible(TestAction::make('delete')->table()->bulk())
        ->assertActionHidden(TestAction::make('email')->table()->bulk());
});
