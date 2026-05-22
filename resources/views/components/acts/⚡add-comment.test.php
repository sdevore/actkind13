<?php

/** @noinspection ALL */

use App\Models\Act;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\ActCommented;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

it('renders successfully', function () {
    Livewire::test('acts.add-comment')
        ->assertStatus(200);
})->group('components');

it('can add a comment to an act', function () {
    Notification::fake();
    $user = User::factory()->create();
    $actOwner = User::factory()->create();
    $act = Act::factory()->create(['user_id' => $actOwner->id]);

    actingAs($user);

    Livewire::test('acts.add-comment', ['act' => $act])
        ->set('showForm', true)
        ->set('body', 'This is a test comment')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('saved')
        ->assertSet('showForm', false)
        ->assertSet('body', '');

    assertDatabaseHas(Comment::class, [
        'act_id' => $act->id,
        'user_id' => $user->id,
        'body' => 'This is a test comment',
    ]);

    Notification::assertSentTo($actOwner, ActCommented::class);
})->group('components');
