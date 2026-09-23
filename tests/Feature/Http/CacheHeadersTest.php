<?php

use App\Models\Act;
use App\Models\User;
use Livewire\Livewire;

dataset('public pages', [
    'welcome' => fn () => route('home'),
    'acts' => fn () => route('acts.index'),
    'act' => fn () => route('acts.show', Act::factory()->create()),
    'terms' => fn () => route('terms'),
    'about' => fn () => route('about'),
]);

test('public pages are never cacheable by shared caches because they carry a session and CSRF token', function (string $url) {
    $cacheControl = $this->get($url)->assertOk()->headers->get('Cache-Control');

    expect($cacheControl)->toContain('private')
        ->not->toContain('public')
        ->not->toContain('s-maxage');
})->with('public pages');

test('public pages are not served fresh from the browser cache, so logging in shows the member view at once', function (string $url) {
    expect($this->get($url)->headers->get('Cache-Control'))->not->toMatch('/max-age=[1-9]/');
})->with('public pages');

test('signed-in pages are private', function () {
    $this->actingAs(User::factory()->create());

    expect($this->get(route('acts.index'))->headers->get('Cache-Control'))->toContain('private')
        ->not->toContain('public');
});

test('unchanged public pages revalidate with a 304 via their ETag', function () {
    $etag = $this->get(route('about'))->assertOk()->headers->get('ETag');

    expect($etag)->not->toBeEmpty();

    // Livewire injects its styles once per process; reset it as a fresh production request would.
    Livewire::flushState();

    $this->get(route('about'), ['If-None-Match' => $etag])->assertStatus(304);
});
