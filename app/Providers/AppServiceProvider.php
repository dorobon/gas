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

        $this->app->make(FuelDataBootstrapLibrary::class)->ensureReady();
    }
}
