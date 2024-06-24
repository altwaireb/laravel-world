<?php

namespace Altwaireb\World;

use Altwaireb\World\Commands\InstallWorldCommand;
use Altwaireb\World\Commands\SeederWorldCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WorldServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {

        $package
            ->name('laravel-world')
            ->hasConfigFile()
            ->hasMigration('create_world_table')
            ->hasCommands([
                InstallWorldCommand::class,
                SeederWorldCommand::class,
            ]);
    }

    public function bootingPackage(): void
    {
        parent::bootingPackage();

        if ($this->app->runningInConsole()) {
            // publishes Models
            $this->publishes([
                __DIR__.'/../stubs/Models/Country.php.stub' => app_path('Models/Country.php'),
                __DIR__.'/../stubs/Models/State.php.stub' => app_path('Models/State.php'),
                __DIR__.'/../stubs/Models/City.php.stub' => app_path('Models/City.php'),
            ], 'world-models');
            // publishes Seeders
            $this->publishes([
                __DIR__.'/../stubs/database/seeders/WorldTableSeeder.php.stub' => database_path('seeders/WorldTableSeeder.php'),
            ], 'world-seeders');
        }
    }
}
