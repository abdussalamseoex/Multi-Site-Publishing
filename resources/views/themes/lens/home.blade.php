<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Lens Photography') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <!-- Minimalist Hero Showcase -->
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
            @php $mainFeatured = $featuredPosts->first(); @endphp
            <div class="mb-20">
                <div class="flex flex-col lg:flex-row gap-12 items-center">
                    <div class="w-full lg:w-1/2">
                        <a href="{{ route('frontend.post', $mainFeatured->slug) }}" class="block w-full aspect-[4/5] object-cover bg-gray-100 overflow-hidden group">
                            @if($mainFeatured->featured_image)
                                <img src="{{ Str::startsWith($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : url($mainFeatured->featured_image) }}" class="w-full h-full object-cover transition duration-1000 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">No Image</div>
                            @endif
                        </a>
                    </div>
                    <div class="w-full lg:w-1/2 space-y-6">
                        <a href="{{ isset($mainFeatured->category) ? route('frontend.category', $mainFeatured->category->slug) : '#' }}" class="inline-block border border-gray-200 px-4 py-1 text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-gray-900 hover:border-gray-900 transition">{{ $mainFeatured->category->name ?? 'Featured' }}</a>
                        <a href="{{ route('frontend.post', $mainFeatured->slug) }}" class="block">
                            <h2 class="text-4xl md:text-5xl lg:text-6xl font-light text-gray-900 leading-[1.1] hover:text-gray-600 transition">{{ $mainFeatured->title }}</h2>
                        </a>
                        <p class="text-gray-500 text-lg md:text-xl font-light leading-relaxed max-w-xl">{{ strip_tags($mainFeatured->summary ?? $mainFeatured->content) }}</p>
                        <div class="flex items-center gap-4 text-xs font-medium text-gray-400 uppercase tracking-widest mt-8">
                            <span>By {{ $mainFeatured->user->name ?? 'Photographer' }}</span>
                            <span>&bull;</span>
                            <span>{{ $mainFeatured->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($featuredPosts->count() > 1)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24 pb-20 border-b border-gray-100">
                @foreach($featuredPosts->skip(1)->take(3) as $post)
                <div class="text-center group block">
                    <a href="{{ route('frontend.post', $post->slug) }}" class="block w-full aspect-[3/2] overflow-hidden bg-gray-100 mb-6">
                        @if($post->featured_image)
                            <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-105">
                        @endif
                    </a>
                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-2 block hover:text-gray-900 transition">{{ $post->category->name ?? 'Gallery' }}</a>
                    <a href="{{ route('frontend.post', $post->slug) }}">
                        <h3 class="text-2xl font-medium text-gray-900 leading-tight mb-2 group-hover:text-gray-500 transition">{{ $post->title }}</h3>
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-16">
            <main class="lg:col-span-3">
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <div class="flex items-center justify-between mb-10 pb-4 border-b border-gray-200">
                    <h3 class="text-xl font-medium text-gray-900 tracking-wide uppercase">Latest Captures</h3>
                </div>
                
                <!-- Clean Photography Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 lg:gap-12">
                    @foreach($latestPosts as $index => $post)
                    <article class="photo-card block mb-4 group relative bg-gray-50">
                        <a href="{{ route('frontend.post', $post->slug) }}" class="block w-full aspect-square overflow-hidden bg-gray-100">
                            @if($post->featured_image)
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100">No Photo</div>
                            @endif
                        </a>
                        <!-- Content Overlay -->
                        <div class="photo-overlay absolute inset-0 bg-white/90 backdrop-blur-sm p-8 flex flex-col items-center justify-center text-center opacity-0 z-10">
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 mb-3 block relative z-20">{{ $post->category->name ?? 'Shots' }}</a>
                            <a href="{{ route('frontend.post', $post->slug) }}" class="relative z-20">
                                <h4 class="text-2xl font-medium text-gray-900 leading-tight mb-4 inline-block relative after:content-[''] after:absolute after:w-0 after:h-0.5 after:-bottom-2 after:left-1/2 after:-translate-x-1/2 after:bg-gray-900 group-hover:after:w-16 after:transition-all after:duration-500">{{ $post->title }}</h4>
                            </a>
                            <div class="flex items-center text-[10px] text-gray-400 uppercase tracking-widest mt-4">
                                <span>{{ $post->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                
                <div class="mt-16 border-t border-gray-200 pt-8">{{ $latestPosts->links() }}</div>
                @endif
            </main>

            <aside class="space-y-16">
                <!-- About Portfolio / Photographer -->
                <div class="text-center pb-12 border-b border-gray-200">
                    <img src="{{ \App\Models\Setting::get('site_logo') ? url(\App\Models\Setting::get('site_logo')) : 'https://ui-avatars.com/api/?name=Lens&background=f3f4f6&color=111' }}" class="w-24 h-24 rounded-full mx-auto mb-6 object-cover bg-gray-50 p-1 border border-gray-200">
                    <h3 class="text-xl font-medium text-gray-900 mb-2 tracking-wide">{{ \App\Models\Setting::get('site_title', 'Lens Studio') }}</h3>
                    <p class="text-gray-500 text-sm font-light leading-relaxed mb-6">{{ \App\Models\Setting::get('site_tagline', 'Capturing light, shadows, and moments. A fine-art photography gallery.') }}</p>
                    <a href="#" class="inline-block border-2 border-gray-900 text-gray-900 px-6 py-2 text-xs font-bold uppercase tracking-[0.2em] hover:bg-gray-900 hover:text-white transition">Get In Touch</a>
                </div>

                <!-- Advertising Setup -->
                @php $adSidebar = \App\Models\Setting::get('ad_sidebar_code'); @endphp
                @if($adSidebar)
                    <div class="w-full flex justify-center mb-6 overflow-hidden">
                        {!! $adSidebar !!}
                    </div>
                @else
                    <div class="bg-gray-50 p-6 text-center border border-dashed border-gray-300">
                        <span class="text-[10px] uppercase text-gray-400 tracking-[0.2em] mb-4 block">Advertisement</span>
                        <div class="w-full bg-gray-200 h-[250px] flex items-center justify-center text-gray-400 font-mono text-xs">
                            300x250 Ad Space
                        </div>
                    </div>
                @endif

                <!-- Fine Art / Popular -->
                <div>
                    <h3 class="text-sm font-medium tracking-widest text-gray-900 uppercase border-b border-gray-200 pb-3 mb-6">Popular Works</h3>
                    <div class="space-y-6">
                        @foreach(\App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take(4)->get() as $pop)
                        <article class="flex gap-4 group relative hover:bg-gray-50 p-2 transition -mx-2 rounded">
                            <a href="{{ route('frontend.post', $pop->slug) }}" class="absolute inset-0 z-0"></a>
                            <div class="w-20 h-20 shrink-0 bg-gray-100 border border-gray-200">
                                @if($pop->featured_image)
                                    <img src="{{ url($pop->featured_image) }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 flex flex-col justify-center">
                                <h4 class="text-sm font-medium text-gray-800 leading-snug group-hover:text-gray-500 transition line-clamp-2 mb-1">{{ $pop->title }}</h4>
                                <span class="text-[10px] font-bold tracking-widest uppercase text-gray-400">{{ $pop->category->name ?? 'Gallery' }}</span>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @include('themes.components.footer')
</body>
</html>
