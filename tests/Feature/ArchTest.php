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

arch('controllers extend the base controller and are suffixed')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toHaveSuffix('Controller')
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring('App\Http\Controllers\Controller');

arch('controllers only expose resource actions')
    ->expect('App\Http\Controllers')
    ->not->toHavePublicMethodsBesides(['index', 'show', 'store', 'update', 'destroy', '__invoke', 'mine']);
