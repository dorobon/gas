<?php

namespace App\Http\Controllers;

use App\Libraries\Fuel\FuelImportLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ImportController extends Controller
{
    public function updatePrices(Request $request, FuelImportLibrary $fuelImportLibrary): JsonResponse
    {
        $request->validate([
            'token' => ['nullable', 'string'],
            'source' => ['nullable', 'string', Rule::in(['auto', 'xls', 'rest'])],
        ]);

        $configuredToken = (string) config('fuel.import_token');
        $receivedToken = (string) ($request->header('X-Import-Token') ?: $request->input('token', ''));

        abort_unless($configuredToken !== '' && hash_equals($configuredToken, $receivedToken), Response::HTTP_FORBIDDEN, 'Token de importación no válido.');

        try {
            $summary = $fuelImportLibrary->importFromOfficialSource(
                null,
                (string) $request->input('source', config('fuel.import_strategy', 'auto')),
            );
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => 'La importación oficial ha fallado.',
                'error' => $throwable->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => 'Importación completada correctamente.',
            'data' => $summary,
        ]);
    }
}
