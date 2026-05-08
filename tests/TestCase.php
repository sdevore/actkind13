<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use JMac\Testing\Traits\AdditionalAssertions;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    use AdditionalAssertions;
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function todo(): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        $this->markTestIncomplete(sprintf('Todo: %s::%s', $caller['class'], $caller['function']));
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
