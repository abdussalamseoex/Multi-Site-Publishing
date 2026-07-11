<!DOCTYPE html>
<html lang="en">
<head>
    @php $isHomepage = !isset($isCategory); @endphp
    @include('themes.components.meta_tags')
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
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

    {{-- Standardized Theme Block Rendering --}}
    @php
        $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');
        $themeBlocksRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
        $allBlocks = $themeBlocksRaw ? json_decode($themeBlocksRaw, true) : [];
    @endphp

    @foreach($allBlocks as $block)
        @if(($block['type'] ?? '') === 'custom_html')
            @include('themes.components.custom_html', ['block' => $block])
        @elseif(($block['type'] ?? '') === 'hero_grid')
            {{-- In Magazine, hero_grid shows featuredPosts --}}
            <div class="max-w-7xl mx-auto px-4 py-12">
                @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
                    @php $hero = $featuredPosts->first(); @endphp
                    <div class="relative bg-black text-white rounded-lg overflow-hidden mb-16 group h-[60vh] flex items-end p-8 md:p-16">
                        @if($hero->featured_image)
                            <img src="{{ Str::startsWith($hero->featured_image, 'http') ? $hero->featured_image : url($hero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-700">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-tr from-black via-gray-900 to-transparent opacity-80 z-10"></div>
                        <div class="relative z-20 max-w-2xl">
                            <a href="{{ isset($hero->category) ? route('frontend.category', $hero->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="px-3 py-1 bg-white text-black text-xs font-bold uppercase tracking-widest mb-4 inline-block">{{ $hero->category->name ?? 'Lifestyle' }}</span></a>
                            <a href="{{ route('frontend.post', $hero->slug) }}">
                                <h2 class="text-5xl md:text-7xl font-black mb-4 leading-tight hover:text-gray-300 transition line-clamp-2">{{ $hero->title }}</h2>
                            </a>
                            <p class="text-lg md:text-xl text-gray-200">{{ Str::limit($hero->summary ?? strip_tags($hero->content), 150) }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @elseif(($block['type'] ?? '') === 'latest_news')
            <div class="max-w-7xl mx-auto px-4 py-12">
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <h3 class="text-2xl font-bold uppercase tracking-widest border-b-2 border-black pb-2 mb-8 mt-12">The Latest</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    @foreach($latestPosts as $post)
                        <article>
                            @if($post->featured_image)
                                <a href="{{ route('frontend.post', $post->slug) }}">
                                    <div class="aspect-[4/5] bg-gray-200 mb-4 rounded-md overflow-hidden">
                                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover:scale-110 transition duration-500">
                                    </div>
                                </a>
                            @endif
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="text-xs font-bold uppercase tracking-widest text-primary mb-2 mt-4">{{ $post->category->name ?? 'Style' }}</div></a>
                            <a href="{{ route('frontend.post', $post->slug) }}">
                                <h4 class="text-2xl font-bold leading-tight mb-2 hover:text-primary transition line-clamp-2">{{ $post->title }}</h4>
                            </a>
                            <p class="text-gray-600 text-sm mb-3 font-sans line-clamp-2">{{ Str::limit(strip_tags($post->summary ?? $post->content), 140) }}</p>
                            <div class="text-xs text-gray-400 font-bold uppercase font-sans">By {{ $post->user->name ?? 'Editor' }}</div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-12">{{ $latestPosts->links() }}</div>
                @endif
            </div>
        @endif
    @endforeach


    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


