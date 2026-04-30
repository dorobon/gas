<?php

namespace App\Repositories\Eloquent;

use App\Models\GasStation;
use App\Repositories\Contracts\GasStationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GasStationRepository implements GasStationRepositoryInterface
{
    public function getProvinces(): array
    {
        return $this->getDistinctValues('province');
    }

    public function getMunicipalities(?string $province = null): array
    {
        return $this->getDistinctValues('municipality', $province);
    }

    public function getBrands(): array
    {
        return $this->getDistinctValues('brand');
    }

    public function getCatalogOptions(?string $province = null): array
    {
        return [
            'provinces' => $this->getProvinces(),
            'municipalities' => $this->getMunicipalities($province),
            'brands' => $this->getBrands(),
        ];
    }

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
            'provinces' => $this->getProvinces(),
            'municipalities' => $this->getMunicipalities($filters['province'] ?? null),
            'brands' => $this->getBrands(),
            'fuels' => config('fuel.fuels'),
        ];
    }

    private function getDistinctValues(string $column, ?string $province = null): array
    {
        return GasStation::query()
            ->when($province !== null && $province !== '', fn (Builder $query) => $query->where('province', $province))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->values()
            ->all();
    }
}
