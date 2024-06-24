<?php

namespace Altwaireb\World\Commands;

use Illuminate\Console\Command;

class InstallWorldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install altwaireb/laravel-world package';

    protected ?string $starRepo = 'altwaireb/laravel-world';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $this->callSilently('vendor:publish', [
            '--tag' => 'world-config',
        ]);

        $this->callSilently('vendor:publish', [
            '--tag' => 'world-migrations',
        ]);

        $this->callSilently('vendor:publish', [
            '--tag' => 'world-models',
        ]);

        $this->callSilently('vendor:publish', [
            '--tag' => 'world-seeders',
        ]);

        if ($this->confirm('Would you like to run the migrations now?')) {
            $this->comment('Running migrations...');

            $this->call('migrate');
        }

        if ($this->confirm('Would you like to star our repo on GitHub?')) {
            $repoUrl = "https://github.com/{$this->starRepo}";

            if (PHP_OS_FAMILY == 'Darwin') {
                exec("open {$repoUrl}");
            }
            if (PHP_OS_FAMILY == 'Windows') {
                exec("start {$repoUrl}");
            }
            if (PHP_OS_FAMILY == 'Linux') {
                exec("xdg-open {$repoUrl}");
            }
        }

        $this->info('world has been installed!');

    }
}
