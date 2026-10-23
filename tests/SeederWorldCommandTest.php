<?php

use Altwaireb\World\Exceptions\InvalidCodeException;
use Altwaireb\World\Exceptions\IsoCodesIsEmptyException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Command\Command;

it('returns invalid when the seeder file is not published', function () {
    $this->artisan('world:seeder')
        ->expectsOutputToContain(
            'Please RUN `php artisan vendor:publish --tag=world-seeders` to publish seeder class'
        )
        ->assertExitCode(Command::INVALID);
});

it('returns invalid when the migration file is not published', function () {
    $this->artisan('vendor:publish', [
        '--tag' => 'world-seeders',
        '--force' => true,
    ])->assertSuccessful();

    $migrationPath = collect(
        glob(database_path('migrations/*_create_world_table.php') ?: [])
    )->first();

    if ($migrationPath !== null) {
        unlink($migrationPath);
    }

    $this->artisan('world:seeder')
        ->expectsOutputToContain(
            'Please RUN `php artisan vendor:publish --tag=world-migrations` to publish migrations tables'
        )
        ->assertExitCode(Command::INVALID);
});

it('returns invalid when the countries table contains data', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    DB::table('countries')->insert([
        'name' => 'Test Country',
        'iso2' => 'TC',
        'iso3' => 'TST',
        'is_active' => true,
    ]);

    require_once database_path('seeders/WorldTableSeeder.php');

    $this->artisan('world:seeder')
        ->expectsOutputToContain(
            "You can't Seeding in countries table because table has data."
        )
        ->assertExitCode(Command::INVALID);
});

it('returns invalid when seeding tables already contain data', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', ['SA']);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    require_once database_path('seeders/WorldTableSeeder.php');

    $this->artisan('world:seeder')
        ->assertSuccessful();

    $this->artisan('world:seeder')
        ->expectsOutputToContain(
            "You can't Seeding in countries table because table has data."
        )
        ->assertExitCode(Command::INVALID);
});

it('seeds only configured countries and their related data', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', ['SA']);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    require_once database_path('seeders/WorldTableSeeder.php');

    $this->artisan('world:seeder')
        ->assertSuccessful();

    expect(DB::table('countries')->count())
        ->toBe(1)
        ->and(DB::table('countries')->value('iso2'))
        ->toBe('SA')
        ->and(DB::table('states')->where('country_id', 194)->count())
        ->toBeGreaterThan(0)
        ->and(DB::table('cities')->where('country_id', 194)->count())
        ->toBeGreaterThan(0);
});

it('marks excluded countries as inactive when seeding all countries', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    config()->set('world.insert_activations_only', false);
    config()->set('world.countries.activation.default', true);
    config()->set('world.countries.activation.except.iso2', ['SA']);
    config()->set('world.countries.activation.except.iso3', []);

    require_once database_path('seeders/WorldTableSeeder.php');

    $this->artisan('world:seeder')
        ->assertSuccessful();

    $saudiArabia = DB::table('countries')
        ->where('iso2', 'SA')
        ->first();

    expect($saudiArabia)->not->toBeNull()
        ->and((bool) $saudiArabia->is_active)->toBeFalse();
});

it('installs the package and seeds only configured countries', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    expect(Schema::hasTable('countries'))->toBeTrue()
        ->and(Schema::hasTable('states'))->toBeTrue()
        ->and(Schema::hasTable('cities'))->toBeTrue();

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', ['SA']);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    require_once database_path('seeders/WorldTableSeeder.php');

    $this->artisan('world:seeder')
        ->assertSuccessful();

    expect(DB::table('countries')->count())
        ->toBe(1)
        ->and(DB::table('countries')->value('iso2'))
        ->toBe('SA')
        ->and(DB::table('states')->where('country_id', 194)->count())
        ->toBeGreaterThan(0)
        ->and(DB::table('cities')->where('country_id', 194)->count())
        ->toBeGreaterThan(0);
});

it('seeds multiple configured countries by iso2 with their related data', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', ['SA', 'AE']);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    $this->artisan('world:seeder')
        ->assertSuccessful();

    $countries = DB::table('countries')->get();

    expect($countries->count())->toBe(2)
        ->and($countries->pluck('iso2')->all())->toContain('SA', 'AE');

    foreach ($countries as $country) {
        expect(
            DB::table('states')->where('country_id', $country->id)->count()
        )->toBeGreaterThan(0)
            ->and(
                DB::table('cities')->where('country_id', $country->id)->count()
            )->toBeGreaterThan(0);

    }
});

it('seeds multiple configured countries by iso3 with their related data', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', []);
    config()->set('world.countries.activation.only.iso3', ['SAU', 'ARE']);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    $this->artisan('world:seeder')
        ->assertSuccessful();

    $countries = DB::table('countries')->get();

    expect($countries->count())->toBe(2)
        ->and($countries->pluck('iso3')->all())->toContain('SAU', 'ARE');

    foreach ($countries as $country) {
        expect(
            DB::table('states')->where('country_id', $country->id)->count()
        )->toBeGreaterThan(0)
            ->and(
                DB::table('cities')->where('country_id', $country->id)->count()
            )->toBeGreaterThan(0);

    }
});

it('seeds multiple configured countries using both iso2 and iso3 with their related data', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', ['SA', 'AE']);
    config()->set('world.countries.activation.only.iso3', ['EGY', 'JOR']);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    $this->artisan('world:seeder')
        ->assertSuccessful();

    $countries = DB::table('countries')->get();

    expect($countries->count())->toBe(4)
        ->and($countries->pluck('iso2')->all())
        ->toContain('SA', 'AE', 'EG', 'JO')
        ->and($countries->pluck('iso3')->all())
        ->toContain('SAU', 'ARE', 'EGY', 'JOR');

    foreach ($countries as $country) {
        expect(
            DB::table('states')
                ->where('country_id', $country->id)
                ->count()
        )->toBeGreaterThan(0)
            ->and(
                DB::table('cities')
                    ->where('country_id', $country->id)
                    ->count()
            )->toBeGreaterThan(0);

    }
});

it('does not duplicate countries configured in both iso2 and iso3', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', ['SA']);
    config()->set('world.countries.activation.only.iso3', ['SAU', 'ARE']);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    $this->artisan('world:seeder')
        ->assertSuccessful();

    $countries = DB::table('countries')->get();

    expect($countries->count())->toBe(2)
        ->and($countries->pluck('iso2')->all())
        ->toContain('SA', 'AE')
        ->and($countries->pluck('iso3')->all())
        ->toContain('SAU', 'ARE');

    $saudiArabia = $countries->firstWhere('iso2', 'SA');
    $unitedArabEmirates = $countries->firstWhere('iso2', 'AE');

    expect(DB::table('countries')->where('iso2', 'SA')->count())
        ->toBe(1)
        ->and(DB::table('states')->where('country_id', $saudiArabia->id)->count())
        ->toBeGreaterThan(0)
        ->and(DB::table('cities')->where('country_id', $saudiArabia->id)->count())
        ->toBeGreaterThan(0)
        ->and(DB::table('states')->where('country_id', $unitedArabEmirates->id)->count())
        ->toBeGreaterThan(0)
        ->and(DB::table('cities')->where('country_id', $unitedArabEmirates->id)->count())
        ->toBeGreaterThan(0);
});

it('marks countries excluded by both iso2 and iso3 as inactive', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.insert_activations_only', false);
    config()->set('world.countries.activation.default', true);
    config()->set('world.countries.activation.except.iso2', ['SA']);
    config()->set('world.countries.activation.except.iso3', ['ARE']);

    $this->artisan('world:seeder')
        ->assertSuccessful();

    $saudiArabia = DB::table('countries')
        ->where('iso2', 'SA')
        ->first();

    $unitedArabEmirates = DB::table('countries')
        ->where('iso3', 'ARE')
        ->first();

    expect($saudiArabia)->not->toBeNull()
        ->and((bool) $saudiArabia->is_active)->toBeFalse()
        ->and($unitedArabEmirates)->not->toBeNull()
        ->and((bool) $unitedArabEmirates->is_active)->toBeFalse();
});

it('throws an exception when activation lists are empty and activation-only mode is enabled', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', []);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    expect(fn () => $this->artisan('world:seeder'))
        ->toThrow(
            IsoCodesIsEmptyException::class,
            "Iso Code Is Empty , please add ISO codes in config file 'config/world.php"
        );
});

it('throws an exception when an invalid iso2 code is configured', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.countries.activation.only.iso2', ['XX']);
    config()->set('world.countries.activation.only.iso3', []);

    expect(fn () => $this->artisan('world:seeder'))
        ->toThrow(
            InvalidCodeException::class,
            'Not Found Country has iso2 with this value XX.'
        );
});

it('throws an exception when an invalid iso3 code is configured', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.countries.activation.only.iso2', []);
    config()->set('world.countries.activation.only.iso3', ['XXX']);

    expect(fn () => $this->artisan('world:seeder'))
        ->toThrow(
            InvalidCodeException::class,
            'Not Found Country has iso3 with this value XXX.'
        );
});

it('propagates an exception when seeding with an invalid iso2 code', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    config()->set('world.countries.activation.only.iso2', ['XX']);
    config()->set('world.countries.activation.only.iso3', []);

    expect(fn () => $this->artisan('world:seeder'))
        ->toThrow(
            InvalidCodeException::class,
            'Not Found Country has iso2 with this value XX.'
        );
});

it('cancels refresh when confirmation is declined', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    require_once database_path('seeders/WorldTableSeeder.php');

    $this->artisan('world:seeder', ['--refresh' => true])
        ->expectsConfirmation(
            'Are you sure you want to delete all data in the Countries/States/Cities tables?',
            'no'
        )
        ->expectsOutputToContain('Seeding cancelled.')
        ->assertExitCode(Command::INVALID);

    expect(DB::table('countries')->count())->toBe(0)
        ->and(DB::table('states')->count())->toBe(0)
        ->and(DB::table('cities')->count())->toBe(0);
});

it('refreshes seeded data using the updated country activation settings', function () {
    $this->artisan('world:install')
        ->expectsConfirmation('Would you like to run the migrations now?', 'yes')
        ->expectsOutput('Running migrations...')
        ->expectsConfirmation('Would you like to star our repo on GitHub?', 'no')
        ->expectsOutput('world has been installed!')
        ->assertSuccessful();

    config()->set('world.insert_activations_only', true);
    config()->set('world.countries.activation.only.iso2', ['SA']);
    config()->set('world.countries.activation.only.iso3', []);
    config()->set('world.countries.activation.except.iso2', []);
    config()->set('world.countries.activation.except.iso3', []);

    require_once database_path('seeders/WorldTableSeeder.php');

    $this->artisan('world:seeder')
        ->assertSuccessful();

    expect(DB::table('countries')->count())
        ->toBe(1)
        ->and(DB::table('countries')->value('iso2'))
        ->toBe('SA');

    config()->set('world.countries.activation.only.iso2', ['AE']);

    $this->artisan('world:seeder', ['--refresh' => true])
        ->expectsConfirmation(
            'Are you sure you want to delete all data in the Countries/States/Cities tables?',
            'yes'
        )
        ->assertSuccessful();

    expect(DB::table('countries')->count())
        ->toBe(1)
        ->and(DB::table('countries')->value('iso2'))
        ->toBe('AE');
});
