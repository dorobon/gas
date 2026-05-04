@extends('layouts.app')

@php
    $defaultSocial = config('social_cards.default_social', 'facebook');
    $defaultSocialPreset = config('social_cards.socials.'.$defaultSocial);
    $defaultSocialType = $defaultSocialPreset['default_type'];
    $defaultOgType = $defaultSocialPreset['default_og_type'] ?? config('social_cards.default_og_type', 'website');
    $defaultSocialSize = $defaultSocialPreset['types'][$defaultSocialType];
    $defaultSocialImageUrl = route('api.social-cards.image', [
        'id' => $station->id,
        'social' => $defaultSocial,
        'type' => $defaultSocialType,
        'og_type' => $defaultOgType,
    ]);
@endphp

@section('meta_type', $defaultOgType)
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

    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('prices.index') }}">Inicio</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('stations.search') }}">Gasolineras</a>
        <span aria-hidden="true">/</span>
        <span>{{ $station->brand ?: 'Gasolinera' }}</span>
    </nav>

    <section class="hero">
        <div class="hero__brand">
            <x-brand-badge :brand="$station->brand" size="xl" />
            <div>
                <span class="eyebrow">Ficha de gasolinera</span>
                <h1>{{ $station->brand ?: 'Gasolinera' }} · {{ $station->municipality }}</h1>
                <p>{{ $station->address }}, {{ $station->postal_code }} · {{ $station->province }}. Consulta sus precios actualizados, horario, coordenadas, mapa y evolución reciente del combustible que más te interese.</p>
            </div>
        </div>
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

    <section class="section card">
        <div class="section__heading">
            <div>
                <h2>Widget social y OG image</h2>
                <p>Genera una miniatura JPG cacheada durante 24 horas y lista para integrarse en cualquier HTML o para alimentar validadores de Facebook, X/Twitter, WhatsApp, LinkedIn, Telegram y shares OG compatibles.</p>
            </div>
        </div>

        <div class="widget-grid" data-social-widget data-endpoint="{{ route('api.social-cards.show') }}" data-station-id="{{ $station->id }}">
            <div class="widget-panel widget-preview">
                <img
                    src="{{ $defaultSocialImageUrl }}"
                    alt="Vista previa social de {{ $station->display_name }}"
                    loading="lazy"
                    decoding="async"
                    data-social-preview
                >
                <div class="widget-meta">
                    <span class="badge badge--flat" data-social-summary>{{ $defaultSocialPreset['label'] }} · {{ $defaultSocialSize['width'] }}×{{ $defaultSocialSize['height'] }} · og:type {{ $defaultOgType }}</span>
                    <span class="widget-status" data-social-status>JPG lista para compartir.</span>
                </div>
            </div>

            <div class="widget-panel">
                <div class="form-grid">
                    <label>
                        Red social
                        <select data-social-select>
                            @foreach(config('social_cards.socials') as $socialKey => $socialMeta)
                                <option value="{{ $socialKey }}" @selected($socialKey === $defaultSocial)>{{ $socialMeta['label'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Formato
                        <select data-social-type-select></select>
                    </label>

                    <label>
                        og:type
                        <select data-social-og-type-select>
                            @foreach(config('social_cards.og_types') as $ogTypeKey => $ogTypeLabel)
                                <option value="{{ $ogTypeKey }}" @selected($ogTypeKey === $defaultOgType)>{{ $ogTypeKey }} · {{ $ogTypeLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="code-block">
                    <strong>URL JPG directa</strong>
                    <input type="text" readonly value="{{ $defaultSocialImageUrl }}" data-social-url>
                </div>

                <div class="code-block">
                    <strong>HTML para incrustar la miniatura</strong>
                    <textarea rows="4" readonly data-social-img-html></textarea>
                </div>

                <div class="code-block">
                    <strong>Metatags Open Graph / Twitter</strong>
                    <textarea rows="8" readonly data-social-meta-html></textarea>
                </div>
            </div>
        </div>
    </section>

    @push('social_meta')
        <meta property="og:image" content="{{ $defaultSocialImageUrl }}">
        <meta property="og:image:secure_url" content="{{ $defaultSocialImageUrl }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="{{ $defaultSocialSize['width'] }}">
        <meta property="og:image:height" content="{{ $defaultSocialSize['height'] }}">
        <meta property="og:image:alt" content="Tarjeta social de {{ $station->display_name }} con precios actuales de carburantes">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $defaultSocialImageUrl }}">
    @endpush

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
                        'name' => 'Gasolineras',
                        'item' => route('stations.search'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $station->display_name,
                        'item' => route('stations.show', [$station, $station->route_slug]),
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush

    @push('scripts')
        <script type="application/json" id="station-history-chart-data">{!! json_encode($stationChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        <script type="application/json" id="social-card-presets-data">{!! json_encode(config('social_cards.socials'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        <script>
            (() => {
                const widget = document.querySelector('[data-social-widget]');

                if (!widget) {
                    return;
                }

                const presetsNode = document.getElementById('social-card-presets-data');
                const presets = JSON.parse(presetsNode?.textContent || '{}');
                const endpoint = widget.dataset.endpoint;
                const stationId = widget.dataset.stationId;
                const socialSelect = widget.querySelector('[data-social-select]');
                const typeSelect = widget.querySelector('[data-social-type-select]');
                const ogTypeSelect = widget.querySelector('[data-social-og-type-select]');
                const preview = widget.querySelector('[data-social-preview]');
                const summary = widget.querySelector('[data-social-summary]');
                const status = widget.querySelector('[data-social-status]');
                const urlInput = widget.querySelector('[data-social-url]');
                const imgHtml = widget.querySelector('[data-social-img-html]');
                const metaHtml = widget.querySelector('[data-social-meta-html]');

                const fillTypeOptions = (selectedSocial, preferredType = null) => {
                    const currentPreset = presets[selectedSocial] || {};
                    const entries = Object.entries(currentPreset.types || {});

                    typeSelect.innerHTML = entries.map(([value, meta]) => {
                        const selected = value === (preferredType || currentPreset.default_type) ? ' selected' : '';

                        return `<option value="${value}"${selected}>${meta.label} · ${meta.width}×${meta.height}</option>`;
                    }).join('');
                };

                const hydrateWidget = async () => {
                    status.textContent = 'Generando miniatura...';

                    const params = new URLSearchParams({
                        id: stationId,
                        social: socialSelect.value,
                        type: typeSelect.value,
                        og_type: ogTypeSelect.value,
                    });

                    try {
                        const response = await fetch(`${endpoint}?${params.toString()}`, {
                            headers: {
                                Accept: 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('No se pudo construir la miniatura social.');
                        }

                        const payload = await response.json();

                        preview.src = payload.image_url;
                        preview.alt = payload.widget.alt;
                        summary.textContent = `${payload.preset.social_label} · ${payload.preset.width}×${payload.preset.height} · og:type ${payload.preset.og_type}`;
                        urlInput.value = payload.image_url;
                        imgHtml.value = payload.widget.img_html;
                        metaHtml.value = payload.widget.meta_html;
                        status.textContent = 'JPG cacheada 24h y lista para pegar en cualquier web.';

                        if (socialSelect.value !== payload.preset.social) {
                            socialSelect.value = payload.preset.social;
                        }

                        fillTypeOptions(payload.preset.social, payload.preset.type);
                        ogTypeSelect.value = payload.preset.og_type;
                    } catch (error) {
                        status.textContent = error.message || 'No se pudo generar la miniatura.';
                    }
                };

                fillTypeOptions(socialSelect.value, '{{ $defaultSocialType }}');
                hydrateWidget();

                socialSelect.addEventListener('change', () => {
                    fillTypeOptions(socialSelect.value);
                    hydrateWidget();
                });

                typeSelect.addEventListener('change', hydrateWidget);
                ogTypeSelect.addEventListener('change', hydrateWidget);
            })();
        </script>
    @endpush
@endsection
