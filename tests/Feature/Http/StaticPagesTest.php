<?php

test('markdown pages render their content with a matching title', function (string $route, string $title, string $heading) {
    $this->get(route($route))
        ->assertOk()
        ->assertSee(config('app.name').': '.$title)
        ->assertSee("<h1>{$heading}</h1>", false);
})->with([
    'terms' => ['terms', 'Terms', 'Terms of Service'],
    'policy' => ['policy', 'Policy', 'Privacy'],
    'about' => ['about', 'About', 'Act Kind'],
]);

test('the contact page shows the contact form under a Contact Us title', function () {
    $this->get(route('contact-us'))
        ->assertOk()
        ->assertSee(config('app.name').': Contact Us')
        ->assertSee('wire:submit.prevent="submit"', false)
        ->assertSee('kindness@example.com');
});

test('the contact page is rate limited to five requests a minute', function () {
    foreach (range(1, 5) as $attempt) {
        $this->get(route('contact-us'))->assertOk();
    }

    $this->get(route('contact-us'))->assertTooManyRequests();
});
