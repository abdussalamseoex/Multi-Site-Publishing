<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FrontendController;

Route::get('/', [FrontendController::class, 'index'])->name('home');

Route::get('/post/{slug}', [FrontendController::class, 'showPost'])->name('frontend.post');
Route::get('/p/{slug}', [FrontendController::class, 'page'])->name('frontend.page');
Route::get('/category/{slug}', [FrontendController::class, 'category'])->name('frontend.category');

Route::get('/dashboard', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    
    if ($user->role === 'admin') {
        $stats = [
            'posts_count' => \App\Models\Post::count(),
            'posts_views' => \App\Models\Post::sum('views'),
            'users_count' => \App\Models\User::count(),
            'categories_count' => \App\Models\Category::count()
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

    // Posts & Guest Post Checkouts
    Route::resource('posts', \App\Http\Controllers\PostController::class);
    Route::get('orders/{post}/checkout', [\App\Http\Controllers\OrderController::class, 'checkout'])->name('orders.checkout');
    Route::post('orders/{post}/process', [\App\Http\Controllers\OrderController::class, 'process'])->name('orders.process');
});

Route::get('/sitemap.xml', [\App\Http\Controllers\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\SeoController::class, 'robots'])->name('robots');

require __DIR__.'/auth.php';

// Admin Routes
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    
    Route::get('/seo', [SettingController::class, 'seoIndex'])->name('seo.index');
    Route::post('/seo', [SettingController::class, 'store'])->name('seo.store');

    Route::get('/demo-import', [SettingController::class, 'importDemo'])->name('demo.import');

    Route::get('/update', [\App\Http\Controllers\Admin\UpdateController::class, 'index'])->name('update.index');
    Route::post('/update', [\App\Http\Controllers\Admin\UpdateController::class, 'process'])->name('update.process');

    Route::resource('/posts', AdminPostController::class)->except(['show']);
    Route::post('/posts/{post}/status', [AdminPostController::class, 'updateStatus'])->name('posts.status');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menus.index');
    Route::post('/menus/{menu}/items', [\App\Http\Controllers\Admin\MenuController::class, 'storeItem'])->name('menus.items.store');
    Route::delete('/menus/items/{item}', [\App\Http\Controllers\Admin\MenuController::class, 'deleteItem'])->name('menus.items.destroy');
    Route::post('/menus/reorder', [\App\Http\Controllers\Admin\MenuController::class, 'reorder'])->name('menus.reorder');

    Route::get('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/bulk-import', [\App\Http\Controllers\Admin\CategoryController::class, 'bulkImport'])->name('categories.bulk_import');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.role');

    Route::resource('/pages', \App\Http\Controllers\Admin\PageController::class)->except(['show']);
});

// Setup Wizard Routes
use App\Http\Controllers\InstallController;

Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::post('/install/database', [InstallController::class, 'processDatabase'])->name('install.processDatabase');
Route::get('/install/step2', [InstallController::class, 'step2'])->name('install.step2');
Route::post('/install/process', [InstallController::class, 'processInstallation'])->name('install.processInstallation');
