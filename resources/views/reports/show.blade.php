@extends('layouts.app')

@section('meta_type', 'article')
@section('canonical_url', route('reports.show', $report['slug']))
@section('meta_published_time', $publishedAt->toIso8601String())
@section('meta_updated_time', $publishedAt->toIso8601String())

@section('content')
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('prices.index') }}">Inicio</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('reports.index') }}">Informes</a>
        <span aria-hidden="true">/</span>
        <span>{{ $report['title'] }}</span>
    </nav>

    <article class="hero article">
        <span class="eyebrow">{{ $report['category'] }}</span>
        <h1>{{ $report['title'] }}</h1>
        <p>{{ $report['excerpt'] }}</p>
        <p class="muted" style="margin-top: 1rem;">Publicado el {{ $publishedAt->translatedFormat('d \d\e F \d\e Y') }} · {{ $report['reading_minutes'] }} min de lectura</p>
    </article>

    <section class="section grid grid--wide">
        <article class="card article">
            @foreach($report['body'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </article>

        <aside class="card article">
            <h2>Claves del informe</h2>
            <ul>
                @foreach($report['highlights'] as $highlight)
                    <li>{{ $highlight }}</li>
                @endforeach
            </ul>
            <div style="margin-top: 1rem;">
                <a class="button" href="{{ route('reports.index') }}">Volver a informes</a>
            </div>
        </aside>
    </section>

    @push('structured_data')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $report['title'],
                'description' => $report['excerpt'],
                'datePublished' => $publishedAt->toIso8601String(),
                'dateModified' => $publishedAt->toIso8601String(),
                'author' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                ],
                'mainEntityOfPage' => route('reports.show', $report['slug']),
                'articleSection' => $report['category'],
                'inLanguage' => str_replace('_', '-', app()->getLocale()),
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
                        'name' => 'Informes',
                        'item' => route('reports.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $report['title'],
                        'item' => route('reports.show', $report['slug']),
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush
@endsection
