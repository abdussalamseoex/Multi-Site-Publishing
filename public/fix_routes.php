<?php
/**
 * =====================================================
 * EMERGENCY FIX SCRIPT - Run Once Then Delete!
 * Upload this file to: public/fix_routes.php
 * Run by visiting: https://bignewsreporter.com/fix_routes.php
 * DELETE THIS FILE IMMEDIATELY AFTER USE!
 * =====================================================
 */

// Security check - only allow from specific IP or add your own password
$allowed = ['103.151.30.224', '127.0.0.1', '::1'];
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowed)) {
    // Simple password fallback
    if (!isset($_GET['key']) || $_GET['key'] !== 'fix2026') {
        die('Access Denied. Use: ?key=fix2026');
    }
}

$basePath = dirname(__DIR__);
$results = [];

// Bootstrap Laravel
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear all caches
$commands = [
    'optimize:clear',
    'route:clear',
    'config:clear',
    'view:clear',
    'cache:clear',
];

foreach ($commands as $cmd) {
    try {
        Illuminate\Support\Facades\Artisan::call($cmd);
        $results[] = "✅ {$cmd} — Done";
    } catch (Exception $e) {
        $results[] = "❌ {$cmd} — " . $e->getMessage();
    }
}

// Show results
echo '<!DOCTYPE html><html><head><title>Fix Script</title>';
echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:40px;} .ok{color:#4ade80;} .err{color:#f87171;} h1{color:#60a5fa;}</style>';
echo '</head><body>';
echo '<h1>🔧 Emergency Route Fix</h1>';
echo '<p style="color:#94a3b8">Server: ' . php_uname('n') . ' | PHP: ' . PHP_VERSION . '</p>';
echo '<ul>';
foreach ($results as $r) {
    echo '<li>' . $r . '</li>';
}
echo '</ul>';
echo '<p style="color:#fbbf24;font-weight:bold;margin-top:30px;">⚠️ IMPORTANT: Delete this file from your server immediately!</p>';
echo '<p style="color:#4ade80;">✅ Done! Now try the Bulk Delete button again. Go back to <a href="/admin/ai-writer/news" style="color:#60a5fa">Admin Panel</a></p>';
echo '</body></html>';
