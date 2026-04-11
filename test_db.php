<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$posts = \App\Models\Post::where('title', 'like', '%Xbox%')->get();
foreach($posts as $p) {
    echo "ID: " . $p->id . "\n";
    echo "URL: " . $p->featured_image . "\n";
}
