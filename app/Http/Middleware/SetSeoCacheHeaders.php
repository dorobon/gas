<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSeoCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->routeIs('prices.index', 'prices.cheapest', 'prices.historic', 'stations.search', 'stations.show')) {
            $response->headers->set('Cache-Control', 'public, max-age=900, s-maxage=900, stale-while-revalidate=3600');
        }

        if ($request->routeIs('seo.sitemap')) {
            $response->headers->set('Cache-Control', 'public, max-age=3600, s-maxage=3600, stale-while-revalidate=86400');
        }

        return $response;
    }
}
