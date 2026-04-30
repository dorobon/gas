<?php

namespace Tests\Feature;

use App\Libraries\Fuel\FuelImportLibrary;
use App\Models\GasStation;
use App\Models\Price;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FuelPortalTest extends TestCase
{
    public function test_homepage_displays_the_prices_dashboard(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Precio de la gasolina y el diésel hoy en España')
            ->assertSee('Resumen nacional actual')
            ->assertSee('dashboard-history-chart');
    }

    public function test_homepage_contains_theme_toggle_footer_and_canonical_metadata(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('<link rel="canonical" href="'.route('prices.index').'">', false)
            ->assertSee('<link rel="manifest" href="'.url('/site.webmanifest').'">', false)
            ->assertSee('Sitemap XML')
            ->assertSee('HTML optimizado con carga diferida de gráficos');
    }

    public function test_cheapest_page_is_available(): void
    {
        $response = $this->get('/baratas?fuel=diesel_a');

        $response
            ->assertOk()
            ->assertSee('Gasolineras más baratas')
            ->assertSee('Ranking ordenado por precio');
    }

    public function test_filtered_pages_are_marked_as_noindex_to_reduce_duplicate_content(): void
    {
        $this->get('/baratas?fuel=diesel_a')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('prices.cheapest').'">', false);
    }

    public function test_historic_page_is_available(): void
    {
        $response = $this->get('/historico?fuel=gas95_e10&days=30');

        $response
            ->assertOk()
            ->assertSee('Histórico de')
            ->assertSee('Gráfica diaria')
            ->assertSee('historic-price-chart');
    }

    public function test_station_search_page_is_available(): void
    {
        $response = $this->get('/gasolineras');

        $response
            ->assertOk()
            ->assertSee('Busca gasolineras por provincia, municipio o marca')
            ->assertSee('Resultados del buscador')
            ->assertSee('data-gas-catalog-form', false)
            ->assertSee(route('api.stations.catalogs'), false);
    }

    public function test_station_catalog_api_returns_json_for_dependency_selects(): void
    {
        $station = GasStation::query()
            ->whereNotNull('province')
            ->whereNotNull('municipality')
            ->whereNotNull('brand')
            ->firstOrFail();

        $response = $this->getJson('/api/stations/catalogs?province='.urlencode((string) $station->province));

        $response->assertOk();

        $payload = $response->json();

        $this->assertContains($station->province, $payload['provinces']);
        $this->assertContains($station->brand, $payload['brands']);
        $this->assertContains($station->municipality, $payload['municipalities']);
    }

    public function test_station_detail_and_api_history_are_available(): void
    {
        $station = GasStation::query()->firstOrFail();
        $stationUrl = route('stations.show', [$station, $station->route_slug]);

        $this->get($stationUrl)
            ->assertOk()
            ->assertSee((string) $station->municipality)
            ->assertSee('Últimos precios')
            ->assertSee('Mapa OpenStreetMap')
            ->assertSee('Abrir en OpenStreetMap')
            ->assertSee('station-history-chart')
            ->assertSee('Widget social y OG image')
            ->assertSee('api/social-cards/render.jpg', false)
            ->assertSee('og:image', false);

        $this->getJson('/api/stations/'.$station->id.'/prices?fuel=diesel_a&days=10')
            ->assertOk()
            ->assertJsonPath('station.id', $station->id)
            ->assertJsonStructure([
                'station' => ['id', 'brand', 'municipality', 'address'],
                'history' => ['fuel', 'fuel_label', 'points'],
            ]);
    }

    public function test_social_card_api_returns_metadata_and_jpg_image(): void
    {
        $station = GasStation::query()->firstOrFail();

        $metadataResponse = $this->getJson('/api/social-cards?id='.$station->id.'&social=twitter&type=summary_large_image&og_type=website');

        $metadataResponse
            ->assertOk()
            ->assertJsonPath('station.id', $station->id)
            ->assertJsonPath('preset.social', 'twitter')
            ->assertJsonPath('preset.type', 'summary_large_image')
            ->assertJsonPath('preset.og_type', 'website');

        $imageUrl = $metadataResponse->json('image_url');
        $imagePath = parse_url($imageUrl, PHP_URL_PATH) ?: '';
        $imageQuery = parse_url($imageUrl, PHP_URL_QUERY);

        $this->get($imagePath.($imageQuery ? '?'.$imageQuery : ''))
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_reports_page_is_available(): void
    {
        $response = $this->get('/informes');

        $response
            ->assertOk()
            ->assertSee('Informes y actualidad sobre los precios de los carburantes');
    }

    public function test_sitemap_is_available_with_core_urls(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee(route('prices.index'), false)
            ->assertSee(route('reports.index'), false);
    }

    public function test_import_endpoint_requires_valid_token(): void
    {
        $this->postJson('/api/update-prices')
            ->assertForbidden();
    }

    public function test_official_import_falls_back_to_rest_when_xls_cannot_be_parsed(): void
    {
        Price::query()->delete();
        GasStation::query()->delete();

        config()->set('fuel.source_url', 'https://example.test/precios.xls');
        config()->set('fuel.rest_source_url', 'https://example.test/precios.json');

        Http::fake([
            'https://example.test/precios.xls' => Http::response('esto no es un excel válido', 200),
            'https://example.test/precios.json' => Http::response([
                'Fecha' => '30/04/2026 07:00:00',
                'ListaEESSPrecio' => [
                    [
                        'IDEESS' => 'TEST-001',
                        'Provincia' => 'MADRID',
                        'Municipio' => 'MADRID',
                        'Localidad' => 'Centro',
                        'C.P.' => '28013',
                        'Dirección' => 'Gran Vía, 1',
                        'Margen' => 'D',
                        'Longitud_x0020__x0028_WGS84_x0029_' => '-3,703790',
                        'Latitud' => '40,416775',
                        'Rótulo' => 'REST DEMO',
                        'Tipo_x0020_Venta' => 'P',
                        'Remisión' => 'OM',
                        'Horario' => '24H',
                        'Precio_x0020_Gasoleo_x0020_A' => '1,499',
                        'Precio_x0020_Gasolina_x0020_95_x0020_E10' => '1,589',
                        'Precio_x0020_Gasolina_x0020_98_x0020_E5' => '1,729',
                        'Precio_x0020_Gases_x0020_licuados_x0020_del_x0020_petróleo' => '0,955',
                        'IDMunicipio' => '079',
                        'IDProvincia' => '28',
                        'IDCCAA' => '13',
                    ],
                ],
            ], 200),
        ]);

        $summary = app(FuelImportLibrary::class)->importFromOfficialSource();

        $this->assertTrue($summary['fallback_used']);
        $this->assertSame('rest', $summary['source_type']);
        $this->assertSame(1, GasStation::query()->count());
        $this->assertSame(1, Price::query()->count());
        $this->assertSame('REST DEMO', GasStation::query()->firstOrFail()->brand);
        $this->assertSame(1.499, Price::query()->firstOrFail()->diesel_a);
    }
}
