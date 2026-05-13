<!DOCTYPE html>
<html lang="en">
<head>
    @php $isHomepage = !isset($isCategory); @endphp
    @include('themes.components.meta_tags')
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#00d282',
                        dark: '#111111',
                        grayLight: '#f5f5f5'
                    },
                    fontFamily: {
                        sans: ['Jost', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .image-hover-zoom img { transition: transform 0.5s ease; }
        .image-hover-zoom:hover img { transform: scale(1.05); }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="bg-grayLight text-gray-800 antialiased flex flex-col min-h-screen">

    @include('themes.components.header')

    <main class="flex-grow max-w-[1200px] w-full mx-auto px-4 py-8">
        @php
            $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');
            $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            if (empty($blocks)) {
                $blocks = [
                    ['type' => 'hero_grid', 'title' => 'Top Stories', 'limit' => 4],
                    ['type' => 'latest_news', 'title' => 'Latest Articles', 'limit' => 6],
                    ['type' => 'category_spotlight', 'title' => 'Editorial Choice', 'limit' => 5],
                    ['type' => 'category_grid', 'title' => 'More News', 'limit' => 6],
                ];
            }
        @endphp

        @foreach($blocks as $block)
            @if(in_array(($block['type'] ?? ''), ['hero_grid', 'custom_html']))
                @if(($block['type'] ?? '') === 'hero_grid')
                    @include('themes.good.components.hero_grid', ['block' => $block])
                @else
                    @include('themes.components.custom_html', ['block' => $block])
                @endif
            @elseif(($block['type'] ?? '') === 'latest_news')
                <!-- Two Column Layout for Latest News -->
                <div class="flex flex-col lg:flex-row gap-8 mt-8 mb-12">
                    <div class="w-full lg:w-2/3 space-y-10">
                        @include('themes.good.components.latest_news', ['block' => $block])
                    </div>
                    <div class="w-full lg:w-1/3">
                        @include('themes.good.components.sidebar')
                    </div>
                </div>
            @elseif(($block['type'] ?? '') === 'category_spotlight')
                @include('themes.good.components.category_spotlight', ['block' => $block])
            @elseif(($block['type'] ?? '') === 'category_grid')
                @include('themes.good.components.category_grid', ['block' => $block])
            @elseif(($block['type'] ?? '') === 'ad_block')
                @include('themes.good.components.ad_block', ['block' => $block])
            @endif
        @endforeach
    </main>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>
