<?php

$dir = __DIR__ . '/resources/views/themes/';
$themes = ['minimal', 'blog', 'news', 'magazine', 'vanguard', 'nexus', 'ledger', 'vitality', 'estate', 'voyage'];

$injection = <<<EOD


    @if(isset(\$isCategory) && \$isCategory)
    <div class="bg-slate-50/80 border-b border-slate-200" style="background-color: var(--bg-category, #f8fafc); border-bottom: 1px solid var(--border-category, #e2e8f0)">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-16 text-center">
            <span class="text-[10px] font-black uppercase tracking-widest text-primary mb-3 block opacity-80">Category</span>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4" style="color: var(--text-main, #0f172a)">{{ \$category->name }}</h1>
            @if(\$category->description)
                <p class="text-lg opacity-70 max-w-2xl mx-auto" style="color: var(--text-muted, #475569)">{{ \$category->description }}</p>
            @endif
        </div>
    </div>
    @endif

EOD;

foreach($themes as $theme) {
    if(file_exists($dir . $theme . '/home.blade.php')) {
        $c = file_get_contents($dir . $theme . '/home.blade.php');
        
        // Remove old injection if exists
        $c = preg_replace('/@if\(isset\(\$isCategory\).*?@endif/s', '', $c);
        
        // Inject after header
        $c = str_replace("@include('themes.components.header')", "@include('themes.components.header')" . $injection, $c);
        
        file_put_contents($dir . $theme . '/home.blade.php', $c);
        echo "Injected category header into $theme\n";
    }
}

// Now Update FrontendController!
$controllerFile = __DIR__ . '/app/Http/Controllers/FrontendController.php';
$controllerContent = file_get_contents($controllerFile);

$categoryMethod = <<<EOD
    public function category(\$slug)
    {
        \$category = \App\Models\Category::where('slug', \$slug)->firstOrFail();
        \$posts = Post::where('category_id', \$category->id)
                     ->where('status', 'published')
                     ->latest()
                     ->paginate(12);

        \$activeTheme = Setting::get('active_theme', 'minimal');
        \$viewName = "themes.{\$activeTheme}.category";

        if (view()->exists(\$viewName)) {
            return view(\$viewName, compact('category', 'posts'));
        }

        // Re-use current active theme's home layout as the category page
        \$homeView = "themes.{\$activeTheme}.home";
        if (view()->exists(\$homeView)) {
            \$featuredPosts = collect(); 
            \$latestPosts = \$posts;
            \$isCategory = true;
            return view(\$homeView, compact('category', 'latestPosts', 'featuredPosts', 'isCategory'));
        }

        return view('themes.category', compact('category', 'posts'));
    }
EOD;

// Replace the entire category method
$controllerContent = preg_replace('/public function category\(\$slug\).*?\n    \}\n/s', $categoryMethod . "\n", $controllerContent);
file_put_contents($controllerFile, $controllerContent);

echo "Updated FrontendController\n";
