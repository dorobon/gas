<?php

namespace Tests;

use App\Libraries\Fuel\FuelDataBootstrapLibrary;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            $this->app->make(FuelDataBootstrapLibrary::class)->ensureReady();
        }
    }
}
