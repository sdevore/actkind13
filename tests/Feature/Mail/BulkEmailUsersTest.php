<?php

use App\Mail\BulkEmailUsers;
use App\Models\User;

function bulkEmailData(): array
{
    return [
        'subject' => 'Community update',
        'body' => 'Thanks for **being kind** this month.',
        'email' => 'team@example.com',
        'name' => 'The Kind Team',
    ];
}

test('the bulk email renders the subject and markdown body for the recipient', function () {
    $user = User::factory()->create(['name' => 'Jamie']);

    $mail = new BulkEmailUsers(bulkEmailData(), $user);

    $mail->assertHasSubject('Community update')
        ->assertSeeInHtml('Jamie')
        ->assertSeeInHtml('being kind</strong>', false);
});

test('the bulk email is sent from the address chosen in the form', function () {
    $mail = new BulkEmailUsers(bulkEmailData(), User::factory()->create());

    $mail->assertFrom('team@example.com', 'The Kind Team');
});
