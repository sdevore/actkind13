<?php

use App\Models\Act;
use App\Models\User;

it('can render', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $acts = Act::factory()->count(5)->create(['user_id' => $user->id]);
    $act = $acts->first();
    $contents = $this->view('acts.mine', ['acts' => $acts,

    ]);

    $contents->assertSee($act->title);
});

it('can render when user has no acts', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $contents = $this->view('acts.mine', ['acts' => collect([]),

    ]);

    $contents->assertSee('shared any acts of kindness');
});
