<?php

namespace App\Repositories\Eloquent;

use App\Models\Price;
use App\Repositories\Contracts\PriceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PriceRepository implements PriceRepositoryInterface
{
    public function getLatestCollectionMoment(): ?Carbon
    {
        $latest = Price::query()->max('collected_at');

        return $latest ? Carbon::parse($latest) : null;
    }

    public function getDashboardSnapshot(): array
    {
        $latest = $this->getLatestCollectionMoment();
        $previous = $this->getPreviousCollectionMoment($latest);
        $featuredFuels = config('fuel.featured_fuels', []);
        $averages = [];

        foreach ($featuredFuels as $fuelKey => $meta) {
            $currentAverage = $latest
                ? (float) Price::query()->where('collected_at', $latest)->whereNotNull($fuelKey)->avg($fuelKey)
                : null;

            $previousAverage = $previous
                ? (float) Price::query()->where('collected_at', $previous)->whereNotNull($fuelKey)->avg($fuelKey)
                : null;

            $averages[] = [
                'key' => $fuelKey,
                'label' => $meta['label'],
                'short' => $meta['short'],
                'current' => $currentAverage ?: null,
                'previous' => $previousAverage ?: null,
                'trend' => ($currentAverage !== null && $previousAverage !== null)
                    ? round($currentAverage - $previousAverage, 3)
                    : null,
            ];
        }

        $history = $this->buildDashboardHistory(array_keys($featuredFuels));
        $cheapestHighlights = $latest
            ? Price::query()
                ->with('station')
                ->where('collected_at', $latest)
                ->whereNotNull('diesel_a')
                ->orderBy('diesel_a')
                ->limit(5)
                ->get()
            : collect();

        return [
            'latest_at' => $latest,
            'previous_at' => $previous,
            'averages' => $averages,
            'history' => $history,
            'legal_notice' => config('fuel.legal_notice'),
            'cheapest_highlights' => $cheapestHighlights,
        ];
    }

    public function getCheapest(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $fuelKey = $this->resolveFuelKey($filters['fuel'] ?? null);
        $latest = $this->getLatestCollectionMoment();

        return Price::query()
            ->with('station')
            ->where('collected_at', $latest)
            ->whereNotNull($fuelKey)
            ->when($filters['province'] ?? null, fn (Builder $query, string $province) => $query->whereHas('station', fn (Builder $stationQuery) => $stationQuery->where('province', $province)))
            ->when($filters['municipality'] ?? null, fn (Builder $query, string $municipality) => $query->whereHas('station', fn (Builder $stationQuery) => $stationQuery->where('municipality', $municipality)))
            ->when($filters['brand'] ?? null, fn (Builder $query, string $brand) => $query->whereHas('station', fn (Builder $stationQuery) => $stationQuery->where('brand', $brand)))
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('station', function (Builder $stationQuery) use ($search): void {
                    $stationQuery
                        ->where('brand', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhere('municipality', 'like', '%'.$search.'%');
                });
            })
            ->orderBy($fuelKey)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getHistoricSeries(array $filters = []): array
    {
        $fuelKey = $this->resolveFuelKey($filters['fuel'] ?? null);
        $days = $this->resolveHistoryDays($filters['days'] ?? null);
        $startDate = now(config('app.timezone'))->subDays($days - 1)->startOfDay();

        $query = Price::query()
            ->whereNotNull($fuelKey)
            ->where('collected_at', '>=', $startDate);

        if (! empty($filters['province'])) {
            $query->whereHas('station', fn (Builder $stationQuery) => $stationQuery->where('province', $filters['province']));
        }

        $rows = $query
            ->selectRaw('date(collected_at) as day, AVG('.$fuelKey.') as average_price, MIN('.$fuelKey.') as min_price, MAX('.$fuelKey.') as max_price')
            ->groupBy(DB::raw('date(collected_at)'))
            ->orderBy(DB::raw('date(collected_at)'))
            ->get();

        $points = $rows->map(fn ($row) => [
            'day' => $row->day,
            'label' => Carbon::parse($row->day)->translatedFormat('d M'),
            'average' => round((float) $row->average_price, 3),
            'min' => round((float) $row->min_price, 3),
            'max' => round((float) $row->max_price, 3),
        ])->values();

        $first = $points->first();
        $last = $points->last();

        return [
            'fuel' => $fuelKey,
            'fuel_label' => config('fuel.fuels.'.$fuelKey.'.label', $fuelKey),
            'days' => $days,
            'province' => $filters['province'] ?? null,
            'points' => $points,
            'summary' => [
                'average' => $points->avg('average'),
                'min' => $points->min('min'),
                'max' => $points->max('max'),
                'variation' => ($first && $last) ? round($last['average'] - $first['average'], 3) : null,
            ],
        ];
    }

    public function getStationHistory(int $stationId, ?string $fuel = null, int $days = 14): array
    {
        $fuelKey = $this->resolveFuelKey($fuel);

        $rows = Price::query()
            ->where('station_id', $stationId)
            ->whereNotNull($fuelKey)
            ->latest('collected_at')
            ->limit($days)
            ->get()
            ->reverse()
            ->values();

        $points = $rows->map(fn (Price $price) => [
            'moment' => $price->collected_at,
            'label' => $price->collected_at?->translatedFormat('d M H:i'),
            'value' => $price->{$fuelKey},
        ]);

        return [
            'fuel' => $fuelKey,
            'fuel_label' => config('fuel.fuels.'.$fuelKey.'.label', $fuelKey),
            'points' => $points,
            'latest' => $points->last(),
        ];
    }

    private function getPreviousCollectionMoment(?Carbon $latest): ?Carbon
    {
        if (! $latest) {
            return null;
        }

        $previous = Price::query()
            ->where('collected_at', '<', $latest)
            ->max('collected_at');

        return $previous ? Carbon::parse($previous) : null;
    }

    private function resolveFuelKey(?string $fuel): string
    {
        $availableFuels = array_keys(config('fuel.fuels', []));

        return in_array($fuel, $availableFuels, true)
            ? $fuel
            : (string) config('fuel.default_fuel');
    }

    private function resolveHistoryDays(mixed $days): int
    {
        $allowed = config('fuel.history_days', [7, 14, 30, 90]);
        $parsed = (int) $days;

        return in_array($parsed, $allowed, true) ? $parsed : 30;
    }

    private function buildDashboardHistory(array $featuredFuels): Collection
    {
        if ($featuredFuels === []) {
            return collect();
        }

        $selects = collect($featuredFuels)
            ->map(fn (string $fuelKey) => 'AVG('.$fuelKey.') as '.$fuelKey)
            ->implode(', ');

        return Price::query()
            ->selectRaw('date(collected_at) as day, '.$selects)
            ->groupBy(DB::raw('date(collected_at)'))
            ->orderByDesc(DB::raw('date(collected_at)'))
            ->limit(7)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($row) use ($featuredFuels): array {
                $values = [];

                foreach ($featuredFuels as $fuelKey) {
                    $values[$fuelKey] = $row->{$fuelKey} !== null ? round((float) $row->{$fuelKey}, 3) : null;
                }

                return [
                    'day' => $row->day,
                    'label' => Carbon::parse($row->day)->translatedFormat('d M'),
                    'values' => $values,
                ];
            });
    }
}
