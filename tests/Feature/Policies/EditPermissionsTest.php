<?php

use App\Filament\Resources\Acts\Pages\EditAct;
use App\Filament\Resources\Invitations\Pages\EditInvitation;
use App\Models\Act;
use App\Models\Comment;
use App\Models\Invitation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function userWithRole(string $role): User
{
    return User::factory()->create()->assignRole($role);
}

test('administrators can edit another members act in the admin panel', function () {
    $this->actingAs(userWithRole('administrator'))
        ->get(EditAct::getUrl(['record' => Act::factory()->create()]))
        ->assertOk();
});

test('moderators cannot edit another members act in the admin panel', function () {
    $this->actingAs(userWithRole('moderator'))
        ->get(EditAct::getUrl(['record' => Act::factory()->create()]))
        ->assertForbidden();
});

test('administrators can update another members act through the API', function () {
    $act = Act::factory()->create();

    $this->actingAs(userWithRole('administrator'), 'sanctum')
        ->postJson("/api/private/acts/{$act->id}", [
            'title' => 'Edited by an admin',
            'description' => $act->description,
            'type' => $act->type->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Edited by an admin');
});

test('administrators can update another members comment through the API', function () {
    $comment = Comment::factory()->create();

    $this->actingAs(userWithRole('administrator'), 'sanctum')
        ->postJson("/api/private/comments/{$comment->id}", ['body' => 'Edited by an admin'])
        ->assertOk()
        ->assertJsonPath('data.body', 'Edited by an admin');
});

test('moderators cannot update another members comment through the API', function () {
    $comment = Comment::factory()->create();

    $this->actingAs(userWithRole('moderator'), 'sanctum')
        ->postJson("/api/private/comments/{$comment->id}", ['body' => 'Edited by a moderator'])
        ->assertForbidden();
});

test('administrators can edit another members invitation in the admin panel', function () {
    $this->actingAs(userWithRole('administrator'))
        ->get(EditInvitation::getUrl(['record' => Invitation::factory()->create()]))
        ->assertOk();
});

test('moderators cannot edit another members invitation in the admin panel', function () {
    $this->actingAs(userWithRole('moderator'))
        ->get(EditInvitation::getUrl(['record' => Invitation::factory()->create()]))
        ->assertForbidden();
});
