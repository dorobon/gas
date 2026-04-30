@extends('layouts.app')

@section('canonical_url', route('stations.search'))

@section('content')
    @php
        $formatPrice = fn ($value) => $value !== null ? number_format((float) $value, 3, ',', '.') . ' €/l' : 'Sin dato';
    @endphp

    <section class="hero">
        <span class="eyebrow">Buscador gasolineras España</span>
        <h1>Busca gasolineras por provincia, municipio o marca</h1>
        <p>Encuentra estaciones de servicio en España, consulta su ubicación, horario y precios más recientes, y accede a la ficha completa para revisar la evolución de cada carburante.</p>
    </section>

    <section class="section card">
        <form method="GET" action="{{ route('stations.search') }}" class="form-grid">
            <label>
                Buscar
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Marca, dirección o localidad">
            </label>
            <label>
                Provincia
                <select name="province">
                    <option value="">Todas</option>
                    @foreach($options['provinces'] as $province)
                        <option value="{{ $province }}" @selected(($filters['province'] ?? '') === $province)>{{ $province }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Municipio
                <select name="municipality">
                    <option value="">Todos</option>
                    @foreach($options['municipalities'] as $municipality)
                        <option value="{{ $municipality }}" @selected(($filters['municipality'] ?? '') === $municipality)>{{ $municipality }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Marca
                <select name="brand">
                    <option value="">Todas</option>
                    @foreach($options['brands'] as $brand)
                        <option value="{{ $brand }}" @selected(($filters['brand'] ?? '') === $brand)>{{ $brand }}</option>
                    @endforeach
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
                        <h3>{{ $station->brand ?: 'Gasolinera' }}</h3>
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
@endsection
