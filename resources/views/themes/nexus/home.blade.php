<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Nexus Tech') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#6366f1'); // Indigo
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .font-tech { font-family: 'Space Grotesk', sans-serif; }
        
        /* Dark Header Override */
        header { background-color: rgba(15, 23, 42, 0.9) !important; border-bottom: 1px solid #1e293b !important; backdrop-filter: blur(12px) !important; }
        header a, header svg { color: #f1f5f9 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
        
        .glass { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .hover-card { transition: all 0.3s ease; }
        .hover-card:hover { transform: translateY(-4px); border-color: var(--primary); box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.3); }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased selection:bg-indigo-500 selection:text-white">
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


    <!-- Hero / Featured -->
    <div class="max-w-7xl mx-auto px-4 py-12">
        @php
            $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            if (empty($blocks)) {
                $blocks = [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'TOP INNOVATIONS', 'category_id' => null, 'limit' => 3],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Intel', 'category_id' => null, 'limit' => 6]
                ];
            }
        @endphp

        <!-- Render Hero Grid if it exists at the top -->
        @foreach($blocks as $index => $block)
            @if($block['type'] === 'hero_grid')
                @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                    @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                @else
                    @include("themes.good.components.{$block['type']}", ['block' => $block])
                @endif
                @php unset($blocks[$index]); @endphp
            @endif
        @endforeach

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mt-12">
            <!-- Latest Grid -->
            <main class="lg:col-span-2">
                @foreach($blocks as $block)
                    @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                        @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                    @else
                        @include("themes.good.components.{$block['type']}", ['block' => $block])
                    @endif
                @endforeach
            </main>

            <!-- Sidebar -->
            <aside class="space-y-10">
                @php
                    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                    
                    if (empty($sidebarBlocks)) {
                        $sidebarBlocks = [
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Trending Now', 'limit' => 6],
                            ['id' => uniqid(), 'type' => 'newsletter_block', 'title' => 'Join the Network']
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


