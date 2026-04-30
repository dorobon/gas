<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\GasStationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GasStationCatalogController extends Controller
{
    public function index(Request $request, GasStationRepository $gasStationRepository): JsonResponse
    {
        $validated = $request->validate([
            'province' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json(
            $gasStationRepository->getCatalogOptions($validated['province'] ?? null)
        );
    }
}