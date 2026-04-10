<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'News Magazine Master') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#0ea5e9'); // Default blue
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Roboto', sans-serif; background-color: #fbfbfb; color: #111; }
        .font-ui { font-family: 'Inter', sans-serif; }
        
        header { background-color: #fff !important; border-bottom: 3px solid #000 !important; }
        header a { color: #111 !important; font-weight: 600; font-family: 'Inter', sans-serif; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .section-title { font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #111; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 20px; position: relative; }
        .section-title::after { content: ''; position: absolute; left: 0; bottom: -2px; width: 40px; height: 2px; background-color: var(--primary); }

        .hover-img { transition: transform 0.4s ease; }
        .group:hover .hover-img { transform: scale(1.05); }
        
        .overlay-gradient { background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0) 100%); }
    </style>
</head>
<body class="antialiased selection:bg-sky-500 selection:text-white">

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


    <!-- Trending Ticker (Optional, nice touch) -->
    <div class="max-w-[1200px] mx-auto px-4 mt-4 flex items-center border-b border-gray-200 pb-3 font-ui text-xs">
        <span class="bg-black text-white px-2 py-1 font-bold uppercase tracking-widest mr-3 text-[10px]">Trending</span>
        <div class="flex-1 overflow-hidden whitespace-nowrap">
            @foreach($latestPosts->take(4) as $ticker)
                <a href="{{ route('frontend.post', $ticker->slug) }}" class="inline-block mr-6 hover:text-sky-600 transition font-medium">{{ $ticker->title }}</a>
            @endforeach
        </div>
    </div>

    <!-- MAIN HERO GRID (Like the screenshot exactly) -->
    <div class="max-w-[1200px] mx-auto px-4 py-8">
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count() >= 5)
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-1 h-auto lg:h-[480px]">
                
                <!-- Large Left Item (Col Span 2) -->
                @php $mainHero = $featuredPosts[0]; @endphp
                <div class="lg:col-span-2 relative group overflow-hidden bg-gray-900 border border-white">
                    <article class="block w-full h-full relative">
    <a href="{{ route('frontend.post', $mainHero->slug) }}" class="absolute inset-0 z-0"></a>
                        @if($mainHero->featured_image)
                            <img src="{{ Str::startsWith($mainHero->featured_image, 'http') ? $mainHero->featured_image : url($mainHero->featured_image) }}" class="w-full h-full object-cover hover-img">
                        @endif
                        <div class="absolute inset-0 overlay-gradient"></div>
                        <div class="absolute bottom-0 left-0 p-6 md:p-8 w-full z-10">
                            <a href="{{ isset($mainHero->category) ? route('frontend.category', $mainHero->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="bg-sky-500 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 mb-3 inline-block">{{ $mainHero->category->name ?? 'News' }}</span></a>
                            <h2 class="text-white text-3xl md:text-4xl font-bold leading-[1.15] mb-3 group-hover:text-blue-200 transition line-clamp-2">{{ $mainHero->title }}</h2>
                            <div class="flex items-center text-xs font-ui text-gray-300">
                                <span class="font-bold text-white">{{ $mainHero->user->name ?? 'Admin' }}</span>
                                <span class="mx-2">-</span>
                                <span>{{ $mainHero->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Right Items (2x2 Grid) -->
                <div class="lg:col-span-2 grid grid-cols-2 grid-rows-2 gap-1">
                    @foreach($featuredPosts->skip(1)->take(4) as $index => $subHero)
                    <div class="relative group overflow-hidden bg-gray-900 border border-white h-[200px] lg:h-auto">
                        <article class="block w-full h-full relative">
    <a href="{{ route('frontend.post', $subHero->slug) }}" class="absolute inset-0 z-0"></a>
                            @if($subHero->featured_image)
                                <img src="{{ Str::startsWith($subHero->featured_image, 'http') ? $subHero->featured_image : url($subHero->featured_image) }}" class="w-full h-full object-cover hover-img">
                            @endif
                            <div class="absolute inset-0 overlay-gradient opacity-90"></div>
                            <div class="absolute bottom-0 left-0 p-4 z-10">
                                @php 
                                    $colors = ['bg-pink-500', 'bg-blue-600', 'bg-green-500', 'bg-orange-500'];
                                    $badgeColor = $colors[$index % count($colors)];
                                @endphp
                                <a href="{{ isset($subHero->category) ? route('frontend.category', $subHero->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="{{ $badgeColor }} text-white text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 mb-2 inline-block">{{ $subHero->category->name ?? 'Style' }}</span></a>
                                <h3 class="text-white text-sm md:text-base font-bold leading-tight mb-2 group-hover:text-gray-300 transition line-clamp-3">{{ $subHero->title }}</h3>
                            </div>
                        </article>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="max-w-[1200px] mx-auto px-4 mt-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LEFT COLUMN (Main Content - 2/3 width) -->
            <div class="lg:col-span-2 space-y-12">
                
                @php 
                    $dontMissPosts = \App\Models\Post::where('status', 'published')->latest()->skip(5)->take(5)->get(); 
                @endphp
                @if($dontMissPosts->count() > 0)
                <!-- DON'T MISS SECTION -->
                <section>
                    <h3 class="section-title">Don't Miss</h3>
                    <div class="flex flex-col md:flex-row gap-6">
                        @php $firstMiss = $dontMissPosts->first(); @endphp
                        <!-- Big Item -->
                        <div class="flex-1 group">
                            <a href="{{ route('frontend.post', $firstMiss->slug) }}" class="block">
                                @if($firstMiss->featured_image)
                                    <div class="aspect-video overflow-hidden mb-4">
                                        <img src="{{ Str::startsWith($firstMiss->featured_image, 'http') ? $firstMiss->featured_image : url($firstMiss->featured_image) }}" class="w-full h-full object-cover hover-img">
                                    </div>
                                @endif
                                <h4 class="text-2xl font-bold leading-snug mb-2 group-hover:text-blue-600 transition line-clamp-2">{{ $firstMiss->title }}</h4>
                            </a>
                            <p class="text-sm text-gray-600 line-clamp-3 mb-3">{{ strip_tags($firstMiss->summary ?? $firstMiss->content) }}</p>
                            <div class="font-ui text-[11px] text-gray-400 font-medium">
                                <span class="text-black font-bold">{{ $firstMiss->user->name ?? 'Editor' }}</span> - {{ $firstMiss->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        
                        <!-- List Items -->
                        <div class="flex-1 flex flex-col gap-4">
                            @foreach($dontMissPosts->skip(1) as $missPost)
                            <a href="{{ route('frontend.post', $missPost->slug) }}" class="flex gap-4 group items-center">
                                @if($missPost->featured_image)
                                    <div class="w-24 h-[70px] shrink-0 overflow-hidden">
                                        <img src="{{ url($missPost->featured_image) }}" class="w-full h-full object-cover hover-img">
                                    </div>
                                @endif
                                <div>
                                    <h5 class="text-sm font-bold leading-snug group-hover:text-blue-600 transition line-clamp-2 mb-1">{{ $missPost->title }}</h5>
                                    <div class="font-ui text-[10px] text-gray-400">{{ $missPost->created_at->format('M d, Y') }}</div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </section>
                @endif

                <!-- CATEGORY BLOCK -->
                <section>
                    <h3 class="section-title">Lifestyle News</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach(\App\Models\Post::where('status', 'published')->inRandomOrder()->take(2)->get() as $life)
                        <div class="group">
                            <article class="block relative">
    <a href="{{ route('frontend.post', $life->slug) }}" class="absolute inset-0 z-0"></a>
                                @if($life->featured_image)
                                    <div class="aspect-[16/10] overflow-hidden mb-3 relative">
                                        <img src="{{ url($life->featured_image) }}" class="w-full h-full object-cover hover-img">
                                        <a href="{{ isset($life->category) ? route('frontend.category', $life->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="absolute bottom-0 left-0 bg-black text-white px-2 py-1 text-[9px] font-bold uppercase tracking-wider">{{ $life->category->name ?? 'Style' }}</span></a>
                                    </div>
                                @endif
                                <h4 class="text-lg font-bold leading-snug mb-2 group-hover:text-blue-600 transition line-clamp-2">{{ $life->title }}</h4>
                            </article>
                            <div class="font-ui text-[11px] text-gray-400 font-medium">
                                <span class="text-black font-bold">{{ $life->user->name ?? 'Editor' }}</span> - {{ $life->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>

                <!-- 4-BLOCK STRIP (Like the screenshot middle section) -->
                <section class="grid grid-cols-4 gap-1 h-[200px]">
                    @foreach(\App\Models\Category::all()->take(4) as $index => $cat)
                    @php 
                        // Just grabbing a random post image for the category background
                        $catPost = \App\Models\Post::where('category_id', $cat->id)->whereNotNull('featured_image')->first();
                        $bg = $catPost ? (Str::startsWith($catPost->featured_image, 'http') ? $catPost->featured_image : url($catPost->featured_image)) : 'https://picsum.photos/400/400?random='.$index;
                    @endphp
                    <div class="relative overflow-hidden group border border-white cursor-pointer bg-gray-900 flex items-center justify-center text-center">
                        <img src="{{ $bg }}" class="absolute inset-0 w-full h-full object-cover hover-img opacity-60 group-hover:opacity-40 transition">
                        <a href="{{ route('frontend.category', $cat->slug) }}"><h4 class="relative z-10 text-white font-bold uppercase tracking-widest text-xs md:text-sm drop-shadow-md group-hover:scale-110 transition duration-300">{{ $cat->name }}</h4></a>
                    </div>
                    @endforeach
                </section>

                <!-- LATEST ARTICLES (Vertical List) -->
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <section>
                    <h3 class="section-title">Latest Articles</h3>
                    <div class="space-y-6">
                        @foreach($latestPosts as $post)
                        <article class="flex flex-col sm:flex-row gap-6 group items-start border-b border-gray-100 pb-6">
                            @if($post->featured_image)
                                <article class="w-full sm:w-[280px] aspect-[16/10] sm:aspect-[4/3] shrink-0 overflow-hidden relative relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover-img">
                                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="absolute bottom-0 left-0 bg-black text-white px-2 py-1 text-[9px] font-bold uppercase tracking-wider">{{ $post->category->name ?? 'News' }}</span></a>
                                </article>
                            @endif
                            <div class="flex-1 py-1">
                                <a href="{{ route('frontend.post', $post->slug) }}">
                                    <h4 class="text-xl md:text-2xl font-bold leading-tight mb-3 group-hover:text-blue-600 transition line-clamp-2">{{ $post->title }}</h4>
                                </a>
                                <div class="font-ui text-xs text-gray-500 font-medium mb-3">
                                    <span class="text-black font-bold">{{ $post->user->name ?? 'Staff' }}</span> - {{ $post->created_at->format('F d, Y') }}
                                </div>
                                <p class="text-sm text-gray-600 line-clamp-2 md:line-clamp-3 mb-4 leading-relaxed">{{ strip_tags($post->summary ?? $post->content) }}</p>
                            </div>
                        </article>
                        @endforeach
                    </div>
                    <div class="mt-8 font-ui">{{ $latestPosts->links() }}</div>
                </section>
                @endif
            </div>

            <!-- RIGHT COLUMN (Sidebar - 1/3 width) -->
            <div class="lg:col-span-1 space-y-10">
                
                <!-- STAY CONNECTED -->
                <div class="widget">
                    <h3 class="section-title">Stay Connected</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <a href="#" class="bg-[#3b5998] text-white flex flex-col items-center justify-center p-3 hover:opacity-90 transition">
                            <span class="font-bold text-sm mb-1">22.5k</span>
                            <span class="text-[9px] uppercase tracking-wider opacity-80">Fans</span>
                        </a>
                        <a href="#" class="bg-[#1da1f2] text-white flex flex-col items-center justify-center p-3 hover:opacity-90 transition">
                            <span class="font-bold text-sm mb-1">14.1k</span>
                            <span class="text-[9px] uppercase tracking-wider opacity-80">Followers</span>
                        </a>
                        <a href="#" class="bg-[#cd201f] text-white flex flex-col items-center justify-center p-3 hover:opacity-90 transition">
                            <span class="font-bold text-sm mb-1">8.9k</span>
                            <span class="text-[9px] uppercase tracking-wider opacity-80">Subs</span>
                        </a>
                    </div>
                </div>

                <!-- ADVERTISEMENT -->
                @php $adSidebar = \App\Models\Setting::get('ad_sidebar_code'); @endphp
                @if($adSidebar)
                    <div class="widget text-center overflow-hidden flex justify-center">
                        {!! $adSidebar !!}
                    </div>
                @else
                    <div class="widget text-center">
                        <div class="text-[9px] text-gray-400 uppercase tracking-widest mb-1">- Advertisement -</div>
                        <div class="w-full bg-gray-200 h-[250px] flex items-center justify-center font-bold text-gray-400 text-sm border border-gray-300">
                            300 x 250 Banner
                        </div>
                    </div>
                @endif

                <!-- MOST POPULAR -->
                <div class="widget">
                    <h3 class="section-title">Most Popular</h3>
                    <div class="space-y-4">
                        @foreach(\App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take(5)->get() as $pop)
                        <a href="{{ route('frontend.post', $pop->slug) }}" class="flex gap-4 group items-center">
                            @if($pop->featured_image)
                                <div class="w-16 h-16 shrink-0 overflow-hidden relative">
                                    <img src="{{ url($pop->featured_image) }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition"></div>
                                </div>
                            @endif
                            <div>
                                <h4 class="text-sm font-bold leading-snug group-hover:text-blue-600 transition line-clamp-2 mb-1">{{ $pop->title }}</h4>
                                <div class="font-ui text-[10px] text-gray-400">{{ $pop->created_at->format('M d, Y') }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- MUST READ / MAKE IT MODERN -->
                <div class="widget">
                    <h3 class="section-title">Must Read</h3>
                    <div class="space-y-5">
                        @foreach(\App\Models\Post::where('status', 'published')->inRandomOrder()->take(1)->get() as $must)
                            <a href="{{ route('frontend.post', $must->slug) }}" class="block group mb-4">
                                @if($must->featured_image)
                                    <div class="aspect-video overflow-hidden mb-3">
                                        <img src="{{ url($must->featured_image) }}" class="w-full h-full object-cover hover-img">
                                    </div>
                                @endif
                                <h4 class="text-base font-bold leading-snug group-hover:text-blue-600 transition line-clamp-2">{{ $must->title }}</h4>
                            </a>
                        @endforeach
                        
                        @foreach(\App\Models\Post::where('status', 'published')->inRandomOrder()->take(3)->get() as $mustList)
                            <a href="{{ route('frontend.post', $mustList->slug) }}" class="block group border-t border-gray-100 pt-3">
                                <h4 class="text-sm font-bold leading-snug group-hover:text-blue-600 transition line-clamp-2">{{ $mustList->title }}</h4>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="mt-16">
        @include('themes.components.footer')
    </div>
</body>
</html>
