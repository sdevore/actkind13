<?php

use Livewire\Livewire;

test('unchanged public pages revalidate with a 304 via their ETag', function () {
    $etag = $this->get(route('about'))->assertOk()->headers->get('ETag');

    expect($etag)->not->toBeEmpty();

    // Livewire injects its styles once per process; reset it as a fresh production request would.
    Livewire::flushState();

    $this->get(route('about'), ['If-None-Match' => $etag])->assertStatus(304);
});
