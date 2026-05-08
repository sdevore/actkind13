<?php

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
arch('app')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();

arch('it will not use ray function')
    ->expect(['ray'])
    ->each->not->toBeUsed();

arch('it will not use debugging functions')
    ->expect(['dd', 'dump'])
    ->each->not->toBeUsed();
