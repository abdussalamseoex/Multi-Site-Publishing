<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Magazine Layout') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#000000');
        $font = \App\Models\Setting::get('typography', 'Playfair Display');
        $logo = \App\Models\Setting::get('site_logo');
        $menu = \App\Models\Menu::with('items')->first();
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: '{{ $font }}', serif; }
        .text-primary { color: var(--primary); }
        .bg-primary { background-color: var(--primary); }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="bg-[#faf9f6] text-[#333]">

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


    <div class="max-w-7xl mx-auto px-4 py-12">
        @php
            $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            if (empty($blocks)) {
                $blocks = [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Featured', 'category_id' => null, 'limit' => 1],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'The Latest', 'category_id' => null, 'limit' => 6]
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
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


