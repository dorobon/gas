<?php

use App\Console\Commands\ImportFuelPricesCommand;
use App\Console\Commands\SeedFuelDemoDataCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ImportFuelPricesCommand::class)->dailyAt('07:00');

Artisan::command('fuel:about', function () {
    $this->components->twoColumnDetail('Fuente oficial', config('fuel.rest_source_url'));
    $this->components->twoColumnDetail('Modo importación', 'REST JSON');
    $this->components->twoColumnDetail('Combustible por defecto', config('fuel.default_fuel'));
    $this->components->twoColumnDetail('Programación', 'Diaria a las 07:00');
})->purpose('Muestra un resumen operativo del portal de carburantes');
