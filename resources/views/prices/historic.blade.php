@extends('layouts.app')

@section('canonical_url', route('prices.historic'))

@section('content')
    @php
        $formatPrice = fn ($value) => $value !== null ? number_format((float) $value, 3, ',', '.') . ' €/l' : 'Sin dato';
        $points = collect($history['points'] ?? []);
        $historicChart = [
            'labels' => $points->pluck('label')->values()->all(),
            'datasets' => [
                [
                    'label' => 'Media',
                    'data' => $points->pluck('average')->values()->all(),
                    'borderColor' => '#4fd1ff',
                    'backgroundColor' => 'rgba(79, 209, 255, 0.14)',
                    'pointBackgroundColor' => '#4fd1ff',
                    'tension' => 0.34,
                    'fill' => false,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                ],
                [
                    'label' => 'Mínimo',
                    'data' => $points->pluck('min')->values()->all(),
                    'borderColor' => '#78f0a5',
                    'backgroundColor' => 'rgba(120, 240, 165, 0.14)',
                    'pointBackgroundColor' => '#78f0a5',
                    'tension' => 0.34,
                    'fill' => false,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                ],
                [
                    'label' => 'Máximo',
                    'data' => $points->pluck('max')->values()->all(),
                    'borderColor' => '#ffd166',
                    'backgroundColor' => 'rgba(255, 209, 102, 0.14)',
                    'pointBackgroundColor' => '#ffd166',
                    'tension' => 0.34,
                    'fill' => false,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                ],
            ],
        ];
    @endphp

    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('prices.index') }}">Inicio</a>
        <span aria-hidden="true">/</span>
        <span>Histórico</span>
    </nav>

    <section class="hero">
        <span class="eyebrow">Histórico precio gasolina España</span>
        <h1>Histórico de {{ $history['fuel_label'] ?? 'carburantes' }}</h1>
        <p>Explora la evolución diaria del precio medio de los carburantes en España y, si quieres hilar fino, filtra por provincia para detectar tendencias locales, repuntes y ventanas de repostaje más favorables.</p>
    </section>

    <section class="section card">
        <form method="GET" action="{{ route('prices.historic') }}" class="form-grid">
            <label>
                Carburante
                <select name="fuel">
                    @foreach($fuelOptions as $fuelKey => $fuelMeta)
                        <option value="{{ $fuelKey }}" @selected(($filters['fuel'] ?? config('fuel.default_fuel')) === $fuelKey)>{{ $fuelMeta['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Provincia
                <select name="province" data-gas-catalog-province data-selected-value="{{ $filters['province'] ?? '' }}">
                    @if(!empty($provinces))
                        <option value="">Todas</option>
                        @foreach($provinces as $prov)
                            <option value="{{ $prov }}" @if(($filters['province'] ?? '') === $prov) selected @endif>{{ $prov }}</option>
                        @endforeach
                    @else
                        <option value="">Cargando provincias...</option>
                    @endif
                </select>
            </label>
            <label>
                Rango
                <select name="days">
                    @foreach(config('fuel.history_days') as $days)
                        <option value="{{ $days }}" @selected((int) ($filters['days'] ?? 30) === (int) $days)>{{ $days }} días</option>
                    @endforeach
                </select>
            </label>
            <label style="align-self: end;">
                <button class="button button--primary" type="submit">Actualizar gráfico</button>
            </label>
        </form>
    </section>

    <section class="section grid grid--cards">
        <article class="card">
            <div class="muted">Promedio del periodo</div>
            <div class="metric"><x-price-marker :value="data_get($history, 'summary.average')" /></div>
        </article>
        <article class="card">
            <div class="muted">Mínimo observado</div>
            <div class="metric"><x-price-marker :value="data_get($history, 'summary.min')" compact /></div>
        </article>
        <article class="card">
            <div class="muted">Máximo observado</div>
            <div class="metric"><x-price-marker :value="data_get($history, 'summary.max')" compact /></div>
        </article>
        <article class="card">
            <div class="muted">Variación del periodo</div>
            <div class="metric">{{ data_get($history, 'summary.variation') !== null ? number_format((float) data_get($history, 'summary.variation'), 3, ',', '.') . ' €/l' : 'Sin dato' }}</div>
        </article>
    </section>

    <section class="section card">
        <div class="section__heading">
            <div>
                <h2>Gráfica diaria</h2>
                <p>Comparativa visual del promedio, mínimo y máximo por día con Chart.js.</p>
            </div>
        </div>

        @if($points->isNotEmpty())
            <div class="chart-panel">
                <canvas id="historic-price-chart" data-gas-chart="historic-price-chart-data"></canvas>
            </div>

            <div class="table-wrap" style="margin-top: 1.4rem;">
                <table>
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Media</th>
                        <th>Mínimo</th>
                        <th>Máximo</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($points as $point)
                        <tr>
                            <td>{{ $point['label'] }}</td>
                            <td>{{ $formatPrice($point['average']) }}</td>
                            <td>{{ $formatPrice($point['min']) }}</td>
                            <td>{{ $formatPrice($point['max']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">No hay suficientes datos históricos para el filtro seleccionado.</div>
        @endif
    </section>

    @push('scripts')
        <script type="application/json" id="historic-price-chart-data">{!! json_encode($historicChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush

    @push('structured_data')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Inicio',
                        'item' => route('prices.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Histórico',
                        'item' => route('prices.historic'),
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush
@endsection
