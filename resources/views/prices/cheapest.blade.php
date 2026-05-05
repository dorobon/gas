@extends('layouts.app')

@section('canonical_url', route('prices.cheapest'))

@section('content')
    @php
        $formatPrice = fn ($value) => $value !== null ? number_format((float) $value, 3, ',', '.') . ' €/l' : 'Sin dato';
    @endphp

    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('prices.index') }}">Inicio</a>
        <span aria-hidden="true">/</span>
        <span>Gasolineras más baratas</span>
    </nav>

    <section class="hero">
        <span class="eyebrow">Gasolineras baratas · ahorrar en combustible</span>
        <h1>Gasolineras más baratas para {{ $fuelLabel }}</h1>
        <p>Filtra por provincia, municipio, marca o búsqueda libre para localizar las estaciones de servicio con el carburante más económico hoy. Ideal para comparar antes de cada repostaje y no regalar céntimos al surtidor equivocado.</p>
    </section>

    <section class="section card">
        <form method="GET" action="{{ route('prices.cheapest') }}" class="form-grid" data-gas-catalog-form data-gas-catalogs-url="{{ route('api.stations.catalogs') }}">
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
                Municipio
                <select name="municipality" data-gas-catalog-municipality data-selected-value="{{ $filters['municipality'] ?? '' }}" disabled>
                    <option value="">Elige una provincia primero</option>
                </select>
            </label>
            <label>
                Marca
                <select name="brand" data-gas-catalog-brand data-selected-value="{{ $filters['brand'] ?? '' }}">
                    <option value="">Cargando marcas...</option>
                </select>
            </label>
            <label>
                Carburante
                <select name="fuel">
                    @foreach($fuelOptions as $fuelKey => $fuelMeta)
                        <option value="{{ $fuelKey }}" @selected(($filters['fuel'] ?? config('fuel.default_fuel')) === $fuelKey)>{{ $fuelMeta['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Buscar
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Marca, dirección o municipio">
            </label>
            <label style="align-self: end;">
                <button class="button button--primary" type="submit">Aplicar filtros</button>
            </label>
        </form>
    </section>

    <section class="section card">
        <div class="section__heading">
            <div>
                <h2>Ranking ordenado por precio</h2>
                <p>Resultados actualizados del combustible seleccionado con acceso directo a cada ficha de estación.</p>
            </div>
            <div class="muted">{{ $results->total() }} resultados</div>
        </div>

        @if($results->count())
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Estación</th>
                        <th>Ubicación</th>
                        <th>Horario</th>
                        <th>Precio</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($results as $index => $entry)
                        @php $station = $entry->station; @endphp
                        <tr>
                            <td>{{ $results->firstItem() + $index }}</td>
                            <td>
                                <div class="brand-line">
                                    <x-brand-badge :brand="$station?->brand" size="sm" />
                                    <a href="{{ route('stations.show', [$station, $station?->route_slug]) }}"><strong>{{ $station?->brand ?: 'Gasolinera' }}</strong></a>
                                </div>
                                <div class="muted">{{ $station?->service_type === 'D' ? 'Autoservicio' : 'Con personal' }}</div>
                            </td>
                            <td>
                                {{ $station?->address }}<br>
                                <span class="muted">{{ $station?->postal_code }} · {{ $station?->municipality }} ({{ $station?->province }})</span>
                            </td>
                            <td>{{ $station?->hours ?: 'No informado' }}</td>
                            <td><x-price-marker :value="$entry->{$filters['fuel'] ?? config('fuel.default_fuel')}" compact /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                {{ $results->links() }}
            </div>
        @else
            <div class="empty-state">No hay gasolineras que cumplan los filtros actuales. Prueba con otra provincia, marca o tipo de combustible.</div>
        @endif
    </section>

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
                        'name' => 'Gasolineras más baratas',
                        'item' => route('prices.cheapest'),
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush
@endsection
