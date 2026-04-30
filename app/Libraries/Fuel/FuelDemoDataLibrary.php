<?php

namespace App\Libraries\Fuel;

use App\Models\GasStation;
use App\Models\Price;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FuelDemoDataLibrary
{
    public function seed(bool $refresh = false): array
    {
        if ($refresh) {
            Price::query()->delete();
            GasStation::query()->delete();
        }

        if (Price::query()->exists()) {
            return [
                'stations' => GasStation::query()->count(),
                'snapshots' => Price::query()->count(),
                'seeded' => false,
            ];
        }

        $stations = [
            ['station_code' => 'MAD-001', 'province' => 'MADRID', 'municipality' => 'MADRID', 'locality' => 'Chamartín', 'postal_code' => '28036', 'address' => 'Paseo de la Castellana, 210', 'margin' => 'D', 'longitude' => -3.68831, 'latitude' => 40.45941, 'brand' => 'REPSOL', 'sale_type' => 'P', 'provider' => 'OM', 'hours' => 'L-D: 06:00-23:00', 'service_type' => 'P', 'municipality_code' => '079', 'province_code' => '28', 'autonomous_community_code' => '13'],
            ['station_code' => 'MAD-002', 'province' => 'MADRID', 'municipality' => 'ALCORCÓN', 'locality' => 'Centro', 'postal_code' => '28921', 'address' => 'Avenida de Móstoles, 12', 'margin' => 'I', 'longitude' => -3.83220, 'latitude' => 40.34682, 'brand' => 'BALLENOIL', 'sale_type' => 'P', 'provider' => 'DM', 'hours' => '24H', 'service_type' => 'D', 'municipality_code' => '007', 'province_code' => '28', 'autonomous_community_code' => '13'],
            ['station_code' => 'BCN-001', 'province' => 'BARCELONA', 'municipality' => 'BARCELONA', 'locality' => 'Sants', 'postal_code' => '08014', 'address' => 'Carrer de Sants, 301', 'margin' => 'D', 'longitude' => 2.14192, 'latitude' => 41.37652, 'brand' => 'CEPSA', 'sale_type' => 'P', 'provider' => 'OM', 'hours' => 'L-D: 07:00-22:00', 'service_type' => 'A', 'municipality_code' => '019', 'province_code' => '08', 'autonomous_community_code' => '09'],
            ['station_code' => 'VAL-001', 'province' => 'VALENCIA', 'municipality' => 'VALÈNCIA', 'locality' => 'Campanar', 'postal_code' => '46015', 'address' => 'Avenida de Pío XII, 44', 'margin' => 'D', 'longitude' => -0.39813, 'latitude' => 39.48555, 'brand' => 'BP', 'sale_type' => 'P', 'provider' => 'OM', 'hours' => 'L-D: 06:30-22:30', 'service_type' => 'P', 'municipality_code' => '250', 'province_code' => '46', 'autonomous_community_code' => '10'],
            ['station_code' => 'SEV-001', 'province' => 'SEVILLA', 'municipality' => 'SEVILLA', 'locality' => 'Triana', 'postal_code' => '41010', 'address' => 'Ronda de Triana, 20', 'margin' => 'I', 'longitude' => -6.00675, 'latitude' => 37.38112, 'brand' => 'PLENOIL', 'sale_type' => 'P', 'provider' => 'DM', 'hours' => '24H', 'service_type' => 'D', 'municipality_code' => '091', 'province_code' => '41', 'autonomous_community_code' => '01'],
            ['station_code' => 'ZAZ-001', 'province' => 'ZARAGOZA', 'municipality' => 'ZARAGOZA', 'locality' => 'Delicias', 'postal_code' => '50010', 'address' => 'Avenida de Navarra, 145', 'margin' => 'D', 'longitude' => -0.91923, 'latitude' => 41.64872, 'brand' => 'SHELL', 'sale_type' => 'P', 'provider' => 'OM', 'hours' => 'L-D: 07:00-23:00', 'service_type' => 'A', 'municipality_code' => '297', 'province_code' => '50', 'autonomous_community_code' => '02'],
            ['station_code' => 'VLL-001', 'province' => 'VALLADOLID', 'municipality' => 'VALLADOLID', 'locality' => 'Parquesol', 'postal_code' => '47014', 'address' => 'Calle Manuel Azaña, 58', 'margin' => 'D', 'longitude' => -4.77174, 'latitude' => 41.64017, 'brand' => 'GALP', 'sale_type' => 'P', 'provider' => 'OM', 'hours' => 'L-D: 07:00-22:00', 'service_type' => 'P', 'municipality_code' => '186', 'province_code' => '47', 'autonomous_community_code' => '08'],
            ['station_code' => 'MAL-001', 'province' => 'MÁLAGA', 'municipality' => 'MÁLAGA', 'locality' => 'Carretera de Cádiz', 'postal_code' => '29004', 'address' => 'Avenida de Velázquez, 212', 'margin' => 'I', 'longitude' => -4.45691, 'latitude' => 36.69144, 'brand' => 'PETROPRIX', 'sale_type' => 'P', 'provider' => 'DM', 'hours' => '24H', 'service_type' => 'D', 'municipality_code' => '067', 'province_code' => '29', 'autonomous_community_code' => '01'],
        ];

        $baseFuelPrices = [
            'diesel_a' => 1.534,
            'diesel_premium' => 1.629,
            'diesel_b' => 1.164,
            'gas95_e5' => 1.612,
            'gas95_e10' => 1.605,
            'gas95_e5_premium' => 1.658,
            'gas98_e5' => 1.744,
            'gas98_e10' => 1.735,
            'glp' => 0.964,
            'gnc' => 1.221,
            'gnl' => 1.176,
            'adblue' => 0.731,
        ];

        $trendPattern = [-0.028, -0.024, -0.020, -0.015, -0.010, -0.005, 0.000];
        $latestMoment = Carbon::now(config('app.timezone'))->startOfDay()->setHour(7);
        $createdStations = 0;
        $createdPrices = 0;

        DB::transaction(function () use ($stations, $baseFuelPrices, $trendPattern, $latestMoment, &$createdStations, &$createdPrices): void {
            foreach ($stations as $stationIndex => $stationAttributes) {
                $station = GasStation::query()->updateOrCreate(
                    ['station_code' => $stationAttributes['station_code']],
                    $stationAttributes,
                );

                $createdStations++;

                for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
                    $seriesIndex = 6 - $daysAgo;
                    $trend = $trendPattern[$seriesIndex];
                    $stationOffset = (($stationIndex % 4) - 1.5) * 0.012;
                    $brandOffset = str_contains($stationAttributes['brand'], 'OIL') ? -0.018 : 0.006;
                    $collectedAt = $latestMoment->copy()->subDays($daysAgo);

                    $payload = [];

                    foreach ($baseFuelPrices as $fuel => $basePrice) {
                        $volatility = (($daysAgo % 2 === 0) ? 0.002 : -0.001) + (($stationIndex % 3) * 0.001);
                        $payload[$fuel] = round($basePrice + $trend + $stationOffset + $brandOffset + $volatility, 3);
                    }

                    $payload['bioethanol'] = round(1.324 + $trend + ($stationOffset / 2), 3);
                    $payload['biodiesel'] = round(1.412 + $trend + ($stationOffset / 2), 3);
                    $payload['pct_bio'] = 7.0;
                    $payload['pct_ester'] = 7.0;
                    $payload['renewable_diesel'] = round(1.688 + $trend + $brandOffset, 3);
                    $payload['renewable_gasoline'] = round(1.701 + $trend + $brandOffset, 3);
                    $payload['biogas_cng'] = round(1.174 + $trend + ($brandOffset / 3), 3);
                    $payload['biogas_lng'] = round(1.121 + $trend + ($brandOffset / 3), 3);
                    $payload['hydrogen'] = round(8.955 + ($stationIndex * 0.03), 3);
                    $payload['ammonia'] = null;
                    $payload['methanol'] = null;

                    Price::query()->updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'collected_at' => $collectedAt,
                        ],
                        $payload,
                    );

                    $createdPrices++;
                }
            }
        });

        return [
            'stations' => $createdStations,
            'snapshots' => $createdPrices,
            'seeded' => true,
        ];
    }
}
