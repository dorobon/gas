<?php

use App\Http\Controllers\Api\GasStationCatalogController;
use App\Http\Controllers\Api\StationPriceController;
use App\Http\Controllers\Api\StationSocialCardController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:12,1')->group(function () {
    Route::post('/update-prices', [ImportController::class, 'updatePrices'])->name('api.prices.update');
    Route::get('/stations/catalogs', [GasStationCatalogController::class, 'index'])->name('api.stations.catalogs');
    Route::get('/stations/{station}/prices', [StationPriceController::class, 'show'])->name('api.stations.prices.show');
    Route::get('/social-cards', [StationSocialCardController::class, 'show'])->name('api.social-cards.show');
    Route::get('/social-cards/render.jpg', [StationSocialCardController::class, 'image'])->name('api.social-cards.image');
});
