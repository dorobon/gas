<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\Fuel\SocialCardLibrary;
use App\Models\GasStation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StationSocialCardController extends Controller
{
    public function show(Request $request, SocialCardLibrary $socialCards): JsonResponse
    {
        $validated = $this->validateRequest($request);
        $station = $this->resolveStation((int) $validated['id']);

        return response()->json(
            $socialCards->describe($station, $validated),
            200,
            [
                'Cache-Control' => 'public, max-age=86400, s-maxage=86400, stale-while-revalidate=3600',
            ]
        );
    }

    public function image(Request $request, SocialCardLibrary $socialCards): BinaryFileResponse
    {
        $validated = $this->validateRequest($request);
        $station = $this->resolveStation((int) $validated['id']);
        $options = $socialCards->resolveOptions($validated);
        $asset = $socialCards->ensureRendered($station, $options);

        return response()->file($asset['path'], [
            'Content-Type' => $asset['mime_type'],
            'Content-Disposition' => 'inline; filename="'.$asset['filename'].'"',
            'Cache-Control' => 'public, max-age=86400, s-maxage=86400, stale-while-revalidate=3600',
        ]);
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'id' => ['required', 'integer', 'exists:stations,id'],
            'social' => ['nullable', 'string', Rule::in(array_keys(config('social_cards.socials', [])))],
            'og_type' => ['nullable', 'string', Rule::in(array_keys(config('social_cards.og_types', [])))],
            'type' => ['nullable', 'string', 'max:50'],
        ]);
    }

    protected function resolveStation(int $stationId): GasStation
    {
        return GasStation::query()
            ->with(['latestPrice'])
            ->findOrFail($stationId);
    }
}
