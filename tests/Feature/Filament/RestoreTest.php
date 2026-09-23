<?php

use App\Filament\Resources\Acts\Pages\ListActs;
use App\Filament\Resources\Comments\Pages\ListComments;
use App\Filament\Resources\Invitations\Pages\ListInvitations;
use App\Models\Act;
use App\Models\Comment;
use App\Models\Invitation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

dataset('restorable resources', [
    'acts' => [ListActs::class, fn (): Model => Act::factory()->create()],
    'comments' => [ListComments::class, fn (): Model => Comment::factory()->create()],
    'invitations' => [ListInvitations::class, fn (): Model => Invitation::factory()->create()],
]);

test('administrators can restore a deleted record from the admin table', function (string $page, Closure $makeRecord) {
    $record = $makeRecord();
    $record->delete();
    $this->actingAs(User::factory()->create()->assignRole('administrator'));

    Livewire::test($page)
        ->filterTable('trashed', true)
        ->callAction(TestAction::make(RestoreAction::class)->table($record));

    expect($record->fresh()->trashed())->toBeFalse();
})->with('restorable resources');

test('administrators can bulk restore deleted records', function (string $page, Closure $makeRecord) {
    $record = $makeRecord();
    $record->delete();
    $this->actingAs(User::factory()->create()->assignRole('administrator'));

    Livewire::test($page)
        ->filterTable('trashed', true)
        ->selectTableRecords([$record->getKey()])
        ->callAction(TestAction::make(RestoreBulkAction::class)->table()->bulk());

    expect($record->fresh()->trashed())->toBeFalse();
})->with('restorable resources');

test('moderators cannot restore deleted records', function (string $page, Closure $makeRecord) {
    $record = $makeRecord();
    $record->delete();
    $this->actingAs(User::factory()->create()->assignRole('moderator'));

    Livewire::test($page)
        ->filterTable('trashed', true)
        ->assertActionHidden(TestAction::make(RestoreAction::class)->table($record));

    expect($record->fresh()->trashed())->toBeTrue();
})->with('restorable resources');
