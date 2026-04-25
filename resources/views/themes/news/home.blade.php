<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'News Magazine Master') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#0ea5e9'); // Default blue
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Roboto', sans-serif; background-color: #fbfbfb; color: #111; }
        .font-ui { font-family: 'Inter', sans-serif; }
        
        header { background-color: #fff !important; border-bottom: 3px solid #000 !important; }
        header a { color: #111 !important; font-weight: 600; font-family: 'Inter', sans-serif; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .section-title { font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #111; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 20px; position: relative; }
        .section-title::after { content: ''; position: absolute; left: 0; bottom: -2px; width: 40px; height: 2px; background-color: var(--primary); }

        .hover-img { transition: transform 0.4s ease; }
        .group:hover .hover-img { transform: scale(1.05); }
        
        .overlay-gradient { background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0) 100%); }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased selection:bg-sky-500 selection:text-white">

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


    <!-- Trending Ticker (Optional, nice touch) -->
    <div class="max-w-[1200px] mx-auto px-4 mt-4 flex items-center border-b border-gray-200 pb-3 font-ui text-xs">
        <span class="bg-black text-white px-2 py-1 font-bold uppercase tracking-widest mr-3 text-[10px]">Trending</span>
        <div class="flex-1 overflow-hidden whitespace-nowrap">
            @foreach($latestPosts->take(4) as $ticker)
                <a href="{{ route('frontend.post', $ticker->slug) }}" class="inline-block mr-6 hover:text-sky-600 transition font-medium">{{ $ticker->title }}</a>
            @endforeach
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="max-w-[1200px] mx-auto px-4 mt-6">
        @php
            $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            if (empty($blocks)) {
                $blocks = [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Top Stories', 'category_id' => null, 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'category_spotlight', 'title' => "Don't Miss", 'category_id' => null, 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'category_grid', 'title' => 'Lifestyle News', 'category_id' => null, 'limit' => 4],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Articles', 'category_id' => null, 'limit' => 6]
                ];
            }
        @endphp

        <!-- Render Hero Grid if it exists at the top to preserve full width layout capability if needed -->
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

    <!-- MAIN CONTENT AREA -->
    <div class="max-w-[1200px] mx-auto px-4 mt-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LEFT COLUMN (Main Content - 2/3 width) -->
            <div class="lg:col-span-2 space-y-12">
                @foreach($blocks as $block)
                    @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                        @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                    @else
                        @include("themes.good.components.{$block['type']}", ['block' => $block])
                    @endif
                @endforeach
            </div>

            <!-- RIGHT COLUMN (Sidebar - 1/3 width) -->
            <div class="lg:col-span-1 space-y-10">
                @php
                    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                    
                    if (empty($sidebarBlocks)) {
                        $sidebarBlocks = [
                            ['id' => uniqid(), 'type' => 'social_counter', 'title' => 'Stay Connected'],
                            ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad'],
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Most Popular', 'limit' => 5]
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
            </div>
        </div>
    </div>

    <div class="mt-16">
        @include('themes.components.footer')
    </div>
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


