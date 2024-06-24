<?php

namespace Altwaireb\World\Tests;

use Altwaireb\World\WorldServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        //
    }

    protected function getPackageProviders($app): array
    {
        return [
            WorldServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_world_table.php.stub';
        $migration->up();
        */
    }
}
