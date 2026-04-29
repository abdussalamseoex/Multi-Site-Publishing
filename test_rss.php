<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$urls = [
    'bbc' => 'http://feeds.bbci.co.uk/news/world/rss.xml',
    'cnn' => 'http://rss.cnn.com/rss/edition.rss',
    'nyt' => 'https://rss.nytimes.com/services/xml/rss/nyt/World.xml',
    'guardian' => 'https://www.theguardian.com/world/rss',
    'aljazeera' => 'https://www.aljazeera.com/xml/rss/all.xml'
];

foreach ($urls as $name => $url) {
    echo "Fetching $name...\n";
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ])->timeout(15)->get($url);

    if (!$response->successful()) {
        echo "Failed to fetch $name: " . $response->status() . "\n";
        continue;
    }
    $content = $response->body();
    echo "Length: " . strlen($content) . "\n";
    
    $xml = @simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$xml) {
        echo "XML parse failed for $name. Errors:\n";
        print_r(libxml_get_errors());
    } else {
        if (isset($xml->channel->item)) {
            echo "Parsed RSS 2.0 for $name. Items: " . count($xml->channel->item) . "\n";
        } elseif (isset($xml->entry)) {
            echo "Parsed Atom for $name. Items: " . count($xml->entry) . "\n";
        } else {
            echo "Parsed XML but no items found for $name.\n";
        }
    }
    echo "\n";
}
