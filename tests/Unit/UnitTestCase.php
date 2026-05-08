<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Tests\Exceptions\DatabaseAccessException;
use Tests\TestCase;

abstract class UnitTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->alertUnwantedDBAccess();
    }

    protected function alertUnwantedDBAccess(): void
    {
        DB::listen(function ($query) {
            throw new DatabaseAccessException($query->sql);
        });
    }
}
