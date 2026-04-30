<?php

namespace App\Console\Commands;

use App\Libraries\Fuel\FuelImportLibrary;
use Illuminate\Console\Command;
use Throwable;

class ImportFuelPricesCommand extends Command
{
    protected $signature = 'fuel:import {--url= : URL alternativa para el fichero oficial} {--source=auto : auto, xls o rest}';

    protected $description = 'Descarga e importa el fichero oficial de precios de carburantes';

    public function handle(FuelImportLibrary $fuelImportLibrary): int
    {
        $this->components->info('Iniciando importación oficial de carburantes...');

        try {
            $summary = $fuelImportLibrary->importFromOfficialSource(
                $this->option('url') ?: null,
                (string) $this->option('source'),
            );
        } catch (Throwable $throwable) {
            $this->components->error($throwable->getMessage());

            return self::FAILURE;
        }

        foreach ($summary as $label => $value) {
            $this->components->twoColumnDetail((string) $label, is_scalar($value) || $value === null ? (string) $value : json_encode($value));
        }

        $this->components->info('Importación completada.');

        return self::SUCCESS;
    }
}
