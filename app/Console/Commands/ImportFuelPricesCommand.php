<?php

namespace App\Console\Commands;

use App\Libraries\Fuel\FuelImportLibrary;
use Illuminate\Console\Command;
use Throwable;

class ImportFuelPricesCommand extends Command
{
    protected $signature = 'fuel:import {--url= : URL alternativa para el endpoint REST oficial} {--source=rest : Solo se admite rest}';

    protected $description = 'Descarga e importa el fichero oficial de precios de carburantes';

    public function handle(FuelImportLibrary $fuelImportLibrary): int
    {
        $this->components->info('Iniciando importación oficial de carburantes...');

        if ((string) $this->option('source') !== 'rest') {
            $this->components->error('La importación oficial solo admite la fuente REST JSON. Usa --source=rest o no indiques la opción.');

            return self::FAILURE;
        }

        try {
            $summary = $fuelImportLibrary->importFromOfficialSource($this->option('url') ?: null);
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
