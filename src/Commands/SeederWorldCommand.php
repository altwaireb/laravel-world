<?php

namespace Altwaireb\World\Commands;

use Altwaireb\World\World;
use Illuminate\Console\Command;

class SeederWorldCommand extends Command
{
    public $signature = 'world:seeder
        {--R|refresh : Reset and restart migrations for countries/states/cities in the table }
        {--F|force : Override if the file seeder already exists }
    ';

    public $description = 'Seeder All Countries/States/Cities Data';

    public function __construct(
        protected World $serves
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->serves->isSeedersPublished()) {
            $this->components->error('Please RUN `php artisan vendor:publish --tag=world-seeders` to publish seeder class');

            return self::INVALID;
        }

        if (! $this->serves->hasMigrationFileName(migrationFileName: 'create_world_table.php')) {
            $this->components->error('Please RUN `php artisan vendor:publish --tag=world-migrations` to publish migrations tables');

            return self::INVALID;
        }

        if ($this->option('force')) {
            $this->call('vendor:publish', [
                '--tag' => 'world-seeders',
                '--force' => true,
            ]);
        }

        if ($this->option('refresh')) {
            if ($this->confirm('Are you sure you want to delete all data in the Countries/States/Cities tables?')) {
                $this->callSilently('migrate:refresh', [
                    '--path' => 'database/migrations/'.$this->serves->getMigrationFileName(migrationFileName: 'create_world_table.php'),
                ]);
            } else {
                $this->components->warn('counsel command.');

                return self::INVALID;
            }
        } else {
            if (! $this->serves->isAllTablesEmpty()) {
                if (! $this->serves->isCountriesTableEmpty()) {
                    $this->components->error("You can't Seeding in countries table because table has data.");

                    return self::INVALID;
                }

                if (! $this->serves->isStatesTableEmpty()) {
                    $this->components->error("You can't Seeding in states table because table has data.");

                    return self::INVALID;
                }

                if (! $this->serves->isCitiesTableEmpty()) {
                    $this->components->error("You can't Seeding in cities table because table has data.");

                    return self::INVALID;
                }

                $this->components->warn('You can run `php artisan world:seeder --refresh` this command delete tables countries/states/cities and re-seeding data.');
            }
        }

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\WorldTableSeeder',
        ]);

        return self::SUCCESS;
    }
}
