<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = collect(config('reports'))
            ->sortByDesc('published_at')
            ->values();

        return view('reports.index', [
            'reports' => $reports,
            'metaTitle' => 'Noticias e informes sobre carburantes',
            'metaDescription' => 'Lee informes, noticias y análisis sobre el precio de la gasolina, el diésel y los carburantes alternativos en España.',
        ]);
    }

    public function show(string $slug): View
    {
        $report = collect(config('reports'))->firstWhere('slug', $slug);

        abort_unless($report, 404);

        return view('reports.show', [
            'report' => $report,
            'publishedAt' => Carbon::parse($report['published_at']),
            'metaTitle' => $report['title'],
            'metaDescription' => $report['excerpt'],
        ]);
    }
}
