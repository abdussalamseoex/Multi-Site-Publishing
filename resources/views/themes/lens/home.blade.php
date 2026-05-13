<!DOCTYPE html>
<html lang="en">
<head>
    @php $isHomepage = !isset($isCategory); @endphp
    @include('themes.components.meta_tags')
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#111827'); // dark gray
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Jost', sans-serif; background-color: #ffffff; color: #374151; }
        
        header { background-color: #ffffff !important; border-bottom: 1px solid #f3f4f6 !important; }
        header a, header svg { color: #111827 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: #ffffff !important; }
        header .bg-gray-900 { background-color: #f3f4f6 !important; color: #111827 !important; border: 1px solid #e5e7eb !important;}
        
        .photo-card { overflow: hidden; position: relative; }
        .photo-card img { transition: transform 0.6s ease, filter 0.6s ease; }
        .photo-card:hover img { transform: scale(1.04); filter: brightness(0.85); }
        .photo-overlay { opacity: 0; transition: opacity 0.4s ease; }
        .photo-card:hover .photo-overlay { opacity: 1; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased selection:bg-gray-900 selection:text-white">
    @include('themes.components.header')

    @if(isset($isCategory) && $isCategory)
    <div class="bg-gray-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-16 md:py-24 text-center">
            <span class="text-xs font-bold uppercase tracking-[0.2em] mb-4 block text-gray-400">Photography Portfolio Collection</span>
            <h1 class="text-4xl md:text-6xl font-medium mb-4 text-gray-900">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-lg text-gray-500 max-w-2xl mx-auto font-light">{{ $category->description }}</p>
            @endif
        </div>
    </div>
    @endif

    <div class="max-w-[1400px] mx-auto px-6 lg:px-12 py-12">
        @php
            $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');
            $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            if (empty($blocks)) {
                $blocks = [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Top Stories', 'category_id' => null, 'limit' => 4],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Captures', 'category_id' => null, 'limit' => 8]
                ];
            }
        @endphp
        <!-- Minimalist Hero Showcase + Custom HTML (full-width) -->
        @foreach($blocks as $block)
            @if(in_array($block['type'], ['hero_grid', 'custom_html']))
                @if($block['type'] === 'custom_html')
                    @include('themes.components.custom_html', ['block' => $block])
                @elseif(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                    @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                @else
                    @include("themes.good.components.{$block['type']}", ['block' => $block])
                @endif
            @endif
        @endforeach

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-16">
            <main class="lg:col-span-3">
                @foreach($blocks as $block)
                    @if(!in_array($block['type'], ['hero_grid', 'custom_html']))
                        @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                            @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                        @else
                            @include("themes.good.components.{$block['type']}", ['block' => $block])
                        @endif
                    @endif
                @endforeach
            </main>

            <aside class="space-y-16">
                @php
                    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                    
                    if (empty($sidebarBlocks)) {
                        $sidebarBlocks = [
                            ['id' => uniqid(), 'type' => 'about_portfolio', 'title' => 'About Portfolio'],
                            ['id' => uniqid(), 'type' => 'ad_sidebar', 'title' => 'Sidebar Ad'],
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Popular Works', 'limit' => 4]
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


