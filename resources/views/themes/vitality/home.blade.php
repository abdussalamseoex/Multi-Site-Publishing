<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Vitality Wellness') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#10b981'); // emerald-500
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        .font-wellness { font-family: 'Outfit', sans-serif; }
        
        header { background-color: #ffffff !important; border-bottom: 1px solid #e2e8f0 !important; }
        header a { color: #475569 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
        header .bg-gray-900 { background-color: #f1f5f9 !important; color: #475569 !important; }

        .vitality-card { background: white; border-radius: 1.5rem; box-shadow: 0 10px 40px -10px rgba(16, 185, 129, 0.08); transition: all 0.3s ease; border: 1px solid #f1f5f9;}
        .vitality-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.2); }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased selection:bg-emerald-500 selection:text-white">

    @include('themes.components.header')

    @if(isset($isCategory) && $isCategory)
    <div class="bg-slate-50/80 border-b border-slate-200" style="background-color: var(--bg-category, #f8fafc); border-bottom: 1px solid var(--border-category, #e2e8f0)">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-16 text-center">
            <span class="text-[10px] font-black uppercase tracking-widest text-primary mb-3 block opacity-80">Category</span>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4" style="color: var(--text-main, #0f172a)">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-lg opacity-70 max-w-2xl mx-auto" style="color: var(--text-muted, #475569)">{{ $category->description }}</p>
            @endif
        </div>
    </div>
    @endif


    <div class="max-w-7xl mx-auto px-4 py-10">
        @php
            $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');
            $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            if (empty($blocks)) {
                $blocks = [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Top Stories', 'category_id' => null, 'limit' => 4],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Recent Insights', 'category_id' => null, 'limit' => 6]
                ];
            }
        @endphp
        <!-- Hero / Featured -->
        @foreach($blocks as $block)
            @if($block['type'] === 'hero_grid')
                @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                    @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                @else
                    @include("themes.good.components.{$block['type']}", ['block' => $block])
                @endif
            @endif
        @endforeach

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mt-12">
            <!-- Latest Wellness Articles -->
            <main class="lg:col-span-2">
                @foreach($blocks as $block)
                    @if($block['type'] !== 'hero_grid')
                        @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                            @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                        @else
                            @include("themes.good.components.{$block['type']}", ['block' => $block])
                        @endif
                    @endif
                @endforeach
            </main>

            <!-- Sidebar -->
            <aside class="space-y-8">
                @php
                    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                    
                    if (empty($sidebarBlocks)) {
                        $sidebarBlocks = [
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Trending Topics', 'limit' => 5],
                            ['id' => uniqid(), 'type' => 'newsletter', 'title' => 'Stay Healthy']
                        ];
                    }
                @endphp

                @foreach($sidebarBlocks as $block)
                    @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                        @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                    @else
                        @include("themes.good.components.{$block['type']}", ['block' => $block])
                    @endif
                @endforeach
            </aside>
        </div>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


