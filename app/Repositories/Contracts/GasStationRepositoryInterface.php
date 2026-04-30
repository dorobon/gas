<?php

namespace App\Repositories\Contracts;

use App\Models\GasStation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GasStationRepositoryInterface
{
    public function search(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function findWithPricing(GasStation|int $station): ?GasStation;

    public function getProvinces(): array;

    public function getMunicipalities(?string $province = null): array;

    public function getBrands(): array;

    public function getCatalogOptions(?string $province = null): array;

    public function getFilterOptions(array $filters = []): array;
}
