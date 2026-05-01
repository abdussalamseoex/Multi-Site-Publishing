<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontendController;

Route::get('/', [FrontendController::class, 'index'])->name('home');

Route::get('/p/{slug}', [FrontendController::class, 'page'])->name('frontend.page');
Route::get('/category/{slug}', [FrontendController::class, 'category'])->name('frontend.category');
Route::get('/search', [FrontendController::class, 'search'])->name('frontend.search');

Route::get('/dashboard', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    
    if ($user->role === 'admin') {
        $stats = [
            'posts_count' => \App\Models\Post::count(),
            'posts_views' => \App\Models\Post::sum('views'),
            'users_count' => \App\Models\User::count(),
            'categories_count' => \App\Models\Category::count(),
            'visits_today' => \App\Models\Visit::whereDate('created_at', today())->count(),
            'live_visitors' => \App\Models\Visit::where('created_at', '>=', now()->subMinutes(5))->distinct('ip_address')->count('ip_address'),
            'total_visits' => \App\Models\Visit::count()
        ];
        $recent_posts = \App\Models\Post::with('category')->latest()->take(5)->get();
    } else {
        $stats = [
            'posts_count' => \App\Models\Post::where('user_id', $user->id)->count(),
            'posts_views' => \App\Models\Post::where('user_id', $user->id)->sum('views'),
            'approved_posts' => \App\Models\Post::where('user_id', $user->id)->where('status', 'published')->count(),
            'pending_posts' => \App\Models\Post::where('user_id', $user->id)->where('status', 'pending')->count()
        ];
        $recent_posts = \App\Models\Post::where('user_id', $user->id)->with('category')->latest()->take(5)->get();
    }

    return view('dashboard', compact('stats', 'recent_posts'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Points Topup
    Route::get('/topup', [\App\Http\Controllers\User\TopupController::class, 'index'])->name('user.topup');
    Route::post('/topup', [\App\Http\Controllers\User\TopupController::class, 'store'])->name('user.topup.store');

    // Posts & Guest Post Checkouts
    Route::resource('posts', \App\Http\Controllers\PostController::class);
    Route::get('orders/{post}/checkout', [\App\Http\Controllers\OrderController::class, 'checkout'])->name('orders.checkout');
    Route::post('orders/{post}/process', [\App\Http\Controllers\OrderController::class, 'process'])->name('orders.process');
});

Route::get('/sitemap.xml', [\App\Http\Controllers\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\SeoController::class, 'robots'])->name('robots');

// Pseudo-Cron for background tasks without cPanel (publicly accessible)
Route::get('/system/pseudo-cron', [\App\Http\Controllers\Admin\SettingController::class, 'pseudoCron'])->name('pseudo.cron');

// Diagnostic tool for news fetcher (admin only, temporary)
Route::get('/system/news-diagnostic', function () {
    if (!auth()->check() || auth()->user()->role !== 'admin') abort(403);

    $urls = [
        'BBC World (HTTP)'  => 'http://feeds.bbci.co.uk/news/world/rss.xml',
        'CNN Top Stories'   => 'http://rss.cnn.com/rss/edition.rss',
        'NYT World (HTTPS)' => 'https://rss.nytimes.com/services/xml/rss/nyt/World.xml',
        'Guardian (HTTPS)'  => 'https://www.theguardian.com/world/rss',
        'AlJazeera (HTTPS)' => 'https://www.aljazeera.com/xml/rss/all.xml',
    ];

    $results = [];
    foreach ($urls as $name => $url) {
        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept'     => 'application/xml,text/xml,*/*',
                ])->timeout(10)->get($url);

            $status  = $response->status();
            $length  = strlen($response->body());
            $isRss   = str_contains($response->body(), '<rss') || str_contains($response->body(), '<feed');
            $xml     = $isRss ? @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA) : false;
            $items   = 0;
            if ($xml && isset($xml->channel->item)) $items = count($xml->channel->item);
            elseif ($xml && isset($xml->entry))      $items = count($xml->entry);

            $results[$name] = [
                'url'     => $url,
                'status'  => $status,
                'ok'      => $response->successful(),
                'length'  => $length,
                'is_rss'  => $isRss,
                'items'   => $items,
                'error'   => null,
            ];
        } catch (\Exception $e) {
            $results[$name] = [
                'url'    => $url,
                'status' => 'Exception',
                'ok'     => false,
                'length' => 0,
                'is_rss' => false,
                'items'  => 0,
                'error'  => $e->getMessage(),
            ];
        }
    }

    $html = '<html><head><title>News Diagnostic</title><style>
        body{font-family:monospace;padding:20px;background:#111;color:#eee}
        table{width:100%;border-collapse:collapse;margin-top:20px}
        th,td{border:1px solid #444;padding:10px;text-align:left}
        th{background:#222}.ok{color:#4ade80}.fail{color:#f87171}
        .label{color:#94a3b8;font-size:12px}
    </style></head><body>';
    $html .= '<h2 style="color:#60a5fa">📡 Auto News Fetcher Diagnostic</h2>';
    $html .= '<p style="color:#94a3b8">Server: ' . php_uname('n') . ' | PHP: ' . PHP_VERSION . ' | Time: ' . now() . '</p>';
    $html .= '<table><tr><th>Source</th><th>Status</th><th>Items Found</th><th>Details</th></tr>';
    foreach ($results as $name => $r) {
        $statusClass = $r['ok'] ? 'ok' : 'fail';
        $icon        = $r['ok'] ? '✅' : '❌';
        $detail      = $r['error'] ?? ($r['is_rss'] ? "RSS OK, {$r['length']} bytes" : "Not RSS ({$r['length']} bytes)");
        $html .= "<tr>
            <td><strong>{$name}</strong><br><span class='label'>{$r['url']}</span></td>
            <td class='{$statusClass}'>{$icon} {$r['status']}</td>
            <td class='{$statusClass}'><strong>{$r['items']}</strong></td>
            <td class='label'>{$detail}</td>
        </tr>";
    }
    $html .= '</table>';

    // Also check OpenAI key
    $openaiKey = \App\Models\Setting::get('openai_api_key');
    $html .= '<h3 style="color:#60a5fa;margin-top:30px">🔑 OpenAI API Key</h3>';
    $html .= $openaiKey ? '<p class="ok">✅ Set (' . strlen($openaiKey) . ' chars)</p>' : '<p class="fail">❌ NOT SET — This is why posts are not generating!</p>';

    $html .= '</body></html>';
    return response($html);
})->middleware('auth');

require __DIR__.'/auth.php';

// Admin Routes
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ThemeOptionsController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/seo', [SettingController::class, 'seoIndex'])->name('settings.seo');
    Route::get('/settings/limits', [SettingController::class, 'limitsIndex'])->name('settings.limits');
    Route::get('/settings/social', [SettingController::class, 'socialIndex'])->name('settings.social');
    Route::get('/settings/ads', [SettingController::class, 'adsIndex'])->name('settings.ads');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    
    // Theme Options / Homepage Builder
    Route::get('/theme-options', [ThemeOptionsController::class, 'index'])->name('theme.options');
    Route::post('/theme-options', [ThemeOptionsController::class, 'store'])->name('theme.options.store');
    Route::get('/theme-options/reset', [ThemeOptionsController::class, 'reset'])->name('theme.options.reset');

    Route::get('/seo', [SettingController::class, 'seoIndex'])->name('seo.index');
    Route::post('/seo', [SettingController::class, 'store'])->name('seo.store');

    Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/api-stats', [\App\Http\Controllers\Admin\AnalyticsController::class, 'apiStats'])->name('analytics.api');
    Route::get('/analytics/export', [\App\Http\Controllers\Admin\AnalyticsController::class, 'export'])->name('analytics.export');
    Route::post('/analytics/block-ip', [\App\Http\Controllers\Admin\AnalyticsController::class, 'blockIp'])->name('analytics.blockIp');
    Route::post('/analytics/unblock-ip', [\App\Http\Controllers\Admin\AnalyticsController::class, 'unblockIp'])->name('analytics.unblockIp');

    Route::get('/demo-import', [SettingController::class, 'importDemo'])->name('demo.import');
    
    Route::get('/import/wordpress', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('import.wordpress.index');
    Route::post('/import/wordpress/upload', [\App\Http\Controllers\Admin\ImportController::class, 'upload'])->name('import.wordpress.upload');
    Route::post('/import/wordpress/process', [\App\Http\Controllers\Admin\ImportController::class, 'processChunk'])->name('import.wordpress.process');

    Route::get('/update', [\App\Http\Controllers\Admin\UpdateController::class, 'index'])->name('update.index');
    Route::post('/update', [\App\Http\Controllers\Admin\UpdateController::class, 'process'])->name('update.process');

    // AI Automation
    Route::get('/ai-writer', [\App\Http\Controllers\Admin\AIWriterController::class, 'index'])->name('ai-writer.index');
    Route::post('/ai-writer/generate', [\App\Http\Controllers\Admin\AIWriterController::class, 'generate'])->name('ai-writer.generate');
    Route::post('/ai-writer/bulk-start', [\App\Http\Controllers\Admin\AIWriterController::class, 'startBulkCampaign'])->name('ai-writer.bulk-start');
    Route::get('/ai-writer/campaigns', [\App\Http\Controllers\Admin\AIWriterController::class, 'campaigns'])->name('ai-writer.campaigns');
    Route::post('/ai-writer/campaigns/{id}/toggle', [\App\Http\Controllers\Admin\AIWriterController::class, 'toggleBulkCampaign'])->name('ai-writer.campaigns.toggle');
    Route::delete('/ai-writer/campaigns/{id}', [\App\Http\Controllers\Admin\AIWriterController::class, 'deleteBulkCampaign'])->name('ai-writer.campaigns.delete');
    
    Route::get('/ai-writer/news', [\App\Http\Controllers\Admin\AutoNewsController::class, 'index'])->name('ai-writer.news');
    Route::get('/ai-writer/news/logs', [\App\Http\Controllers\Admin\AutoNewsController::class, 'logs'])->name('ai-writer.news.logs');
    Route::post('/ai-writer/news', [\App\Http\Controllers\Admin\AutoNewsController::class, 'store'])->name('ai-writer.news.store');
    Route::delete('/ai-writer/news/{id}', [\App\Http\Controllers\Admin\AutoNewsController::class, 'destroy'])->name('ai-writer.news.destroy');
    Route::put('/ai-writer/news/{id}', [\App\Http\Controllers\Admin\AutoNewsController::class, 'update'])->name('ai-writer.news.update');
    Route::post('/ai-writer/news/{id}/fetch', [\App\Http\Controllers\Admin\AutoNewsController::class, 'fetchNow'])->name('ai-writer.news.fetch');
    Route::post('/ai-writer/news/import-predefined', [\App\Http\Controllers\Admin\AutoNewsController::class, 'importPredefinedSources'])->name('ai-writer.news.import-predefined');
    Route::post('/ai-writer/news/import-bbc', [\App\Http\Controllers\Admin\AutoNewsController::class, 'importBBCSources'])->name('ai-writer.news.import-bbc');
    Route::post('/ai-writer/news/import-crypto', [\App\Http\Controllers\Admin\AutoNewsController::class, 'importCryptoSources'])->name('ai-writer.news.import-crypto');
    Route::post('/ai-writer/news/import-authors', [\App\Http\Controllers\Admin\AutoNewsController::class, 'importAuthors'])->name('ai-writer.news.import-authors');
    Route::post('/ai-writer/news/bulk-action', [\App\Models\AutoNewsSource::class == \App\Http\Controllers\Admin\AutoNewsController::class ? '' : \App\Http\Controllers\Admin\AutoNewsController::class, 'bulkAction'])->name('ai-writer.news.bulk-action');
    Route::post('/ai-writer/news/bulk_destroy', [\App\Http\Controllers\Admin\AutoNewsController::class, 'bulkAction']); // Keep for compatibility if needed
    Route::get('/ai-writer/news/bulk-destroy', function() { return redirect()->route('admin.ai-writer.news'); });
    Route::get('/ai-writer/news/bulk_destroy', function() { return redirect()->route('admin.ai-writer.news'); });

    Route::get('/ai-writer/settings', [\App\Http\Controllers\Admin\AIWriterController::class, 'settings'])->name('ai-writer.settings');
    Route::post('/ai-writer/settings', [\App\Http\Controllers\Admin\AIWriterController::class, 'storeSettings'])->name('ai-writer.settings.store');

    Route::post('/posts/bulk-action', [AdminPostController::class, 'bulkAction'])->name('posts.bulk-action');
    Route::resource('/posts', AdminPostController::class)->except(['show']);
    Route::post('/posts/{post}/status', [AdminPostController::class, 'updateStatus'])->name('posts.status');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    
    Route::post('/system/migrate', [\App\Http\Controllers\Admin\SettingController::class, 'runMigration'])->name('system.migrate');

    // Point Top-up Requests
    Route::get('/topup-requests', [\App\Http\Controllers\Admin\TopupRequestController::class, 'index'])->name('topup.requests');
    Route::patch('/topup-requests/{topupRequest}', [\App\Http\Controllers\Admin\TopupRequestController::class, 'update'])->name('topup.update');

    Route::get('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menus.index');
    Route::post('/menus/{menu}/items', [\App\Http\Controllers\Admin\MenuController::class, 'storeItem'])->name('menus.items.store');
    Route::post('/menus/{menu}/import-categories', [\App\Http\Controllers\Admin\MenuController::class, 'importCategories'])->name('menus.import_categories');
    Route::delete('/menus/items/bulk', [\App\Http\Controllers\Admin\MenuController::class, 'bulkDeleteItems'])->name('menus.items.bulk_destroy');
    Route::delete('/menus/items/{item}', [\App\Http\Controllers\Admin\MenuController::class, 'deleteItem'])->name('menus.items.destroy');
    Route::post('/menus/reorder', [\App\Http\Controllers\Admin\MenuController::class, 'reorder'])->name('menus.reorder');

    Route::get('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/bulk-import', [\App\Http\Controllers\Admin\CategoryController::class, 'bulkImport'])->name('categories.bulk_import');
    Route::post('/categories/bulk-destroy', [\App\Http\Controllers\Admin\CategoryController::class, 'bulkDestroy'])->name('categories.bulk_destroy');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::post('/users/bulk-action', [\App\Http\Controllers\Admin\UserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.role');
    Route::patch('/users/{user}/toggle-ban', [\App\Http\Controllers\Admin\UserController::class, 'toggleBan'])->name('users.toggle-ban');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/limits', [\App\Http\Controllers\Admin\UserController::class, 'updateLimits'])->name('users.limits');

    Route::resource('/pages', \App\Http\Controllers\Admin\PageController::class)->except(['show']);
});

// Setup Wizard Routes
use App\Http\Controllers\InstallController;

Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::post('/install/database', [InstallController::class, 'processDatabase'])->name('install.processDatabase');
Route::get('/install/step2', [InstallController::class, 'step2'])->name('install.step2');
Route::post('/install/process', [InstallController::class, 'processInstallation'])->name('install.processInstallation');

// Catch-all route for Posts (Must remain at the absolute bottom)
Route::get('/{slug}', [FrontendController::class, 'showPost'])->name('frontend.post');
