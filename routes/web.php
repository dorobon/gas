<?php

use App\Http\Controllers\PriceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
	$urls = collect([
		[
			'loc' => route('prices.index'),
			'lastmod' => now()->toDateString(),
			'changefreq' => 'daily',
			'priority' => '1.0',
		],
		[
			'loc' => route('prices.cheapest'),
			'lastmod' => now()->toDateString(),
			'changefreq' => 'daily',
			'priority' => '0.9',
		],
		[
			'loc' => route('prices.historic'),
			'lastmod' => now()->toDateString(),
			'changefreq' => 'daily',
			'priority' => '0.9',
		],
		[
			'loc' => route('stations.search'),
			'lastmod' => now()->toDateString(),
			'changefreq' => 'daily',
			'priority' => '0.8',
		],
		[
			'loc' => route('reports.index'),
			'lastmod' => now()->toDateString(),
			'changefreq' => 'weekly',
			'priority' => '0.7',
		],
	])->merge(
		collect(config('reports'))->map(fn (array $report) => [
			'loc' => route('reports.show', $report['slug']),
			'lastmod' => Carbon::parse($report['published_at'])->toDateString(),
			'changefreq' => 'monthly',
			'priority' => '0.6',
		])
	);

	$xml = collect([
		'<?xml version="1.0" encoding="UTF-8"?>',
		'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
	])
		->merge($urls->map(function (array $entry) {
			return implode('', [
				'<url>',
				'<loc>', e($entry['loc']), '</loc>',
				'<lastmod>', e($entry['lastmod']), '</lastmod>',
				'<changefreq>', e($entry['changefreq']), '</changefreq>',
				'<priority>', e($entry['priority']), '</priority>',
				'</url>',
			]);
		}))
		->push('</urlset>')
		->implode('');

	return response()
		->make($xml)
		->header('Content-Type', 'application/xml');
})->name('seo.sitemap');

Route::get('/', [PriceController::class, 'index'])->name('prices.index');
Route::get('/baratas', [PriceController::class, 'cheapest'])->name('prices.cheapest');
Route::get('/historico', [PriceController::class, 'historic'])->name('prices.historic');

Route::get('/gasolineras', [StationController::class, 'search'])->name('stations.search');
Route::get('/gasolineras/{station}/{slug?}', [StationController::class, 'show'])->name('stations.show');

Route::get('/informes', [ReportController::class, 'index'])->name('reports.index');
Route::get('/informes/{slug}', [ReportController::class, 'show'])->name('reports.show');
