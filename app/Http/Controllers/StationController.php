<?php

namespace App\Http\Controllers;

use App\Models\GasStation;
use App\Repositories\Contracts\GasStationRepositoryInterface;
use App\Repositories\Contracts\PriceRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StationController extends Controller
{
    public function search(Request $request, GasStationRepositoryInterface $gasStationRepository): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'municipality' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:150'],
        ]);

        return view('stations.search', [
            'filters' => $filters,
            'stations' => $gasStationRepository->search($filters, (int) config('fuel.search_page_size')),
            'options' => $gasStationRepository->getFilterOptions($filters),
            'metaTitle' => 'Buscador de gasolineras en España',
            'metaDescription' => 'Busca gasolineras por provincia, municipio, marca o dirección y consulta sus precios actualizados de gasolina y diésel.',
        ]);
    }

    public function show(Request $request, GasStation $station, GasStationRepositoryInterface $gasStationRepository, PriceRepositoryInterface $priceRepository): View
    {
        $validated = $request->validate([
            'fuel' => ['nullable', 'string', Rule::in(array_keys(config('fuel.fuels')))],
        ]);

        $selectedFuel = $validated['fuel'] ?? config('fuel.default_fuel');
        $station = $gasStationRepository->findWithPricing($station) ?? abort(404);

        return view('stations.show', [
            'station' => $station,
            'selectedFuel' => $selectedFuel,
            'history' => $priceRepository->getStationHistory($station->id, $selectedFuel, 14),
            'fuelOptions' => config('fuel.fuels'),
            'metaTitle' => sprintf('%s - precios actualizados en %s', $station->brand ?: 'Gasolinera', $station->municipality),
            'metaDescription' => sprintf('Consulta la ficha de %s en %s con dirección, horario y precios recientes de carburantes.', $station->brand ?: 'esta gasolinera', $station->municipality),
        ]);
    }
}
