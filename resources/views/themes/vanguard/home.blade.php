<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Vanguard Elite') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;600;700&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#ea580c'); // orange-600 default
        $font = \App\Models\Setting::get('typography', 'Inter');
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: '{{ $font }}', sans-serif; background-color: #f3f4f6; }
        .font-gaming { font-family: 'Teko', sans-serif; }
        /* Dark Header Override */
        header { background-color: #0f172a !important; border-bottom: 2px solid var(--primary) !important; backdrop-filter: none !important; }
        header a, header svg { color: #e2e8f0 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
        header .bg-gray-900 { background-color: #334155 !important; }
        
        .badge-orange { background-color: #f97316; color: white; }
        .badge-blue { background-color: #3b82f6; color: white; }
        .badge-red { background-color: #ef4444; color: white; }
        .badge-green { background-color: #10b981; color: white; }
        .badge-purple { background-color: #8b5cf6; color: white; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="text-gray-900 antialiased">
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


    <div class="max-w-7xl mx-auto px-4 py-8">
        @php
            $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            if (empty($blocks)) {
                $blocks = [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Featured', 'category_id' => null, 'limit' => 3],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Reviews', 'category_id' => null, 'limit' => 6],
                    ['id' => uniqid(), 'type' => 'category_spotlight', 'title' => 'Deep Dive Blogs', 'category_id' => null, 'limit' => 4]
                ];
            }
        @endphp
        <!-- Hero Section (Featured) -->
        @foreach($blocks as $block)
            @if($block['type'] === 'hero_grid')
                @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                    @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                @else
                    @include("themes.good.components.{$block['type']}", ['block' => $block])
                @endif
            @endif
        @endforeach

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <main class="lg:col-span-3 space-y-12">
                @foreach($blocks as $block)
                    @if($block['type'] !== 'category_spotlight' && $block['type'] !== 'hero_grid')
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
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Most Popular', 'limit' => 5],
                            ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad']
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

        @foreach($blocks as $block)
            @if($block['type'] === 'category_spotlight')
                @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                    @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                @else
                    @include("themes.good.components.{$block['type']}", ['block' => $block])
                @endif
            @endif
        @endforeach
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


