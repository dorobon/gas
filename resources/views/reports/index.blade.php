@extends('layouts.app')

@section('canonical_url', route('reports.index'))

@section('content')
    <section class="hero">
        <span class="eyebrow">Noticias diésel gasolina</span>
        <h1>Informes y actualidad sobre los precios de los carburantes</h1>
        <p>Una sección editorial pensada para reforzar el contexto SEO del portal y ayudar al usuario a entender por qué suben o bajan la gasolina, el diésel y los combustibles alternativos.</p>
    </section>

    <section class="section">
        <div class="grid grid--cards">
            @foreach($reports as $report)
                <article class="card article-list__item">
                    <div class="muted">{{ \Illuminate\Support\Carbon::parse($report['published_at'])->translatedFormat('d M Y') }} · {{ $report['category'] }} · {{ $report['reading_minutes'] }} min</div>
                    <h2 style="margin-bottom: 0.5rem;">{{ $report['title'] }}</h2>
                    <p>{{ $report['excerpt'] }}</p>
                    <a class="button button--primary" href="{{ route('reports.show', $report['slug']) }}">Leer informe</a>
                </article>
            @endforeach
        </div>
    </section>
@endsection
