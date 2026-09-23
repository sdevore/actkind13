<?php

/** @noinspection ALL */

use App\Models\Comment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
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

it('does not let members flag a comment', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $comment = Comment::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('comments.flag', ['comment' => $comment])
        ->set('reason', 'This is not kind at all')
        ->call('save')
        ->assertForbidden();

    expect($comment->flags()->count())->toBe(0);
});

it('lets moderators flag a comment with a reason', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();
    $comment = Comment::factory()->create();

    Livewire::actingAs(User::factory()->create()->assignRole('moderator'))
        ->test('comments.flag', ['comment' => $comment])
        ->set('reason', 'This is not kind at all')
        ->call('save')
        ->assertHasNoErrors();

    expect($comment->flags()->sole()->reason)->toBe('This is not kind at all');
});
