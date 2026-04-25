<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <!-- BlogPost Standard Theme -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'My Standard Blog') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="bg-gray-100 text-gray-800">

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


    <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
        
        <main class="w-full md:w-2/3">
            @php
                $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
                $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
                
                if (empty($blocks)) {
                    $blocks = [
                        ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Articles', 'category_id' => null, 'limit' => 10]
                    ];
                }
            @endphp

            @foreach($blocks as $block)
                @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                    @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                @else
                    @include("themes.good.components.{$block['type']}", ['block' => $block])
                @endif
            @endforeach
        </main>

        <aside class="w-full md:w-1/3">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 sticky top-8">
                <h3 class="font-bold text-lg border-b pb-2 mb-4">About Us</h3>
                <p class="text-sm text-gray-600 mb-6">Welcome to {{ \App\Models\Setting::get('site_title') }}. Here you will find the best articles and guest posts.</p>
                
                @php
                    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                    
                    if (empty($sidebarBlocks)) {
                        $sidebarBlocks = [
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Featured', 'limit' => 5]
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
        </aside>

    </div>
    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


