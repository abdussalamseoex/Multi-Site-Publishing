<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Estate Premier') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,800;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#b45309'); // amber-700
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Lato', sans-serif; background-color: #fafafa; color: #1c1917; }
        .font-elegant { font-family: 'Playfair Display', serif; }
        
        header { background-color: #1c1917 !important; border-bottom: none !important; padding: 1rem 0;}
        header a, header svg { color: #f5f5f4 !important; }
        header .text-primary { color: #d97706 !important; } /* amber-600 */
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .gold-border { border-color: #d97706; }
        .bg-gold { background-color: #d97706; color: white; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased">

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


    @php
        $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');
        $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
        $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
        
        if (empty($blocks)) {
            $blocks = [
                ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Featured Properties', 'category_id' => null, 'limit' => 4],
                ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Market Insights', 'category_id' => null, 'limit' => 5]
            ];
        }
    @endphp

    <!-- Hero Showcase -->
    @foreach($blocks as $block)
        @if($block['type'] === 'hero_grid')
            @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
            @else
                @include("themes.good.components.{$block['type']}", ['block' => $block])
            @endif
        @endif
    @endforeach

    <div class="max-w-7xl mx-auto px-4 py-16">
        
        <!-- Grid is now part of Hero Component -->

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 mt-12">
            <!-- Latest Articles -->
            <main class="lg:col-span-8 space-y-12">
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

            <aside class="lg:col-span-4 space-y-12">
                @php
                    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                    
                    if (empty($sidebarBlocks)) {
                        $sidebarBlocks = [
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Popular Local', 'limit' => 4],
                            ['id' => uniqid(), 'type' => 'agency_widget', 'title' => 'List With Us']
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


