<?php

namespace Altwaireb\World\Tests;

use Altwaireb\World\WorldServiceProvider;
use Illuminate\Support\Facades\File;
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
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
    }

    protected function tearDown(): void
    {
        try {
            $filesToDelete = [
                app_path('Models/Country.php'),
                app_path('Models/State.php'),
                app_path('Models/City.php'),
                database_path('seeders/WorldTableSeeder.php'),
                config_path('world.php'),
            ];

            foreach ($filesToDelete as $file) {
                if (File::exists($file)) {
                    File::delete($file);
                }
            }

            foreach (
                glob(database_path('migrations/*_create_world_table.php') ?: []) as $migrationFile
            ) {
                File::delete($migrationFile);
            }
        } finally {
            parent::tearDown();
        }
    }
}
