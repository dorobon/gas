<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GasStation;
use App\Repositories\Contracts\PriceRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StationPriceController extends Controller
{
    public function show(Request $request, GasStation $station, PriceRepositoryInterface $priceRepository): JsonResponse
    {
        $validated = $request->validate([
            'fuel' => ['nullable', 'string', Rule::in(array_keys(config('fuel.fuels')))],
            'days' => ['nullable', 'integer', 'min:3', 'max:30'],
        ]);

        $days = (int) ($validated['days'] ?? 14);
        $fuel = $validated['fuel'] ?? config('fuel.default_fuel');

        return response()->json([
            'station' => [
                'id' => $station->id,
                'brand' => $station->brand,
                'municipality' => $station->municipality,
                'address' => $station->address,
            ],
            'history' => $priceRepository->getStationHistory($station->id, $fuel, $days),
        ]);
    }
}
