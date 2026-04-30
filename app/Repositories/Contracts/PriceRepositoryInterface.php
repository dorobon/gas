<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

interface PriceRepositoryInterface
{
    public function getLatestCollectionMoment(): ?Carbon;

    public function getDashboardSnapshot(): array;

    public function getCheapest(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function getHistoricSeries(array $filters = []): array;

    public function getStationHistory(int $stationId, ?string $fuel = null, int $days = 14): array;
}
