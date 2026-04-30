<?php

namespace App\Console\Commands;

use App\Libraries\Fuel\BrandLogoLibrary;
use App\Models\GasStation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuditBrandLogosCommand extends Command
{
    protected $signature = 'fuel:brand-logos:audit {--write-manifest : Guarda un JSON con las marcas pendientes de recopilar}';

    protected $description = 'Lista las marcas detectadas y comprueba qué logos locales faltan por recopilar';

    public function handle(BrandLogoLibrary $brandLogos): int
    {
        $report = $brandLogos->buildCollectionReport(
            GasStation::query()
                ->whereNotNull('brand')
                ->distinct()
                ->orderBy('brand')
                ->pluck('brand')
        );

        $this->info('Resumen de logos de marca');
        $this->newLine();
        $this->table(
            ['Marca', 'Slug', 'Disponible', 'Ruta esperada'],
            $report->map(fn (array $item) => [
                $item['brand'],
                $item['slug'],
                $item['has_logo'] ? 'Sí' : 'No',
                $item['expected_files'][0] ?? '-',
            ])->all(),
        );

        $missing = $report->where('has_logo', false)->values();

        $this->newLine();
        $this->components->twoColumnDetail('Marcas totales', (string) $report->count());
        $this->components->twoColumnDetail('Pendientes de recopilar', (string) $missing->count());

        if ($this->option('write-manifest')) {
            $directory = storage_path('app/brand-logos');
            File::ensureDirectoryExists($directory);
            File::put(
                $directory.DIRECTORY_SEPARATOR.'pending-brands.json',
                $missing->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            );

            $this->components->info('Manifest escrito en storage/app/brand-logos/pending-brands.json');
        }

        return self::SUCCESS;
    }
}
