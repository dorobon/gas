<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/stations/catalogs', 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . PHP_EOL;
$content = (string) $response->getContent();
$decoded = json_decode($content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON decode error\n";
    echo $content . PHP_EOL;
    exit(1);
}

echo "Provinces: " . count($decoded['provinces'] ?? []) . PHP_EOL;
foreach (array_slice($decoded['provinces'] ?? [], 0, 20) as $p) {
    echo " - $p\n";
}

$kernel->terminate($request, $response);
