<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\GasStationRepositoryInterface;
use App\Repositories\Contracts\PriceRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PriceController extends Controller
{
    public function index(PriceRepositoryInterface $priceRepository): View
    {
        return view('prices.index', [
            'dashboard' => $priceRepository->getDashboardSnapshot(),
            'metaTitle' => 'Precio de la gasolina y diésel hoy en España',
            'metaDescription' => 'Consulta el precio de la gasolina y del diésel hoy en España, con evolución reciente, comparativa diaria y acceso rápido a las gasolineras más baratas.',
        ]);
    }

    public function cheapest(Request $request, PriceRepositoryInterface $priceRepository, GasStationRepositoryInterface $gasStationRepository): View
    {
        $filters = $request->validate([
            'province' => ['nullable', 'string', 'max:120'],
            'municipality' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:150'],
            'fuel' => ['nullable', 'string', Rule::in(array_keys(config('fuel.fuels')))],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $filters['fuel'] = $filters['fuel'] ?? config('fuel.default_fuel');

        return view('prices.cheapest', [
            'filters' => $filters,
            'results' => $priceRepository->getCheapest($filters, (int) config('fuel.cheapest_page_size')),
            'options' => $gasStationRepository->getFilterOptions($filters),
            'fuelLabel' => config('fuel.fuels.'.$filters['fuel'].'.label', $filters['fuel']),
            'metaTitle' => 'Gasolineras más baratas de hoy en España',
            'metaDescription' => 'Filtra por provincia, municipio, marca y carburante para encontrar las gasolineras más baratas de hoy y ahorrar en cada repostaje.',
        ]);
    }

    public function historic(Request $request, PriceRepositoryInterface $priceRepository, GasStationRepositoryInterface $gasStationRepository): View
    {
        $filters = $request->validate([
            'province' => ['nullable', 'string', 'max:120'],
            'fuel' => ['nullable', 'string', Rule::in(array_keys(config('fuel.fuels')))],
            'days' => ['nullable', 'integer', Rule::in(config('fuel.history_days'))],
        ]);

        return view('prices.historic', [
            'filters' => $filters,
            'history' => $priceRepository->getHistoricSeries($filters),
            'options' => $gasStationRepository->getFilterOptions($filters),
            'metaTitle' => 'Histórico del precio de la gasolina y el diésel',
            'metaDescription' => 'Analiza la evolución histórica del precio de los carburantes en España por combustible y provincia con una vista diaria clara y orientada a SEO.',
        ]);
    }
}
