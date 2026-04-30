<?php

namespace App\Libraries\Fuel;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BrandLogoLibrary
{
    public function resolve(?string $brand): array
    {
        $label = trim($brand ?: 'Gasolinera');
        $slug = $this->normalize($label);
        $resolvedSlug = config('brand_logos.aliases.'.$slug, $slug);

        foreach (config('brand_logos.extensions', []) as $extension) {
            $path = rtrim((string) config('brand_logos.directory'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$resolvedSlug.'.'.$extension;

            if (is_file($path)) {
                return [
                    'brand' => $label,
                    'slug' => $resolvedSlug,
                    'has_logo' => true,
                    'path' => $path,
                    'url' => url(rtrim((string) config('brand_logos.public_prefix', '/brand-logos'), '/').'/'.$resolvedSlug.'.'.$extension),
                    'mime_type' => $this->mimeTypeForExtension($extension),
                    'initials' => $this->initials($label),
                    'expected_files' => $this->expectedFiles($resolvedSlug),
                ];
            }
        }

        return [
            'brand' => $label,
            'slug' => $resolvedSlug,
            'has_logo' => false,
            'path' => null,
            'url' => null,
            'mime_type' => null,
            'initials' => $this->initials($label),
            'expected_files' => $this->expectedFiles($resolvedSlug),
        ];
    }

    public function buildCollectionReport(iterable $brands): Collection
    {
        return collect($brands)
            ->filter(fn ($brand) => filled($brand))
            ->map(fn ($brand) => $this->resolve((string) $brand))
            ->unique('slug')
            ->sortBy('brand')
            ->values();
    }

    public function normalize(?string $brand): string
    {
        return (string) Str::of($brand ?: 'generica')
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->value() ?: 'generica';
    }

    public function initials(?string $brand): string
    {
        $words = Str::of($brand ?: 'Gasolinera')
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9 ]+/', ' ')
            ->squish()
            ->explode(' ')
            ->filter();

        if ($words->isEmpty()) {
            return 'GS';
        }

        if ($words->count() === 1) {
            return Str::substr((string) $words->first(), 0, 2);
        }

        return $words
            ->take(2)
            ->map(fn (string $word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    protected function expectedFiles(string $slug): array
    {
        return collect(config('brand_logos.extensions', []))
            ->map(fn (string $extension) => rtrim((string) config('brand_logos.public_prefix', '/brand-logos'), '/').'/'.$slug.'.'.$extension)
            ->values()
            ->all();
    }

    protected function mimeTypeForExtension(string $extension): string
    {
        return match (Str::lower($extension)) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
    }
}
