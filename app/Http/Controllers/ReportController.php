<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        $reports = collect(config('reports'))
            ->sortByDesc('published_at')
            ->values();

        return response()->view('reports.index', [
            'reports' => $reports,
            'metaTitle' => 'Noticias e informes sobre carburantes',
            'metaDescription' => 'Lee informes, noticias y análisis sobre el precio de la gasolina, el diésel y los carburantes alternativos en España.',
        ], 200, [
            'Cache-Control' => 'public, max-age=3600, s-maxage=3600, stale-while-revalidate=86400',
        ]);
    }

    public function show(string $slug): Response
    {
        $report = collect(config('reports'))->firstWhere('slug', $slug);

        abort_unless($report, 404);

        $publishedAt = Carbon::parse($report['published_at']);

        return response()->view('reports.show', [
            'report' => $report,
            'publishedAt' => $publishedAt,
            'metaTitle' => $report['title'],
            'metaDescription' => $report['excerpt'],
        ], 200, [
            'Cache-Control' => 'public, max-age=21600, s-maxage=21600, stale-while-revalidate=86400',
            'Last-Modified' => $publishedAt->toRfc7231String(),
        ]);
    }
}
