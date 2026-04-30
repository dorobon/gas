<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trim(($metaTitle ?? config('app.name')).' | '.config('app.name'), ' |') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Portal de precios de carburantes en España con gasolineras baratas, histórico y buscador.' }}">
    <meta name="robots" content="index,follow">
    <meta property="og:title" content="{{ $metaTitle ?? config('app.name') }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Portal de precios de carburantes en España.' }}">
    <meta property="og:type" content="website">
    <style>
        :root {
            --bg: #07111f;
            --bg-soft: #101d31;
            --panel: rgba(255, 255, 255, 0.08);
            --panel-strong: rgba(255, 255, 255, 0.12);
            --line: rgba(255, 255, 255, 0.12);
            --text: #f4f7fb;
            --muted: #b8c3d9;
            --brand: #4fd1ff;
            --brand-2: #8bffb0;
            --warning: #ffd166;
            --danger: #ff8b8b;
            --success: #78f0a5;
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.22);
            --radius: 22px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(79, 209, 255, 0.14), transparent 30%),
                radial-gradient(circle at top right, rgba(139, 255, 176, 0.08), transparent 22%),
                linear-gradient(180deg, #08101d 0%, #0d1830 45%, #0a1221 100%);
            min-height: 100vh;
        }

        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; }
        .shell { width: min(1180px, calc(100% - 2rem)); margin: 0 auto; }
        .site-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(18px);
            background: rgba(7, 17, 31, 0.72);
            border-bottom: 1px solid var(--line);
        }
        .site-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 0;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        .brand__mark {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 18px;
            display: inline-grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: #02111f;
            font-weight: 900;
            box-shadow: var(--shadow);
        }
        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }
        .nav a {
            padding: 0.72rem 1rem;
            border-radius: 999px;
            border: 1px solid transparent;
            color: var(--muted);
            transition: 0.2s ease;
        }
        .nav a:hover,
        .nav a.is-active {
            color: var(--text);
            background: var(--panel);
            border-color: var(--line);
        }
        .page {
            padding: 2rem 0 4rem;
        }
        .hero {
            padding: 2rem;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, rgba(255,255,255,0.09), rgba(255,255,255,0.05));
            border-radius: calc(var(--radius) + 6px);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .hero h1 {
            margin: 0 0 0.9rem;
            font-size: clamp(2rem, 4vw, 3.7rem);
            line-height: 1.03;
        }
        .hero p {
            margin: 0;
            max-width: 72ch;
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.7;
        }
        .hero__actions {
            margin-top: 1.4rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding: 0.9rem 1.1rem;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.08);
            color: var(--text);
            font-weight: 700;
            transition: 0.2s ease;
        }
        .button:hover { transform: translateY(-1px); background: rgba(255, 255, 255, 0.13); }
        .button--primary {
            background: linear-gradient(135deg, var(--brand), #5da8ff);
            color: #04111d;
            border-color: transparent;
        }
        .section {
            margin-top: 1.6rem;
        }
        .section__heading {
            margin-bottom: 1rem;
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 1rem;
        }
        .section__heading h2, .section__heading h3 {
            margin: 0;
            font-size: clamp(1.3rem, 2vw, 1.9rem);
        }
        .section__heading p {
            margin: 0.35rem 0 0;
            color: var(--muted);
        }
        .grid {
            display: grid;
            gap: 1rem;
        }
        .grid--cards { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .grid--wide { grid-template-columns: 1.2fr 0.8fr; }
        .card {
            padding: 1.2rem;
            border-radius: var(--radius);
            border: 1px solid var(--line);
            background: var(--panel);
            box-shadow: var(--shadow);
        }
        .card h3, .card h4 { margin: 0 0 0.6rem; }
        .eyebrow {
            display: inline-flex;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: rgba(79, 209, 255, 0.12);
            color: var(--brand);
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 0.9rem;
            font-size: 0.85rem;
        }
        .metric {
            font-size: clamp(1.7rem, 3vw, 2.6rem);
            font-weight: 800;
            margin: 0.3rem 0 0.2rem;
        }
        .muted { color: var(--muted); }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .badge--up { background: rgba(255, 139, 139, 0.14); color: var(--danger); }
        .badge--down { background: rgba(120, 240, 165, 0.14); color: var(--success); }
        .badge--flat { background: rgba(255, 209, 102, 0.14); color: var(--warning); }
        .price-marker {
            display: inline-flex;
            align-items: flex-end;
            gap: 0.22rem;
            padding: 0.55rem 0.75rem 0.5rem;
            border-radius: 18px;
            background: linear-gradient(180deg, #17110d 0%, #090604 100%);
            border: 1px solid rgba(255, 209, 102, 0.32);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.08),
                0 14px 26px rgba(0, 0, 0, 0.28);
            color: #ffe39b;
            white-space: nowrap;
        }
        .price-marker__digits {
            display: inline-flex;
            align-items: flex-end;
            gap: 0.12rem;
        }
        .price-marker__digit {
            min-width: 0.82em;
            height: 1.55em;
            display: inline-grid;
            place-items: center;
            padding: 0 0.08em;
            border-radius: 10px;
            border: 1px solid rgba(255, 209, 102, 0.2);
            background: linear-gradient(180deg, #2a1b12 0%, #120b07 100%);
            box-shadow:
                inset 0 -2px 0 rgba(0, 0, 0, 0.5),
                0 1px 0 rgba(255, 255, 255, 0.05);
            font-family: "Courier New", Courier, monospace;
            font-weight: 800;
            font-size: 1rem;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            transform-origin: center bottom;
            animation: priceFlip 700ms cubic-bezier(0.2, 0.7, 0.2, 1) both;
            animation-delay: calc(var(--digit-index) * 45ms);
        }
        .price-marker__separator {
            min-width: 0.34em;
            display: inline-grid;
            place-items: center;
            transform: translateY(0.06em);
            font-size: 1.15em;
            color: #ffd166;
            text-shadow: 0 0 10px rgba(255, 209, 102, 0.25);
        }
        .price-marker__unit {
            margin-left: 0.28rem;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #ffd166;
            opacity: 0.95;
        }
        .price-marker--compact {
            padding: 0.42rem 0.56rem 0.38rem;
            border-radius: 16px;
        }
        .price-marker--compact .price-marker__digit {
            min-width: 0.72em;
            height: 1.28em;
            font-size: 0.9rem;
        }
        .price-marker--compact .price-marker__unit {
            font-size: 0.68rem;
        }
        .price-marker--empty {
            color: var(--muted);
        }
        .price-marker__empty {
            font-size: 0.92rem;
            font-weight: 700;
        }
        .chart-panel {
            position: relative;
            height: 320px;
            margin-top: 1rem;
        }
        .chart-panel--sm { height: 260px; }
        .chart-panel canvas {
            width: 100% !important;
            height: 100% !important;
        }
        @keyframes priceFlip {
            from {
                opacity: 0.1;
                transform: perspective(700px) rotateX(84deg) translateY(0.4rem);
                filter: blur(1px);
            }
            to {
                opacity: 1;
                transform: perspective(700px) rotateX(0deg) translateY(0);
                filter: blur(0);
            }
        }
        .mini-list, .detail-list, .article-list { display: grid; gap: 0.75rem; }
        .mini-list__item, .article-list__item {
            padding: 0.95rem 1rem;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.04);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.9rem;
        }
        label { display: grid; gap: 0.4rem; font-weight: 600; }
        input, select {
            width: 100%;
            padding: 0.85rem 0.95rem;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(9, 18, 33, 0.92);
            color: var(--text);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.96rem;
        }
        th, td {
            padding: 0.95rem 0.75rem;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }
        th { color: var(--muted); font-weight: 700; }
        .table-wrap { overflow-x: auto; }
        .notice {
            padding: 1rem 1.1rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 209, 102, 0.22);
            background: rgba(255, 209, 102, 0.1);
            color: #ffe6a1;
        }
        .footer {
            margin-top: 2.2rem;
            padding-top: 1.2rem;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 0.95rem;
        }
        .article h2, .article h3 { margin-top: 0; }
        .article p, .article li { color: var(--muted); line-height: 1.8; }
        .article ul { padding-left: 1.2rem; }
        .empty-state {
            text-align: center;
            padding: 2rem;
            border-radius: var(--radius);
            border: 1px dashed var(--line);
            color: var(--muted);
        }
        @media (max-width: 900px) {
            .grid--wide { grid-template-columns: 1fr; }
            .site-header__inner { flex-direction: column; align-items: flex-start; }
        }
    </style>
    @stack('head')
</head>
<body>
<header class="site-header">
    <div class="shell site-header__inner">
        <a class="brand" href="{{ route('prices.index') }}">
            <span class="brand__mark">⛽</span>
            <span>{{ config('app.name') }}</span>
        </a>

        <nav class="nav" aria-label="Principal">
            <a href="{{ route('prices.index') }}" class="{{ request()->routeIs('prices.index') ? 'is-active' : '' }}">Precios hoy</a>
            <a href="{{ route('prices.cheapest') }}" class="{{ request()->routeIs('prices.cheapest') ? 'is-active' : '' }}">Más baratas</a>
            <a href="{{ route('prices.historic') }}" class="{{ request()->routeIs('prices.historic') ? 'is-active' : '' }}">Histórico</a>
            <a href="{{ route('stations.search') }}" class="{{ request()->routeIs('stations.*') ? 'is-active' : '' }}">Gasolineras</a>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'is-active' : '' }}">Informes</a>
        </nav>
    </div>
</header>

<main class="page">
    <div class="shell">
        @yield('content')

        <footer class="footer">
            <p>Fuente principal prevista: Geoportal Gasolineras y catálogo de datos abiertos del Ministerio. Actualización programada cada día a las 07:00, con soporte para importación manual bajo token.</p>
        </footer>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
    window.gasFormatPrice = (value) => {
        const parsed = Number(value);

        if (Number.isNaN(parsed)) {
            return 'Sin dato';
        }

        return `${new Intl.NumberFormat('es-ES', {
            minimumFractionDigits: 3,
            maximumFractionDigits: 3,
        }).format(parsed)} €/l`;
    };

    window.renderGasChart = (canvasId, chartData) => {
        const canvas = document.getElementById(canvasId);

        if (!canvas || !window.Chart) {
            return null;
        }

        return new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#b8c3d9',
                            usePointStyle: true,
                            pointStyle: 'circle',
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${window.gasFormatPrice(context.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: { color: '#b8c3d9' },
                        grid: { color: 'rgba(255, 255, 255, 0.06)' },
                    },
                    y: {
                        ticks: {
                            color: '#b8c3d9',
                            callback: (value) => window.gasFormatPrice(value),
                        },
                        grid: { color: 'rgba(255, 255, 255, 0.06)' },
                    },
                },
            },
        });
    };

</script>

@stack('scripts')
</body>
</html>
