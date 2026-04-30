<?php

namespace App\Console\Commands;

use App\Libraries\Fuel\FuelDemoDataLibrary;
use Illuminate\Console\Command;

class SeedFuelDemoDataCommand extends Command
{
    protected $signature = 'fuel:seed-demo {--refresh : Reemplaza los datos existentes por el dataset de demostración}';

    protected $description = 'Carga datos demo para visualizar el portal sin necesidad de importar el XLS oficial';

    public function handle(FuelDemoDataLibrary $fuelDemoDataLibrary): int
    {
        $summary = $fuelDemoDataLibrary->seed((bool) $this->option('refresh'));

        foreach ($summary as $label => $value) {
            $this->components->twoColumnDetail((string) $label, (string) $value);
        }

        $this->components->info('Datos demo preparados.');

        return self::SUCCESS;
    }
}
