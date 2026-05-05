<?php

namespace App\Providers;

use App\Libraries\Fuel\FuelDataBootstrapLibrary;
use App\Repositories\Contracts\GasStationRepositoryInterface;
use App\Repositories\Contracts\PriceRepositoryInterface;
use App\Repositories\Eloquent\GasStationRepository;
use App\Repositories\Eloquent\PriceRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GasStationRepositoryInterface::class, GasStationRepository::class);
        $this->app->bind(PriceRepositoryInterface::class, PriceRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('database.default') === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
            return;
        }

        try {
            $this->app->make(FuelDataBootstrapLibrary::class)->ensureReady();
        } catch (\Throwable $e) {
            // In some test environments the PDO sqlite driver may be unavailable
            // or misconfigured. Don't break request lifecycle because of boot-time
            // DB issues; log and continue so tests can render pages that don't
            // strictly require DB access.
            logger()->warning('FuelDataBootstrapLibrary not ready: '.$e->getMessage());
        }
    }
}
