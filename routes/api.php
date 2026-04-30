<?php

use App\Http\Controllers\Api\StationPriceController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:12,1')->group(function () {
    Route::post('/update-prices', [ImportController::class, 'updatePrices'])->name('api.prices.update');
    Route::get('/stations/{station}/prices', [StationPriceController::class, 'show'])->name('api.stations.prices.show');
});
