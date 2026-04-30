<?php

namespace App\Libraries\Fuel;

use App\Models\Price;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FuelDataBootstrapLibrary
{
    public function __construct(
        private readonly FuelDemoDataLibrary $demoDataLibrary,
    ) {
    }

    public function ensureReady(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $databasePath = config('database.connections.sqlite.database');

        if (is_string($databasePath) && $databasePath !== ':memory:') {
            $directory = dirname($databasePath);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (! file_exists($databasePath)) {
                touch($databasePath);
            }
        }

        DB::connection()->getPdo();

        if (! Schema::hasTable('stations') || ! Schema::hasTable('prices')) {
            $schema = file_get_contents(database_path('sql/sqlite/fuel_portal_schema.sql'));
            DB::unprepared($schema ?: '');
        }

        if (app()->environment(['local', 'testing']) && ! Price::query()->exists()) {
            $this->demoDataLibrary->seed();
        }
    }
}
