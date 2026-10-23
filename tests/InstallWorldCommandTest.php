<?php

use Altwaireb\World\World;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

it('registers the world install command', function () {
    expect(Artisan::all())
        ->toHaveKey('world:install');
});

it('publishes the package files', function () {
    $this->artisan('vendor:publish', ['--tag' => 'world-config'])
        ->assertSuccessful();

    $this->artisan('vendor:publish', ['--tag' => 'world-migrations'])
        ->assertSuccessful();

    $this->artisan('vendor:publish', ['--tag' => 'world-models'])
        ->assertSuccessful();

    $this->artisan('vendor:publish', ['--tag' => 'world-seeders'])
        ->assertSuccessful();

    expect(file_exists(config_path('world.php')))->toBeTrue()
        ->and(count(glob(database_path('migrations/*_create_world_table.php'))))->toBe(1)
        ->and(file_exists(app_path('Models/Country.php')))->toBeTrue()
        ->and(file_exists(app_path('Models/State.php')))->toBeTrue()
        ->and(file_exists(app_path('Models/City.php')))->toBeTrue()
        ->and(file_exists(database_path('seeders/WorldTableSeeder.php')))->toBeTrue();
});

it('runs the world install command without optional actions', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'no')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();
});

it('runs migrations when confirmed during installation', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    expect(Schema::hasTable('countries'))->toBeTrue()
        ->and(Schema::hasTable('states'))->toBeTrue()
        ->and(Schema::hasTable('cities'))->toBeTrue();
});

it('publishes package files through the install command', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'no')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    expect(file_exists(config_path('world.php')))->toBeTrue()
        ->and(count(glob(database_path('migrations/*_create_world_table.php'))))->toBe(1)
        ->and(file_exists(app_path('Models/Country.php')))->toBeTrue()
        ->and(file_exists(app_path('Models/State.php')))->toBeTrue()
        ->and(file_exists(app_path('Models/City.php')))->toBeTrue()
        ->and(file_exists(database_path('seeders/WorldTableSeeder.php')))->toBeTrue();
});

it('caches countries data after the first read', function () {
    $first = World::getCountries();
    $second = World::getCountries();

    expect($first)->toBe($second);
});

it('clears cached countries data', function () {
    $first = World::getCountries();

    World::clearCaches();

    $second = World::getCountries();

    expect($second)->toEqual($first)
        ->and($second)->not->toBe($first);
});
