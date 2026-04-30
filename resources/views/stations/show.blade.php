@extends('layouts.app')

@section('canonical_url', route('stations.show', [$station, $station->route_slug]))

@section('content')
    @php
        $formatPrice = fn ($value) => $value !== null ? number_format((float) $value, 3, ',', '.') . ' €/l' : 'Sin dato';
        $historyPoints = collect($history['points'] ?? []);
        $stationUrl = route('stations.show', [$station, $station->route_slug]);
        $stationChart = [
            'labels' => $historyPoints->pluck('label')->values()->all(),
            'datasets' => [
                [
                    'label' => $history['fuel_label'],
                    'data' => $historyPoints->pluck('value')->values()->all(),
                    'borderColor' => '#4fd1ff',
                    'backgroundColor' => 'rgba(79, 209, 255, 0.14)',
                    'pointBackgroundColor' => '#4fd1ff',
                    'tension' => 0.34,
                    'fill' => false,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                ],
            ],
        ];
    @endphp

    <section class="hero">
        <span class="eyebrow">Ficha de gasolinera</span>
        <h1>{{ $station->brand ?: 'Gasolinera' }} · {{ $station->municipality }}</h1>
        <p>{{ $station->address }}, {{ $station->postal_code }} · {{ $station->province }}. Consulta sus precios actualizados, horario, coordenadas, mapa y evolución reciente del combustible que más te interese.</p>
    </section>

    <section class="section grid grid--wide">
        <article class="card">
            <div class="section__heading">
                <div>
                    <h2>Datos de la estación</h2>
                    <p>Información útil para ubicarla y decidir si te compensa la parada.</p>
                </div>
            </div>
            <div class="detail-list">
                <div><strong>Horario:</strong> {{ $station->hours ?: 'No informado' }}</div>
                <div><strong>Tipo de venta:</strong> {{ $station->sale_type_label }}</div>
                <div><strong>Tipo de servicio:</strong> {{ $station->service_type_label }}</div>
                <div><strong>Margen:</strong> {{ $station->margin_label }}</div>
                <div><strong>Coordenadas:</strong> {{ $station->latitude !== null && $station->longitude !== null ? $station->latitude.', '.$station->longitude : 'No informadas' }}</div>
            </div>

            <div style="margin-top: 1.25rem;">
                <div class="section__heading" style="margin-bottom: 0.75rem;">
                    <div>
                        <h3>Ubicación</h3>
                        <p>Mapa OpenStreetMap para localizar la estación de un vistazo.</p>
                    </div>
                </div>

                @if($station->openStreetMapEmbedUrl)
                    <div style="border-radius: 18px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08);">
                        <iframe
                            title="Mapa de {{ $station->display_name }}"
                            src="{{ $station->openStreetMapEmbedUrl }}"
                            style="width: 100%; height: 360px; border: 0; display: block;"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>

                    <div style="margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                        <a class="button button--primary" href="{{ $station->openStreetMapUrl }}" target="_blank" rel="noopener noreferrer">
                            Abrir en OpenStreetMap
                        </a>
                        <span class="muted">{{ $station->latitude }}, {{ $station->longitude }}</span>
                    </div>
                @else
                    <div class="empty-state" style="margin-top: 0.5rem;">No hay coordenadas disponibles para mostrar el mapa.</div>
                @endif
            </div>
        </article>

        <aside class="card">
            <div class="section__heading">
                <div>
                    <h2>Últimos precios</h2>
                    <p>Captura más reciente disponible en la base de datos.</p>
                </div>
            </div>
            <div class="mini-list">
                @foreach(config('fuel.featured_fuels') as $fuelKey => $fuelMeta)
                    <div class="mini-list__item">
                        <strong>{{ $fuelMeta['label'] }}</strong>
                        <div style="margin-top: 0.35rem;"><x-price-marker :value="data_get($station, 'latestPrice.'.$fuelKey)" compact /></div>
                    </div>
                @endforeach
            </div>
        </aside>
    </section>

    <section class="section card">
        <form method="GET" action="{{ $stationUrl }}" class="form-grid">
            <label>
                Combustible para la evolución
                <select name="fuel">
                    @foreach($fuelOptions as $fuelKey => $fuelMeta)
                        <option value="{{ $fuelKey }}" @selected($selectedFuel === $fuelKey)>{{ $fuelMeta['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label style="align-self: end;">
                <button class="button button--primary" type="submit">Ver serie</button>
            </label>
        </form>
    </section>

    <section class="section card">
        <div class="section__heading">
            <div>
                <h2>Serie reciente de {{ $history['fuel_label'] }}</h2>
                <p>Últimas mediciones almacenadas para esta estación con Chart.js.</p>
            </div>
        </div>

        @if($historyPoints->isNotEmpty())
            <div class="chart-panel">
                <canvas id="station-history-chart" data-gas-chart="station-history-chart-data"></canvas>
            </div>
        @else
            <div class="empty-state">No hay histórico suficiente para este combustible en la estación seleccionada.</div>
        @endif
    </section>

    <section class="section card">
        <div class="section__heading">
            <div>
                <h2>Últimas capturas</h2>
                <p>Tabla rápida con los precios recientes más relevantes.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Gasóleo A</th>
                    <th>Gasolina 95 E10</th>
                    <th>Gasolina 98 E5</th>
                    <th>GLP</th>
                </tr>
                </thead>
                <tbody>
                @forelse($station->prices as $snapshot)
                    <tr>
                        <td>{{ $snapshot->collected_at?->translatedFormat('d/m/Y H:i') }}</td>
                        <td><x-price-marker :value="$snapshot->diesel_a" compact /></td>
                        <td><x-price-marker :value="$snapshot->gas95_e10" compact /></td>
                        <td><x-price-marker :value="$snapshot->gas98_e5" compact /></td>
                        <td><x-price-marker :value="$snapshot->glp" compact /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No hay capturas registradas todavía.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @push('structured_data')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'GasStation',
                'name' => $station->display_name,
                'url' => route('stations.show', [$station, $station->route_slug]),
                'description' => $metaDescription ?? sprintf('Consulta la ficha de %s en %s con dirección, horario y precios recientes de carburantes.', $station->brand ?: 'esta gasolinera', $station->municipality),
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $station->address,
                    'addressLocality' => $station->municipality,
                    'addressRegion' => $station->province,
                    'postalCode' => $station->postal_code,
                    'addressCountry' => 'ES',
                ],
                'geo' => $station->latitude !== null && $station->longitude !== null ? [
                    '@type' => 'GeoCoordinates',
                    'latitude' => $station->latitude,
                    'longitude' => $station->longitude,
                ] : null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush

    @push('scripts')
        <script type="application/json" id="station-history-chart-data">{!! json_encode($stationChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush
@endsection
