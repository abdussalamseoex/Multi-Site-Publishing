<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$posts = \App\Models\Post::latest()->take(3)->get();
foreach($posts as $p) echo $p->title . " => " . $p->featured_image . "\n";
