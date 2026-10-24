<?php

use App\Models\Act;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

it('renders successfully', function () {
    $comment = Comment::factory()->create();
    Livewire::test('comments.flag', ['comment' => $comment])
        ->assertStatus(200);
})->group('components');

it('shows the correct number of flags on a comment with 1 flag added', function () {
    Notification::fake();
    $user = User::factory()->create();
    Permission::findOrCreate('flag comments');
    $user->givePermissionTo('flag comments');

    $comment = Comment::factory()->create();

    // Directly create a flag or use the flag method
    $comment->flag($user, 'Inappropriate content');

    Livewire::test('comments.flag', ['comment' => $comment])
        ->assertSee('1');
})->group('components');

it('shows each flagger as an initials avatar instead of a blank image', function (string $component, string $model) {
    Notification::fake();
    $flagger = User::factory()->create(['name' => 'Jane Doe']);
    $flagger->givePermissionTo(Permission::findOrCreate('flag acts'), Permission::findOrCreate('flag comments'));
    $flaggable = $model::factory()->create();
    $flaggable->flag($flagger, 'Inappropriate content');

    Livewire::test($component, [lcfirst(class_basename($model)) => $flaggable->fresh()])
        ->assertSeeHtml('data-flux-avatar')
        ->assertSee('JD')
        ->assertSee('Jane Doe')
        ->assertDontSeeHtml('<img');
})->with([
    'act' => ['acts.flag', Act::class],
    'comment' => ['comments.flag', Comment::class],
])->group('components');
