<?php

use App\Filament\Resources\Acts\Pages\ListActs;
use App\Filament\Resources\Comments\Pages\ListComments;
use App\Filament\Resources\Flags\Pages\ListFlags;
use App\Models\Act;
use App\Models\Comment;
use App\Models\Flag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->moderator = User::factory()->create();
    $this->moderator->assignRole('moderator');
    $this->actingAs($this->moderator);
});

function flagOn(Act|Comment $flaggable): Flag
{
    return Flag::factory()->create([
        'flaggable_id' => $flaggable->id,
        'flaggable_type' => $flaggable->getMorphClass(),
        'flagged_user_id' => $flaggable->user_id,
    ]);
}

test('moderators see flags on acts and comments labelled by type', function () {
    $actFlag = flagOn(Act::factory()->create());
    $commentFlag = flagOn(Comment::factory()->create());

    Livewire::test(ListFlags::class)
        ->assertCanSeeTableRecords([$actFlag, $commentFlag])
        ->assertTableColumnFormattedStateSet('flaggable_type', 'Act', $actFlag)
        ->assertTableColumnFormattedStateSet('flaggable_type', 'Comment', $commentFlag);
});

test('moderators can dismiss a flag', function () {
    $flag = flagOn(Act::factory()->create());

    Livewire::test(ListFlags::class)
        ->callAction(TestAction::make(DeleteAction::class)->table($flag));

    $this->assertSoftDeleted($flag);
});

test('moderators can remove flagged acts, which removes their comments and flags', function () {
    $act = Act::factory()->create();
    $comment = Comment::factory()->create(['act_id' => $act->id]);
    $flag = flagOn($act);

    Livewire::test(ListActs::class)
        ->selectTableRecords([$act])
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    $this->assertSoftDeleted($act);
    $this->assertSoftDeleted($comment);
    $this->assertSoftDeleted($flag);
});

test('moderators can remove flagged comments', function () {
    $comment = Comment::factory()->create();

    Livewire::test(ListComments::class)
        ->selectTableRecords([$comment])
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    $this->assertSoftDeleted($comment);
});
