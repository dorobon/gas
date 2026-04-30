@extends('layouts.app')

@section('canonical_url', route('prices.index'))

@section('content')
    @php
        $formatPrice = fn ($value) => $value !== null ? number_format((float) $value, 3, ',', '.') . ' €/l' : 'Sin dato';
        $history = collect($dashboard['history'] ?? []);
        $dashboardChart = [
            'labels' => $history->pluck('label')->values()->all(),
            'datasets' => [],
        ];
        $palette = [
            ['border' => '#4fd1ff', 'fill' => 'rgba(79, 209, 255, 0.16)'],
            ['border' => '#8bffb0', 'fill' => 'rgba(139, 255, 176, 0.16)'],
            ['border' => '#ffd166', 'fill' => 'rgba(255, 209, 102, 0.16)'],
            ['border' => '#a78bfa', 'fill' => 'rgba(167, 139, 250, 0.16)'],
            ['border' => '#ff8b8b', 'fill' => 'rgba(255, 139, 139, 0.16)'],
            ['border' => '#5da8ff', 'fill' => 'rgba(93, 168, 255, 0.16)'],
        ];

        foreach (config('fuel.featured_fuels') as $fuelKey => $fuelMeta) {
            $colors = $palette[count($dashboardChart['datasets']) % count($palette)];

            $dashboardChart['datasets'][] = [
                'label' => $fuelMeta['short'],
                'data' => $history->map(fn ($point) => data_get($point, 'values.'.$fuelKey))->values()->all(),
                'borderColor' => $colors['border'],
                'backgroundColor' => $colors['fill'],
                'pointBackgroundColor' => $colors['border'],
                'tension' => 0.35,
                'fill' => false,
                'spanGaps' => true,
                'pointRadius' => 3,
                'pointHoverRadius' => 5,
            ];
        }
    @endphp

    <section class="hero">
        <span class="eyebrow">Precio gasolina hoy · precio diésel España</span>
        <h1>Precio de la gasolina y el diésel hoy en España</h1>
        <p>Consulta los precios medios actuales de los carburantes más relevantes, compáralos con el día anterior y salta directamente a las gasolineras más baratas o al histórico de evolución. Un tablero claro, rápido y sin humo… salvo el metafórico.</p>
        <div class="hero__actions">
            <a class="button button--primary" href="{{ route('prices.cheapest') }}">Ver gasolineras baratas</a>
            <a class="button" href="{{ route('prices.historic') }}">Analizar histórico</a>
            <a class="button" href="{{ route('stations.search') }}">Buscar una estación</a>
        </div>
    </section>

    <section class="section">
        <div class="section__heading">
            <div>
                <h2>Resumen nacional actual</h2>
                <p>Comparativa frente a la captura anterior para los carburantes más consultados.</p>
            </div>
            <div class="muted">
                @if($dashboard['latest_at'])
                    Actualizado: {{ $dashboard['latest_at']->translatedFormat('d/m/Y H:i') }}
                @endif
            </div>
        </div>

        @if(! empty($dashboard['averages']))
            <div class="grid grid--cards">
                @foreach($dashboard['averages'] as $item)
                    @php
                        $trend = $item['trend'];
                        $badgeClass = $trend === null ? 'badge--flat' : ($trend > 0 ? 'badge--up' : ($trend < 0 ? 'badge--down' : 'badge--flat'));
                    @endphp
                    <article class="card">
                        <div class="muted">{{ $item['label'] }}</div>
                        <div class="metric"><x-price-marker :value="$item['current']" /></div>
                        <div class="muted" style="margin-top: 0.35rem;">Ayer: <x-price-marker :value="$item['previous']" compact /></div>
                        <div style="margin-top: 0.75rem;">
                            <span class="badge {{ $badgeClass }}">
                                @if($trend === null)
                                    Sin variación disponible
                                @elseif($trend > 0)
                                    ▲ {{ number_format($trend, 3, ',', '.') }} €/l
                                @elseif($trend < 0)
                                    ▼ {{ number_format(abs($trend), 3, ',', '.') }} €/l
                                @else
                                    = Sin cambios
                                @endif
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">Todavía no hay precios cargados. Puedes usar el comando de demo o lanzar la importación oficial.</div>
        @endif
    </section>

    <section class="section grid grid--wide">
        <article class="card">
            <div class="section__heading">
                <div>
                    <h3>Evolución reciente</h3>
                    <p>Promedio diario de los combustibles destacados para detectar tendencias de un vistazo con Chart.js.</p>
                </div>
            </div>

            @if($history->isNotEmpty())
                <div class="chart-panel chart-panel--sm">
                    <canvas id="dashboard-history-chart" data-gas-chart="dashboard-history-chart-data"></canvas>
                </div>
            @endif

            @if($history->isNotEmpty())
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            @foreach(config('fuel.featured_fuels') as $fuelKey => $fuelMeta)
                                <th>{{ $fuelMeta['short'] }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($history as $point)
                            <tr>
                                <td>{{ $point['label'] }}</td>
                                @foreach(config('fuel.featured_fuels') as $fuelKey => $fuelMeta)
                                    <td>{{ $formatPrice(data_get($point, 'values.'.$fuelKey)) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">Aún no existe histórico suficiente para construir la comparativa.</div>
            @endif
        </article>

        <aside class="card">
            <div class="section__heading">
                <div>
                    <h3>Gasolineras con diésel A más barato</h3>
                    <p>Atajo rápido hacia la parte más ahorradora del portal.</p>
                </div>
            </div>

            @if(($dashboard['cheapest_highlights'] ?? collect())->isNotEmpty())
                <div class="mini-list">
                    @foreach($dashboard['cheapest_highlights'] as $entry)
                        <a class="mini-list__item" href="{{ route('stations.show', [$entry->station, $entry->station?->route_slug]) }}">
                            <strong>{{ $entry->station?->brand ?: 'Gasolinera' }}</strong>
                            <div class="muted">{{ $entry->station?->address }} · {{ $entry->station?->municipality }}</div>
                            <div style="margin-top: 0.5rem; font-weight: 700;">{{ $formatPrice($entry->diesel_a) }}</div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">No hay suficientes estaciones para mostrar un ranking destacado.</div>
            @endif
        </aside>
    </section>

    <section class="section">
        <div class="notice">{{ $dashboard['legal_notice'] }}</div>
    </section>

    @push('structured_data')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $metaTitle ?? 'Precio de la gasolina y diésel hoy en España',
                'description' => $metaDescription ?? 'Consulta el precio de la gasolina y del diésel hoy en España.',
                'url' => route('prices.index'),
                'inLanguage' => str_replace('_', '-', app()->getLocale()),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush

    @push('scripts')
        <script type="application/json" id="dashboard-history-chart-data">{!! json_encode($dashboardChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush
@endsection
