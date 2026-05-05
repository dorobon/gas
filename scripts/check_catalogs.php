<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$repo = $app->make(App\Repositories\Eloquent\GasStationRepository::class);
$catalogs = $repo->getCatalogOptions(null);

echo "Provinces: " . count($catalogs['provinces']) . PHP_EOL;
echo "First 20 provinces:\n";
foreach (array_slice($catalogs['provinces'], 0, 20) as $p) {
    echo " - $p\n";
}

echo "\nMunicipalities sample: " . count($catalogs['municipalities']) . PHP_EOL;
echo "Brands sample: " . count($catalogs['brands']) . PHP_EOL;
