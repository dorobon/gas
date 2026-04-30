<?php

namespace App\Libraries\Fuel;

use App\Models\GasStation;
use App\Models\Price;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use RuntimeException;

class FuelImportLibrary
{
    public function importFromOfficialSource(?string $sourceUrl = null, string $strategy = 'auto'): array
    {
        $strategy = in_array($strategy, ['auto', 'xls', 'rest'], true)
            ? $strategy
            : (string) config('fuel.import_strategy', 'auto');

        if ($strategy === 'rest') {
            return $this->importFromRestSource($sourceUrl ?: (string) config('fuel.rest_source_url'));
        }

        try {
            return $this->importFromXlsSource($sourceUrl ?: (string) config('fuel.source_url'));
        } catch (\Throwable $throwable) {
            if ($strategy === 'xls') {
                throw $throwable;
            }

            report($throwable);

            return $this->importFromRestSource(
                (string) config('fuel.rest_source_url'),
                $throwable,
            );
        }
    }

    public function importFromXlsSource(string $url): array
    {
        $response = Http::timeout(180)
            ->retry(2, 1200)
            ->withUserAgent(sprintf('%s fuel-importer/1.0', config('app.name')))
            // En entornos locales Windows sin cacert instalado puede fallar la verificación SSL.
            // forzar sin verificación para permitir la importación localmente (no recomendable en producción).
            ->withoutVerifying()
            ->get($url);

        $response->throw();

        $directory = storage_path('app/private/imports');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory.'/preciosEESS_'.now()->format('Ymd_His').'.xls';
        file_put_contents($filePath, $response->body());

        $summary = $this->importFromFile($filePath, $url);
        $summary['source_type'] = 'xls';
        $summary['fallback_used'] = false;

        return $summary;
    }

    public function importFromRestSource(string $url, ?\Throwable $fallbackReason = null): array
    {
        $response = Http::timeout(180)
            ->retry(2, 1200)
            ->acceptJson()
            ->withUserAgent(sprintf('%s fuel-importer/1.0', config('app.name')))
            // Ignorar verificación SSL en entornos locales para evitar errores de cURL por CA faltante.
            ->withoutVerifying()
            ->get($url);

        $response->throw();

        $payload = $response->json();

        if (! is_array($payload) || ! isset($payload['ListaEESSPrecio']) || ! is_array($payload['ListaEESSPrecio'])) {
            throw new RuntimeException('La respuesta REST oficial no tiene el formato esperado.');
        }

        $processed = 0;
        $createdStations = 0;
        $updatedStations = 0;
        $createdPrices = 0;
        $latestCollectedAt = null;
        $defaultCollectedAt = $this->normalizeCollectedAt(is_string($payload['Fecha'] ?? null) ? $payload['Fecha'] : null);

        DB::transaction(function () use ($payload, $defaultCollectedAt, &$processed, &$createdStations, &$updatedStations, &$createdPrices, &$latestCollectedAt): void {
            foreach ($payload['ListaEESSPrecio'] as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $mappedRow = $this->normalizeJsonRow($row, $defaultCollectedAt);

                if ($this->rowIsEmpty($mappedRow)) {
                    continue;
                }

                $this->persistMappedRow(
                    $mappedRow,
                    $processed,
                    $createdStations,
                    $updatedStations,
                    $createdPrices,
                    $latestCollectedAt,
                );
            }
        });

        return [
            'processed_rows' => $processed,
            'created_stations' => $createdStations,
            'updated_stations' => $updatedStations,
            'stored_snapshots' => $createdPrices,
            'latest_collected_at' => $latestCollectedAt?->toIso8601String(),
            'source' => $url,
            'source_type' => 'rest',
            'fallback_used' => $fallbackReason !== null,
            'fallback_reason' => $fallbackReason?->getMessage(),
        ];
    }

    public function importFromFile(string $filePath, ?string $source = null): array
    {
        try {
            $reader = IOFactory::createReader('Xls');
        } catch (ReaderException $exception) {
            throw new RuntimeException('No se pudo preparar el lector XLS.', previous: $exception);
        }

        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }

        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }

        try {
            $spreadsheet = $reader->load($filePath);
        } catch (ReaderException $exception) {
            throw new RuntimeException('El fichero XLS oficial no se ha podido leer.', previous: $exception);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        [$headerIndex, $headerRow] = $this->locateHeaderRow($rows);
        $mappedColumns = $this->mapColumns($headerRow);

        if (! isset($mappedColumns['province'], $mappedColumns['municipality'], $mappedColumns['address'])) {
            throw new RuntimeException('No se reconocieron las columnas mínimas del fichero oficial.');
        }

        $processed = 0;
        $createdStations = 0;
        $updatedStations = 0;
        $createdPrices = 0;
        $latestCollectedAt = null;

        DB::transaction(function () use ($rows, $headerIndex, $mappedColumns, &$processed, &$createdStations, &$updatedStations, &$createdPrices, &$latestCollectedAt): void {
            foreach ($rows as $rowNumber => $row) {
                if ($rowNumber <= $headerIndex) {
                    continue;
                }

                $mappedRow = $this->extractMappedRow($row, $mappedColumns);

                if ($this->rowIsEmpty($mappedRow)) {
                    continue;
                }

                $this->persistMappedRow(
                    $mappedRow,
                    $processed,
                    $createdStations,
                    $updatedStations,
                    $createdPrices,
                    $latestCollectedAt,
                );
            }
        });

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [
            'processed_rows' => $processed,
            'created_stations' => $createdStations,
            'updated_stations' => $updatedStations,
            'stored_snapshots' => $createdPrices,
            'latest_collected_at' => $latestCollectedAt?->toIso8601String(),
            'source' => $source,
            'source_type' => 'xls-file',
            'fallback_used' => false,
        ];
    }

    private function locateHeaderRow(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $normalizedValues = array_map(fn ($value) => $this->normalizeHeader((string) $value), $row);

            if (in_array('provincia', $normalizedValues, true) && in_array('municipio', $normalizedValues, true)) {
                return [$index, $row];
            }
        }

        return [1, $rows[1] ?? []];
    }

    private function mapColumns(array $headerRow): array
    {
        $aliases = $this->columnAliases();

        $mappedColumns = [];

        foreach ($headerRow as $column => $heading) {
            $normalizedHeading = $this->normalizeHeader((string) $heading);

            foreach ($aliases as $target => $candidates) {
                if (in_array($normalizedHeading, $candidates, true)) {
                    $mappedColumns[$target] = $column;
                }
            }
        }

        return $mappedColumns;
    }

    private function columnAliases(): array
    {
        return [
            'station_code' => ['ideess'],
            'province' => ['provincia'],
            'municipality' => ['municipio'],
            'locality' => ['localidad'],
            'postal_code' => ['c_p', 'cp', 'codigo_postal'],
            'address' => ['direccion'],
            'margin' => ['margen'],
            'longitude' => ['longitud_wgs84', 'longitud_x0020_x0028_wgs84_x0029'],
            'latitude' => ['latitud'],
            'brand' => ['rotulo'],
            'sale_type' => ['tipo_venta'],
            'provider' => ['remision'],
            'hours' => ['horario'],
            'service_type' => ['tipo_servicio'],
            'municipality_code' => ['idmunicipio'],
            'province_code' => ['idprovincia'],
            'autonomous_community_code' => ['idccaa'],
            'collected_at' => ['toma_de_datos', 'fecha'],
            'gas95_e5' => ['precio_gasolina_95_e5'],
            'gas95_e10' => ['precio_gasolina_95_e10'],
            'gas95_e25' => ['precio_gasolina_95_e25'],
            'gas95_e5_premium' => ['precio_gasolina_95_e5_premium'],
            'gas95_e85' => ['precio_gasolina_95_e85'],
            'gas98_e5' => ['precio_gasolina_98_e5'],
            'gas98_e10' => ['precio_gasolina_98_e10'],
            'renewable_gasoline' => ['precio_gasolina_renovable'],
            'diesel_a' => ['precio_gasoleo_a'],
            'diesel_b' => ['precio_gasoleo_b'],
            'diesel_premium' => ['precio_gasoleo_premium'],
            'renewable_diesel' => ['precio_diesel_renovable'],
            'bioethanol' => ['precio_bioetanol'],
            'pct_bio' => ['pct_bioetanol', 'bioetanol', 'x0025_x0020_bioetanol'],
            'biodiesel' => ['precio_biodiesel'],
            'pct_ester' => ['pct_ester_metilico', 'x0025_x0020_ester_metilico'],
            'glp' => ['precio_gases_licuados_del_petroleo'],
            'gnc' => ['precio_gas_natural_comprimido'],
            'gnl' => ['precio_gas_natural_licuado'],
            'biogas_cng' => ['precio_biogas_natural_comprimido'],
            'biogas_lng' => ['precio_biogas_natural_licuado'],
            'hydrogen' => ['precio_hidrogeno'],
            'adblue' => ['precio_adblue'],
            'ammonia' => ['precio_amoniaco'],
            'methanol' => ['precio_metanol'],
        ];
    }

    private function extractMappedRow(array $row, array $mappedColumns): array
    {
        $payload = [];

        foreach ($mappedColumns as $target => $column) {
            $payload[$target] = isset($row[$column]) ? trim((string) $row[$column]) : null;
        }

        return $payload;
    }

    private function extractFuelPayload(array $row): array
    {
        $payload = [];

        foreach (array_keys(config('fuel.fuels')) as $fuelKey) {
            $payload[$fuelKey] = array_key_exists($fuelKey, $row)
                ? $this->normalizeDecimal($row[$fuelKey])
                : null;
        }

        return $payload;
    }

    private function normalizeJsonRow(array $row, Carbon $defaultCollectedAt): array
    {
        $mappedRow = [
            'collected_at' => $defaultCollectedAt->format('Y-m-d H:i:s'),
        ];

        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeHeader((string) $key);

            foreach ($this->columnAliases() as $target => $aliases) {
                if (in_array($normalizedKey, $aliases, true)) {
                    $mappedRow[$target] = is_scalar($value) || $value === null
                        ? trim((string) $value)
                        : null;
                }
            }
        }

        return $mappedRow;
    }

    private function persistMappedRow(
        array $mappedRow,
        int &$processed,
        int &$createdStations,
        int &$updatedStations,
        int &$createdPrices,
        Carbon|string|null &$latestCollectedAt,
    ): void {
        $stationCode = $mappedRow['station_code'] ?? null;
        $stationCode = $stationCode ?: sha1(implode('|', [
            $mappedRow['province'] ?? null,
            $mappedRow['municipality'] ?? null,
            $mappedRow['address'] ?? null,
            $mappedRow['postal_code'] ?? null,
        ]));

        $station = GasStation::query()->firstOrNew(['station_code' => $stationCode]);
        $wasRecentlyCreated = ! $station->exists;

        $station->fill([
            'province' => $mappedRow['province'] ?? null,
            'municipality' => $mappedRow['municipality'] ?? null,
            'locality' => $mappedRow['locality'] ?? null,
            'postal_code' => $mappedRow['postal_code'] ?? null,
            'address' => $mappedRow['address'] ?? null,
            'margin' => $mappedRow['margin'] ?? null,
            'longitude' => $this->normalizeDecimal($mappedRow['longitude'] ?? null),
            'latitude' => $this->normalizeDecimal($mappedRow['latitude'] ?? null),
            'brand' => $mappedRow['brand'] ?? null,
            'sale_type' => $mappedRow['sale_type'] ?? null,
            'provider' => $mappedRow['provider'] ?? null,
            'hours' => $mappedRow['hours'] ?? null,
            'service_type' => $mappedRow['service_type'] ?? null,
            'municipality_code' => $mappedRow['municipality_code'] ?? null,
            'province_code' => $mappedRow['province_code'] ?? null,
            'autonomous_community_code' => $mappedRow['autonomous_community_code'] ?? null,
        ]);
        $station->save();

        $wasRecentlyCreated ? $createdStations++ : $updatedStations++;

        $collectedAt = $this->normalizeCollectedAt($mappedRow['collected_at'] ?? null);
        $latestCollectedAt = $latestCollectedAt ? max($latestCollectedAt, $collectedAt) : $collectedAt;

        Price::query()->updateOrCreate(
            [
                'station_id' => $station->id,
                'collected_at' => $collectedAt,
            ],
            $this->extractFuelPayload($mappedRow),
        );

        $createdPrices++;
        $processed++;
    }

    private function rowIsEmpty(array $row): bool
    {
        return blank($row['province'] ?? null)
            && blank($row['municipality'] ?? null)
            && blank($row['address'] ?? null)
            && blank($row['brand'] ?? null);
    }

    private function normalizeHeader(string $value): string
    {
        return (string) Str::of($value)
            ->replaceMatches('/_x0025_/i', ' pct ')
            ->replaceMatches('/_x[0-9a-f]{4}_/i', ' ')
            ->ascii()
            ->replace('%', ' pct ')
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');
    }

    private function normalizeDecimal(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $sanitized = trim($value);

        if ($sanitized === '' || $sanitized === '-') {
            return null;
        }

        $sanitized = str_replace(['€', ' '], '', $sanitized);

        if (str_contains($sanitized, ',') && str_contains($sanitized, '.')) {
            $sanitized = str_replace('.', '', $sanitized);
        }

        $sanitized = str_replace(',', '.', $sanitized);

        return is_numeric($sanitized) ? round((float) $sanitized, 3) : null;
    }

    private function normalizeCollectedAt(?string $value): Carbon
    {
        if (blank($value)) {
            return Carbon::now(config('app.timezone'))->startOfHour();
        }

        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value, config('app.timezone'));
            } catch (InvalidFormatException) {
                continue;
            }
        }

        return Carbon::parse($value, config('app.timezone'));
    }
}
