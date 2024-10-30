<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateAll extends Command
{
    protected $signature = 'migrate:all';
    protected $description = 'Run migrations from all folders';

    public function handle()
    {
        $paths = [
            'database/migrations', // Główny katalog migracji
            'database/migrations/courier_announcement',
            'database/migrations/others',
            'database/migrations/user_announcement',
        ];

        foreach ($paths as $path) {
            $output = Artisan::call('migrate', ['--path' => $path, '--force' => true, '--verbose' => true]);
            $this->info('Migrated: ' . $path);
            $this->line(Artisan::output()); // Wypisz wynik migracji
        }
    }
}
