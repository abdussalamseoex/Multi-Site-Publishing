<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <title>{{ $post->meta_title ?? $post->title }}</title>
    <meta name="description" content="{{ $post->meta_description }}">
    @if($post->meta_keywords)
    <meta name="keywords" content="{{ $post->meta_keywords }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    
    <!-- Open Graph & SEO -->
    <meta property="og:title" content="{{ $post->meta_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->meta_description }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ request()->url() }}">
    @if($post->featured_image)
    <meta property="og:image" content="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}">
    @endif
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Article",
      "headline": "{{ $post->title }}",
      "author": { "@@type": "Person", "name": "{{ $post->user->name ?? 'Author' }}" },
      "datePublished": "{{ $post->created_at->toIso8601String() }}"
    }
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#000000');
        $font = \App\Models\Setting::get('typography', 'Playfair Display');
        $logo = \App\Models\Setting::get('site_logo');
        $menu = \App\Models\Menu::where('location', 'header')->with('items')->first() ?? \App\Models\Menu::with('items')->first();
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: '{{ $font }}', serif; }
        .text-primary { color: var(--primary); }
        .bg-primary { background-color: var(--primary); }
        .prose p::first-letter { font-size: 3rem; font-weight: bold; font-family: '{{ $font }}', serif; float: left; margin-top:-0.5rem; margin-right: 0.5rem; color: var(--primary); }
        .prose p { margin-bottom: 2rem; font-size: 1.125rem; line-height: 2; color: #444; font-family: 'Inter', sans-serif;}
        .prose img { width: 100%; border-radius: 8px; margin-bottom: 2rem;}
        .prose h2, .prose h3 { font-family: '{{ $font }}', serif; font-weight: black; color: #000; margin-top: 2rem; margin-bottom: 1rem; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="bg-[#faf9f6] text-[#333]">

    @include('themes.components.header')

    <div class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 lg:grid-cols-4 gap-16">
        <article class="lg:col-span-3">
            <header class="mb-12">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="text-primary font-bold uppercase tracking-widest text-xs mb-4">{{ $post->category->name ?? 'Editorial' }}</div></a>
                <h1 class="text-5xl md:text-7xl font-black leading-tight mb-8">{{ $post->title }}</h1>
                <div class="flex items-center gap-4 text-sm font-bold uppercase tracking-wider text-gray-500 font-sans border-b-2 border-black pb-6">
                    <span>By {{ $post->user->name ?? 'Editor' }}</span>
                    <span>&bull;</span>
                    <span>{{ $post->created_at->format('d F Y') }}</span>
                    <span>&bull;</span>
                    <span>{{ $post->views }} Views</span>
                </div>
            </header>

            @if($post->featured_image)
                <div class="w-full mb-16 overflow-hidden bg-gray-100 aspect-[16/9]">
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover">
                </div>
            @endif

            <div class="prose max-w-none">
                {!! $post->content !!}
            </div>

            <!-- Related Posts -->
            <div class="mt-20 border-t-4 border-black pt-12">
                <h3 class="text-3xl font-black uppercase tracking-widest mb-8">Also in {{ $post->category->name ?? 'Editorial' }}</h3>
                @php 
                    $relatedList = \App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->take(3)->get();
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($relatedList as $rel)
                        <a href="{{ route('frontend.post', $rel->slug) }}" class="block group">
                            @if($rel->featured_image)
                                <div class="aspect-square bg-gray-200 mb-4 overflow-hidden">
                                    <img src="{{ url($rel->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                </div>
                            @endif
                            <h4 class="font-bold text-xl leading-tight group-hover:text-primary transition font-serif line-clamp-2">{{ $rel->title }}</h4>
                        </a>
                    @endforeach
                </div>
            </div>
        </article>

        <!-- Sidebar -->
        <aside class="space-y-12 border-t-4 border-black lg:border-t-0 pt-12 lg:pt-0">
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest border-b-2 border-black pb-2 mb-6">Contributor</h3>
                <div class="bg-white p-6 border border-gray-200 text-center">
                    <div class="w-20 h-20 bg-black text-white mx-auto rounded-full flex items-center justify-center font-bold text-4xl font-serif mb-4">
                        {{ substr($post->user->name ?? 'M', 0, 1) }}
                    </div>
                    <h4 class="font-black text-xl mb-1">{{ $post->user->name ?? 'Editorial Team' }}</h4>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-4">Senior Editor</p>
                    <p class="text-sm text-gray-600 font-sans italic">Delivering the latest insights in fashion, lifestyle, and culture.</p>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-black uppercase tracking-widest border-b-2 border-black pb-2 mb-6">Topics</h3>
                <div class="flex flex-wrap gap-2 font-sans font-bold text-xs uppercase tracking-wider">
                    @foreach(\App\Models\Category::all() as $cat)
                        <a href="#" class="px-3 py-2 bg-gray-100 hover:bg-primary hover:text-white transition">{{ $cat->name }}</a>
                    @endforeach
                </div>
            </div>

            <div class="bg-black text-white p-8 text-center border-4 border-black">
                <h3 class="text-2xl font-black uppercase tracking-widest mb-4">The VIP List</h3>
                <p class="text-gray-400 text-sm font-sans mb-6">Exclusive content straight to your inbox.</p>
                <form onsubmit="event.preventDefault(); alert('Subscribed to VIP List!');" class="flex flex-col gap-4 font-sans">
                    <input type="email" placeholder="Your Email" class="bg-transparent border border-gray-600 px-4 py-3 text-white text-sm text-center focus:border-white outline-none">
                    <button class="bg-white text-black font-bold uppercase tracking-widest py-3 hover:bg-gray-200 transition text-sm">Join Now</button>
                </form>
            </div>
        </aside>
    </div>
    
    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



