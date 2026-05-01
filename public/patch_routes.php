<?php
/**
 * =====================================================
 * EMERGENCY PATCH SCRIPT v2 - Patches routes/web.php directly
 * Upload to: public/patch_routes.php
 * Run: https://bignewsreporter.com/patch_routes.php?key=fix2026
 * DELETE AFTER USE!
 * =====================================================
 */

// Security check
if (!isset($_GET['key']) || $_GET['key'] !== 'fix2026') {
    die('Access Denied. Use: ?key=fix2026');
}

$results = [];
$webRoutesPath = dirname(__DIR__) . '/routes/web.php';

// Read the current routes file
$currentContent = file_get_contents($webRoutesPath);

// Check if bulk-destroy route already exists
if (strpos($currentContent, 'bulk-destroy') !== false) {
    $results[] = "ℹ️ bulk-destroy route already exists in routes/web.php";
} else {
    // Find the line with 'ai-writer.news.fetch' and insert after it
    $searchFor = "Route::post('/ai-writer/news/{id}/fetch'";
    
    $newRoutes = "\n    Route::post('/ai-writer/news/bulk-destroy', [\\App\\Http\\Controllers\\Admin\\AutoNewsController::class, 'bulkDestroy'])->name('ai-writer.news.bulk-destroy');\n    Route::post('/ai-writer/news/bulk_destroy', [\\App\\Http\\Controllers\\Admin\\AutoNewsController::class, 'bulkDestroy']);\n    Route::get('/ai-writer/news/bulk-destroy', function() { return redirect()->route('admin.ai-writer.news'); });\n    Route::get('/ai-writer/news/bulk_destroy', function() { return redirect()->route('admin.ai-writer.news'); });\n    Route::post('/ai-writer/news/import-predefined', [\\App\\Http\\Controllers\\Admin\\AutoNewsController::class, 'importPredefinedSources'])->name('ai-writer.news.import-predefined');\n    Route::post('/ai-writer/news/import-authors', [\\App\\Http\\Controllers\\Admin\\AutoNewsController::class, 'importAuthors'])->name('ai-writer.news.import-authors');";

    // Check what currently comes after the fetch route
    $hasImportRoutes = strpos($currentContent, 'import-predefined') !== false;
    
    if ($hasImportRoutes) {
        // Only add bulk-destroy routes
        $newRoutes = "\n    Route::post('/ai-writer/news/bulk-destroy', [\\App\\Http\\Controllers\\Admin\\AutoNewsController::class, 'bulkDestroy'])->name('ai-writer.news.bulk-destroy');\n    Route::post('/ai-writer/news/bulk_destroy', [\\App\\Http\\Controllers\\Admin\\AutoNewsController::class, 'bulkDestroy']);\n    Route::get('/ai-writer/news/bulk-destroy', function() { return redirect()->route('admin.ai-writer.news'); });\n    Route::get('/ai-writer/news/bulk_destroy', function() { return redirect()->route('admin.ai-writer.news'); });";
    }
    
    // Insert after the fetch route line
    $insertAfter = "->name('ai-writer.news.fetch');";
    $position = strpos($currentContent, $insertAfter);
    
    if ($position !== false) {
        $newContent = substr($currentContent, 0, $position + strlen($insertAfter)) . $newRoutes . substr($currentContent, $position + strlen($insertAfter));
        
        if (file_put_contents($webRoutesPath, $newContent)) {
            $results[] = "✅ routes/web.php patched successfully! bulk-destroy routes added.";
        } else {
            $results[] = "❌ Failed to write routes/web.php - check file permissions (chmod 644)";
        }
    } else {
        $results[] = "❌ Could not find insertion point in routes/web.php";
        $results[] = "ℹ️ Manual upload required.";
    }
}

// Also check and patch AutoNewsController
$controllerPath = dirname(__DIR__) . '/app/Http/Controllers/Admin/AutoNewsController.php';
$controllerContent = file_get_contents($controllerPath);

if (strpos($controllerContent, 'bulkDestroy') !== false) {
    $results[] = "ℹ️ bulkDestroy method already exists in AutoNewsController";
} else {
    // Add the bulkDestroy method before the last closing brace
    $bulkDestroyMethod = "\n\n    /**\n     * Bulk delete selected news sources\n     */\n    public function bulkDestroy(Request \$request)\n    {\n        \$ids = \$request->input('ids', []);\n        \n        if (empty(\$ids)) {\n            return back()->withErrors(['error' => 'No sources selected for deletion.']);\n        }\n\n        \\App\\Models\\AutoNewsSource::whereIn('id', \$ids)->delete();\n\n        return back()->with('status', count(\$ids) . ' news sources deleted successfully.');\n    }\n";
    
    // Insert before last closing brace
    $lastBrace = strrpos($controllerContent, '}');
    if ($lastBrace !== false) {
        $newControllerContent = substr($controllerContent, 0, $lastBrace) . $bulkDestroyMethod . '}' . PHP_EOL;
        if (file_put_contents($controllerPath, $newControllerContent)) {
            $results[] = "✅ AutoNewsController.php patched! bulkDestroy method added.";
        } else {
            $results[] = "❌ Failed to write AutoNewsController.php - check permissions";
        }
    }
}

// Now clear caches via Laravel
$basePath = dirname(__DIR__);
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$commands = ['route:clear', 'config:clear', 'view:clear', 'cache:clear'];
foreach ($commands as $cmd) {
    try {
        Illuminate\Support\Facades\Artisan::call($cmd);
        $results[] = "✅ {$cmd} — Done";
    } catch (Exception $e) {
        $results[] = "❌ {$cmd} — " . $e->getMessage();
    }
}

// Show results
echo '<!DOCTYPE html><html><head><title>Patch Script v2</title>';
echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:40px;} h1{color:#60a5fa;} li{margin:6px 0;}</style>';
echo '</head><body>';
echo '<h1>🔧 Emergency Patch v2</h1>';
echo '<p style="color:#94a3b8">Server: ' . php_uname('n') . ' | PHP: ' . PHP_VERSION . '</p>';
echo '<ul>';
foreach ($results as $r) {
    echo '<li>' . $r . '</li>';
}
echo '</ul>';
echo '<p style="color:#fbbf24;font-weight:bold;margin-top:30px;">⚠️ Delete this file from server now!</p>';
echo '<p>Go back to <a href="/admin/ai-writer/news" style="color:#60a5fa">Admin Panel → Auto News</a> and try Bulk Delete again.</p>';
echo '</body></html>';
