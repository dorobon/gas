<?php

namespace App\Libraries\Fuel;

use App\Models\GasStation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SocialCardLibrary
{
    public function __construct(
        protected BrandLogoLibrary $brandLogos,
    ) {
    }

    public function describe(GasStation $station, array $input = []): array
    {
        $options = $this->resolveOptions($input);
        $asset = $this->ensureRendered($station, $options);

        $imageUrl = $this->buildImageUrl($station, $options);
        $alt = sprintf(
            'Tarjeta social de %s en %s con precios actuales de carburantes',
            $station->brand ?: 'Gasolinera',
            $station->municipality ?: 'España',
        );

        $twitterCard = $options['social'] === 'twitter'
            ? ($options['type'] === 'summary' ? 'summary' : 'summary_large_image')
            : 'summary_large_image';

        $metaHtml = implode(PHP_EOL, [
            sprintf('<meta property="og:type" content="%s">', e($options['og_type'])),
            sprintf('<meta property="og:image" content="%s">', e($imageUrl)),
            sprintf('<meta property="og:image:type" content="%s">', e($asset['mime_type'])),
            sprintf('<meta property="og:image:width" content="%d">', $asset['width']),
            sprintf('<meta property="og:image:height" content="%d">', $asset['height']),
            sprintf('<meta property="og:image:alt" content="%s">', e($alt)),
            sprintf('<meta name="twitter:card" content="%s">', e($twitterCard)),
            sprintf('<meta name="twitter:image" content="%s">', e($imageUrl)),
        ]);

        return [
            'station' => [
                'id' => $station->id,
                'brand' => $station->brand,
                'municipality' => $station->municipality,
                'province' => $station->province,
                'address' => $station->address,
            ],
            'preset' => [
                'social' => $options['social'],
                'social_label' => $options['social_label'],
                'type' => $options['type'],
                'type_label' => $options['type_label'],
                'og_type' => $options['og_type'],
                'width' => $options['width'],
                'height' => $options['height'],
                'aspect_ratio' => $options['aspect_ratio'],
                'twitter_card' => $twitterCard,
            ],
            'image_url' => $imageUrl,
            'metadata_url' => $this->buildMetadataUrl($station, $options),
            'mime_type' => $asset['mime_type'],
            'cache_ttl_minutes' => (int) config('social_cards.cache_ttl_minutes', 1440),
            'widget' => [
                'alt' => $alt,
                'img_html' => sprintf(
                    '<img src="%s" width="%d" height="%d" alt="%s" loading="lazy" decoding="async">',
                    e($imageUrl),
                    $asset['width'],
                    $asset['height'],
                    e($alt),
                ),
                'meta_html' => $metaHtml,
            ],
        ];
    }

    public function ensureRendered(GasStation $station, array $options): array
    {
        $logo = $this->brandLogos->resolve($station->brand);
        $latestSnapshot = $station->latestPrice?->collected_at?->toIso8601String() ?? 'sin-precios';
        $logoFingerprint = $logo['has_logo'] && $logo['path'] ? (string) filemtime($logo['path']) : 'fallback';
        $fingerprint = sha1(json_encode([
            'version' => config('social_cards.version', 'v1'),
            'station' => $station->id,
            'social' => $options['social'],
            'type' => $options['type'],
            'og_type' => $options['og_type'],
            'latest' => $latestSnapshot,
            'logo' => $logoFingerprint,
        ], JSON_THROW_ON_ERROR));

        $cacheKey = 'social-card:'.$fingerprint;
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && is_file($cached['path'] ?? null)) {
            return $cached;
        }

        $outputDirectory = (string) config('social_cards.output_directory');
        $outputPath = rtrim($outputDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$fingerprint.'.jpg';

        if (! is_file($outputPath)) {
            $this->renderViaNode($this->buildPayload($station, $options, $logo), $outputPath);
        }

        $metadata = [
            'path' => $outputPath,
            'width' => $options['width'],
            'height' => $options['height'],
            'mime_type' => 'image/jpeg',
            'cache_key' => $cacheKey,
            'filename' => Str::slug(($station->brand ?: 'gasolinera').'-'.$station->municipality.'-'.$options['social'].'-'.$options['type']).'.jpg',
        ];

        Cache::put($cacheKey, $metadata, now()->addMinutes((int) config('social_cards.cache_ttl_minutes', 1440)));

        return $metadata;
    }

    public function resolveOptions(array $input): array
    {
        $socials = config('social_cards.socials', []);
        $social = Arr::get($input, 'social', config('social_cards.default_social', 'facebook'));

        if (! isset($socials[$social])) {
            $social = config('social_cards.default_social', 'facebook');
        }

        $preset = $socials[$social];
        $type = (string) Arr::get($input, 'type', $preset['default_type']);

        if (! isset($preset['types'][$type])) {
            $type = $preset['default_type'];
        }

        $ogType = (string) Arr::get($input, 'og_type', $preset['default_og_type'] ?? config('social_cards.default_og_type', 'website'));

        if (! array_key_exists($ogType, config('social_cards.og_types', []))) {
            $ogType = $preset['default_og_type'] ?? config('social_cards.default_og_type', 'website');
        }

        $typeConfig = $preset['types'][$type];

        return [
            'social' => $social,
            'social_label' => $preset['label'],
            'type' => $type,
            'type_label' => $typeConfig['label'],
            'og_type' => $ogType,
            'width' => (int) $typeConfig['width'],
            'height' => (int) $typeConfig['height'],
            'aspect_ratio' => $typeConfig['aspect_ratio'],
        ];
    }

    public function buildImageUrl(GasStation $station, array $options): string
    {
        return route('api.social-cards.image', [
            'id' => $station->id,
            'social' => $options['social'],
            'type' => $options['type'],
            'og_type' => $options['og_type'],
        ]);
    }

    public function buildMetadataUrl(GasStation $station, array $options): string
    {
        return route('api.social-cards.show', [
            'id' => $station->id,
            'social' => $options['social'],
            'type' => $options['type'],
            'og_type' => $options['og_type'],
        ]);
    }

    protected function buildPayload(GasStation $station, array $options, array $logo): array
    {
        $latest = $station->latestPrice;

        return [
            'appName' => config('app.name'),
            'dimensions' => [
                'width' => $options['width'],
                'height' => $options['height'],
            ],
            'station' => [
                'brand' => $station->brand ?: 'Gasolinera',
                'municipality' => $station->municipality ?: 'España',
                'province' => $station->province,
                'address' => $station->address ?: 'Dirección no indicada',
                'postal_code' => $station->postal_code,
            ],
            'meta' => [
                'social' => $options['social_label'],
                'social_key' => $options['social'],
                'og_type' => $options['og_type'],
                'preset_label' => $options['type_label'],
                'size_label' => sprintf('%dx%d', $options['width'], $options['height']),
                'updated_at' => $latest?->collected_at?->timezone(config('app.timezone'))->format('d/m/Y H:i'),
            ],
            'prices' => collect(config('fuel.featured_fuels', []))
                ->map(fn (array $fuelMeta, string $fuelKey) => [
                    'key' => $fuelKey,
                    'label' => $fuelMeta['label'],
                    'short' => $fuelMeta['short'] ?? $fuelMeta['label'],
                    'value' => $latest?->{$fuelKey},
                    'formatted' => $this->formatPrice($latest?->{$fuelKey}),
                ])
                ->values()
                ->all(),
            'logo' => [
                'brand' => $logo['brand'],
                'has_logo' => $logo['has_logo'],
                'path' => $logo['path'],
                'initials' => $logo['initials'],
            ],
        ];
    }

    protected function formatPrice(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Sin dato';
        }

        return number_format((float) $value, 3, ',', '.').' €/l';
    }

    protected function renderViaNode(array $payload, string $outputPath): void
    {
        $script = (string) config('social_cards.renderer_script');
        $binary = (string) config('social_cards.node_binary', 'node');

        if (! is_file($script)) {
            throw new RuntimeException('No se encontró el script de renderizado de tarjetas sociales.');
        }

        if (! is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0777, true);
        }

        $inputPath = tempnam(sys_get_temp_dir(), 'gas-social-card-');

        if ($inputPath === false) {
            throw new RuntimeException('No se pudo crear el archivo temporal para la tarjeta social.');
        }

        file_put_contents($inputPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        $process = new Process([$binary, $script, $inputPath, $outputPath], base_path());
        $process->setTimeout(120);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new RuntimeException('Falló la generación del JPG social: '.$exception->getProcess()->getErrorOutput(), previous: $exception);
        } finally {
            @unlink($inputPath);
        }
    }
}
