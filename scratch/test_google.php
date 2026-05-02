<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

$apiKey = Setting::get('google_search_api_key');
$cx = Setting::get('google_search_engine_id');

echo "Testing Google Search API...\n";
echo "Key: " . substr($apiKey, 0, 5) . "...\n";
echo "CX: " . substr($cx, 0, 5) . "...\n";

$response = Http::get("https://www.googleapis.com/customsearch/v1", [
    'key' => $apiKey,
    'cx' => $cx,
    'q' => 'test'
]);

if ($response->successful()) {
    echo "SUCCESS: Connection established.\n";
    $data = $response->json();
    echo "Found " . count($data['items'] ?? []) . " items.\n";
} else {
    echo "FAILED: " . $response->body() . "\n";
}
