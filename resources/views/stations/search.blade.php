@extends('layouts.app')

@section('canonical_url', route('stations.search'))

@section('content')
    @php
        $formatPrice = fn ($value) => $value !== null ? number_format((float) $value, 3, ',', '.') . ' €/l' : 'Sin dato';
    @endphp

    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('prices.index') }}">Inicio</a>
        <span aria-hidden="true">/</span>
        <span>Buscador de gasolineras</span>
    </nav>

    <section class="hero">
        <span class="eyebrow">Buscador gasolineras España</span>
        <h1>Busca gasolineras por provincia, municipio o marca</h1>
        <p>Encuentra estaciones de servicio en España, consulta su ubicación, horario y precios más recientes, y accede a la ficha completa para revisar la evolución de cada carburante.</p>
    </section>

    <section class="section card">
        <form method="GET" action="{{ route('stations.search') }}" class="form-grid" data-gas-catalog-form data-gas-catalogs-url="{{ route('api.stations.catalogs') }}">
            <label>
                Buscar
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Marca, dirección o localidad">
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
            <label style="align-self: end;">
                <button class="button button--primary" type="submit">Buscar estaciones</button>
            </label>
        </form>
    </section>

    <section class="section">
        <div class="section__heading">
            <div>
                <h2>Resultados del buscador</h2>
                <p>{{ $stations->total() }} gasolineras localizadas con los filtros actuales.</p>
            </div>
        </div>

        @if($stations->count())
            <div class="grid grid--cards">
                @foreach($stations as $station)
                    <article class="card">
                        <div class="muted">{{ $station->province }} · {{ $station->municipality }}</div>
                        <div class="brand-line">
                            <x-brand-badge :brand="$station->brand" size="sm" />
                            <h3>{{ $station->brand ?: 'Gasolinera' }}</h3>
                        </div>
                        <p class="muted">{{ $station->address }} · {{ $station->postal_code }}</p>
                        <div class="detail-list">
                            <div><strong>Horario:</strong> {{ $station->hours ?: 'No informado' }}</div>
                            <div><strong>Gasóleo A:</strong> <x-price-marker :value="$station->latestPrice?->diesel_a" compact /></div>
                            <div><strong>Gasolina 95 E10:</strong> <x-price-marker :value="$station->latestPrice?->gas95_e10" compact /></div>
                            <div><strong>Gasolina 98 E5:</strong> <x-price-marker :value="$station->latestPrice?->gas98_e5" compact /></div>
                        </div>
                        <div style="margin-top: 1rem;">
                            <a class="button button--primary" href="{{ route('stations.show', [$station, $station->route_slug]) }}">Ver ficha</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top: 1rem;">
                {{ $stations->links() }}
            </div>
        @else
            <div class="empty-state">No se han encontrado gasolineras con los filtros indicados.</div>
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
                        'name' => 'Buscador de gasolineras',
                        'item' => route('stations.search'),
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush
@endsection
