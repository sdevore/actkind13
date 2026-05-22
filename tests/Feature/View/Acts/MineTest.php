<?php

use App\Models\User;

it('can render', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

        $contents = $this->view('acts.mine', [
        //
    ]);

    $contents->assertSee('');
});
