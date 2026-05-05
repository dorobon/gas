<?php
// Script to count distinct provinces from GasStation model
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo (int) \App\Models\GasStation::query()->whereNotNull('province')->where('province', '!=', '')->distinct()->count() . PHP_EOL;
