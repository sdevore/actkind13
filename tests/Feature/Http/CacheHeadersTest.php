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

test('cookieless guests get an edge-cacheable page with no session, cookies or CSRF token', function (string $url) {
    $response = $this->get($url)->assertOk();

    expect($response->headers->getCookies())->toBeEmpty()
        ->and($response->headers->get('Cache-Control'))->toContain('public')
        ->toContain('s-maxage=300')
        ->toContain('max-age=0')
        ->and($response->getContent())->not->toContain('csrf-token');
})->with('public pages');

test('cookieless guests all receive the same page, so one cached copy is valid for every guest', function () {
    $first = $this->get(route('about'))->headers->get('ETag');
    Livewire::flushState();
    $second = $this->get(route('about'))->headers->get('ETag');

    expect($first)->not->toBeEmpty()->toBe($second);
});

test('visitors who already have a session get a private page with their session', function (string $url) {
    $response = $this->withCookie(config('session.cookie'), 'existing-session')->get($url)->assertOk();

    expect($response->headers->get('Cache-Control'))->toContain('private')
        ->not->toContain('public')
        ->and(collect($response->headers->getCookies())->map->getName())->toContain(config('session.cookie'))
        ->and($response->getContent())->toContain('csrf-token');
})->with('public pages');

test('visitors with a remember-me cookie get a private page', function () {
    $response = $this->withCookie('remember_web_abc', 'token')->get(route('acts.index'))->assertOk();

    expect($response->headers->get('Cache-Control'))->toContain('private')->not->toContain('public');
});

test('signed-in members get a private page with the member view', function () {
    $act = Act::factory()->create();

    $response = $this->actingAs(User::factory()->create())
        ->get(route('acts.show', $act))
        ->assertOk()
        ->assertSee($act->user->name);

    expect($response->headers->get('Cache-Control'))->toContain('private')->not->toContain('public');
});

test('unchanged public pages revalidate with a 304 via their ETag', function () {
    $etag = $this->get(route('about'))->assertOk()->headers->get('ETag');

    expect($etag)->not->toBeEmpty();

    // Livewire injects its styles once per process; reset it as a fresh production request would.
    Livewire::flushState();

    $this->get(route('about'), ['If-None-Match' => $etag])->assertStatus(304);
});
