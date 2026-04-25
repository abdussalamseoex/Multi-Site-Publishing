<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$settings = \App\Models\Setting::where('key', 'like', 'theme_blocks_%')->get();
foreach ($settings as $setting) {
    $blocks = json_decode($setting->value, true);
    if (is_array($blocks)) {
        $filtered = array_filter($blocks, function($block) {
            return isset($block['type']) && $block['type'] !== 'legacy_theme_content';
        });
        
        // If it becomes empty, we should just delete the setting so the theme defaults take over!
        if (empty($filtered)) {
            $setting->delete();
            echo "Deleted setting {$setting->key} because it became empty.\n";
        } else if (count($filtered) !== count($blocks)) {
            $setting->value = json_encode(array_values($filtered));
            $setting->save();
            echo "Updated setting {$setting->key} (removed legacy_theme_content).\n";
        }
    }
}
echo "Done.\n";
