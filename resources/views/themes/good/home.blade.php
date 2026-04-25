<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'GOOD Theme') }} | {{ \App\Models\Setting::get('site_tagline', 'The Ultimate News Experience') }}</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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

    @include('themes.good.components.navbar')

    <main class="flex-grow max-w-[1200px] w-full mx-auto px-4 py-8 space-y-12">
        @php
            $layoutRaw = \App\Models\Setting::get('homepage_layout');
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
        @endphp

        @if(empty($blocks))
            <div class="text-center py-20 text-gray-500 bg-white shadow-sm rounded-lg">
                <h2 class="text-2xl font-bold mb-2">Welcome to the Dynamic Theme Builder!</h2>
                <p>Please go to the <strong>Admin Panel -> Theme Builder</strong> to add content blocks to your homepage.</p>
            </div>
        @else
            @foreach($blocks as $block)
                @if($block['type'] === 'hero_grid')
                    @include('themes.good.components.hero_grid', ['block' => $block])
                @elseif($block['type'] === 'latest_news')
                    @include('themes.good.components.latest_news', ['block' => $block])
                @elseif($block['type'] === 'category_spotlight')
                    @include('themes.good.components.category_spotlight', ['block' => $block])
                @elseif($block['type'] === 'category_grid')
                    @include('themes.good.components.category_grid', ['block' => $block])
                @elseif($block['type'] === 'ad_block')
                    @include('themes.good.components.ad_block', ['block' => $block])
                @endif
            @endforeach
        @endif
    </main>

    @include('themes.good.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>
