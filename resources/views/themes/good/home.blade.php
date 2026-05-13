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
            // Read specific theme blocks
            $layoutRaw = \App\Models\Setting::get('theme_blocks_good');
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            // Provide a rich default layout if empty so the theme looks "ready" out of the box
            if (empty($blocks)) {
                $blocks = [
                    ['type' => 'hero_grid', 'title' => 'Top Stories', 'limit' => 4],
                    ['type' => 'latest_news', 'title' => 'Latest Articles', 'limit' => 6],
                    ['type' => 'ad_block'],
                    ['type' => 'category_spotlight', 'title' => 'Editorial Choice', 'limit' => 5],
                    ['type' => 'category_grid', 'title' => 'More News', 'limit' => 6],
                ];
            }

            // Separate Hero grid & custom_html from the rest (both render full-width)
            $heroBlocks = array_filter($blocks, fn($b) => in_array($b['type'], ['hero_grid', 'custom_html']));
            $otherBlocks = array_filter($blocks, fn($b) => !in_array($b['type'], ['hero_grid', 'custom_html']));
        @endphp

        <!-- Full Width Blocks (Hero + Custom HTML) -->
        @foreach($heroBlocks as $block)
            @if($block['type'] === 'hero_grid')
                @include('themes.good.components.hero_grid', ['block' => $block])
            @elseif($block['type'] === 'custom_html')
                @include('themes.components.custom_html', ['block' => $block])
            @endif
        @endforeach

        <!-- Two Column Layout (Main Content + Sidebar) -->
        <div class="flex flex-col lg:flex-row gap-8 mt-8">
            
            <!-- Left Main Content Area -->
            <div class="w-full lg:w-2/3 space-y-10">
                @foreach($otherBlocks as $block)
                    @if($block['type'] === 'latest_news')
                        @include('themes.good.components.latest_news', ['block' => $block])
                    @elseif($block['type'] === 'category_spotlight')
                        @include('themes.good.components.category_spotlight', ['block' => $block])
                    @elseif($block['type'] === 'category_grid')
                        @include('themes.good.components.category_grid', ['block' => $block])
                    @elseif($block['type'] === 'ad_block')
                        @include('themes.good.components.ad_block', ['block' => $block])
                    @endif
                @endforeach
            </div>

            <!-- Right Sidebar Area -->
            <div class="w-full lg:w-1/3">
                @include('themes.good.components.sidebar')
            </div>

        </div>
    </main>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>
