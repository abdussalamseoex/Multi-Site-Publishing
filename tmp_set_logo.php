<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\App\Models\Setting::updateOrCreate(['key'=>'site_logo'],['value'=>'storage/logos/logo.png']);
echo "Logo updated successfully.\n";
