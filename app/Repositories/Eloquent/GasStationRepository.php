<?php

namespace App\Repositories\Eloquent;

use App\Models\GasStation;
use App\Repositories\Contracts\GasStationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GasStationRepository implements GasStationRepositoryInterface
{
    public function search(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return GasStation::query()
            ->with('latestPrice')
            ->when($filters['province'] ?? null, fn (Builder $query, string $province) => $query->where('province', $province))
            ->when($filters['municipality'] ?? null, fn (Builder $query, string $municipality) => $query->where('municipality', $municipality))
            ->when($filters['brand'] ?? null, fn (Builder $query, string $brand) => $query->where('brand', $brand))
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('brand', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhere('municipality', 'like', '%'.$search.'%')
                        ->orWhere('locality', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('province')
            ->orderBy('municipality')
            ->orderBy('brand')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findWithPricing(GasStation|int $station): ?GasStation
    {
        $stationModel = $station instanceof GasStation
            ? $station
            : GasStation::query()->find($station);

        if (! $stationModel) {
            return null;
        }

        return $stationModel->load([
            'latestPrice',
            'prices' => fn ($query) => $query->latest('collected_at')->limit(30),
        ]);
    }

    public function getFilterOptions(array $filters = []): array
    {
        return [
            'provinces' => GasStation::query()
                ->whereNotNull('province')
                ->distinct()
                ->orderBy('province')
                ->pluck('province')
                ->all(),
            'municipalities' => GasStation::query()
                ->when($filters['province'] ?? null, fn (Builder $query, string $province) => $query->where('province', $province))
                ->whereNotNull('municipality')
                ->distinct()
                ->orderBy('municipality')
                ->pluck('municipality')
                ->all(),
            'brands' => GasStation::query()
                ->whereNotNull('brand')
                ->distinct()
                ->orderBy('brand')
                ->pluck('brand')
                ->all(),
            'fuels' => config('fuel.fuels'),
        ];
    }
}
