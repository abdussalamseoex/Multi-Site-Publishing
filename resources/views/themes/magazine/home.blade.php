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
        <!-- Hero Section -->
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
            @php $hero = $featuredPosts->first(); @endphp
            <div class="relative bg-black text-white rounded-lg overflow-hidden mb-16 group h-[60vh] flex items-end p-8 md:p-16">
                <!-- Fetch actual featured image -->
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
                    <p class="text-gray-600 text-sm mb-3 font-sans">{{ Str::limit(strip_tags($post->content), 100) }}</p>
                    <div class="text-xs text-gray-400 font-bold uppercase font-sans">By {{ $post->user->name ?? 'Editor' }}</div>
                </article>
            @endforeach
        </div>
        
        <div class="mt-12">{{ $latestPosts->links() }}</div>
        @endif
    </div>

    @include('themes.components.footer')
</body>
</html>

