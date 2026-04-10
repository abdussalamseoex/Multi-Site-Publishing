<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Vanguard Elite') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;600;700&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#ea580c'); // orange-600 default
        $font = \App\Models\Setting::get('typography', 'Inter');
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: '{{ $font }}', sans-serif; background-color: #f3f4f6; }
        .font-gaming { font-family: 'Teko', sans-serif; }
        /* Dark Header Override */
        header { background-color: #0f172a !important; border-bottom: 2px solid var(--primary) !important; backdrop-filter: none !important; }
        header a, header svg { color: #e2e8f0 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
        header .bg-gray-900 { background-color: #334155 !important; }
        
        .badge-orange { background-color: #f97316; color: white; }
        .badge-blue { background-color: #3b82f6; color: white; }
        .badge-red { background-color: #ef4444; color: white; }
        .badge-green { background-color: #10b981; color: white; }
        .badge-purple { background-color: #8b5cf6; color: white; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="text-gray-900 antialiased">
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


    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Hero Section (Featured) -->
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
                @php 
                    $mainFeatured = $featuredPosts->first(); 
                    $otherFeatured = $featuredPosts->skip(1)->take(2);
                @endphp
                
                <div class="lg:col-span-2 relative rounded-xl overflow-hidden shadow-2xl group min-h-[400px]">
                    @if($mainFeatured->featured_image)
                        <img src="{{ Str::startsWith($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : url($mainFeatured->featured_image) }}" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-105">
                    @else
                        <div class="absolute inset-0 w-full h-full bg-slate-800"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-8 w-full z-10">
                        <span class="badge-orange px-3 py-1 text-xs font-black uppercase tracking-wider rounded border border-orange-500/50 shadow-lg mb-3 inline-block">Trending</span>
                        <a href="{{ route('frontend.post', $mainFeatured->slug) }}">
                            <h2 class="text-white text-4xl md:text-5xl font-gaming font-bold leading-tight mb-2 group-hover:text-primary transition line-clamp-2">{{ $mainFeatured->title }}</h2>
                        </a>
                        <div class="flex items-center text-gray-300 text-xs font-bold uppercase gap-4">
                            <span>By {{ $mainFeatured->user->name ?? 'Admin' }}</span>
                            <span>{{ $mainFeatured->created_at->format('M d, Y') }}</span>
                            <span class="flex text-yellow-400 text-sm">★★★★★</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-6">
                    @foreach($otherFeatured as $post)
                    <div class="relative rounded-xl overflow-hidden shadow-xl group h-1/2 min-h-[190px]">
                        @if($post->featured_image)
                            <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-105">
                        @else
                            <div class="absolute inset-0 w-full h-full bg-slate-800"></div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-5 w-full z-10">
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="badge-blue px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded border border-blue-500/50 mb-2 inline-block shadow-lg">{{ $post->category->name ?? 'News' }}</span></a>
                            <a href="{{ route('frontend.post', $post->slug) }}">
                                <h3 class="text-white text-2xl font-gaming font-bold leading-tight mb-1 group-hover:text-primary transition line-clamp-2">{{ $post->title }}</h3>
                            </a>
                            <div class="flex items-center text-gray-300 text-[10px] font-bold uppercase gap-2">
                                <span>{{ $post->created_at->format('M d') }}</span>
                                <span class="flex text-yellow-400">★★★★☆</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <main class="lg:col-span-3 space-y-12">
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <!-- Reviews / Latest Grid -->
                <section>
                    <h3 class="text-3xl font-gaming font-bold border-b-2 border-slate-300 pb-2 mb-6 uppercase text-gray-900"><span class="border-b-4 border-primary pb-3">Latest Reviews</span></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($latestPosts->take(6) as $index => $post)
                        @php 
                            $colors = ['badge-orange', 'badge-blue', 'badge-red', 'badge-green', 'badge-purple'];
                            $badgeClass = $colors[$index % count($colors)];
                        @endphp
                        <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden group hover:shadow-xl transition flex flex-col h-full">
                            <article class="block relative h-56 overflow-hidden relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                                @if($post->featured_image)
                                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @else
                                    <div class="w-full h-full bg-slate-200"></div>
                                @endif
                                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="{{ $badgeClass }} absolute top-3 left-3 px-3 py-1 text-[11px] font-black uppercase tracking-widest rounded shadow-lg">{{ $post->category->name ?? 'Review' }}</span></a>
                                <div class="absolute bottom-0 right-0 bg-gray-900 text-white font-bold text-[11px] px-3 py-1.5 rounded-tl-lg shadow-lg flex items-center gap-1 border-t border-l border-gray-700">
                                    <span class="text-yellow-400">★</span> {{ number_format(4 + ($post->id % 10) / 10, 1) }}
                                </div>
                            </article>
                            <div class="p-6 flex-1 flex flex-col">
                                <a href="{{ route('frontend.post', $post->slug) }}" class="mb-auto">
                                    <h4 class="text-xl font-black mb-3 group-hover:text-primary transition leading-tight line-clamp-2">{{ $post->title }}</h4>
                                </a>
                                <p class="text-gray-500 text-sm mb-4 line-clamp-2 mt-auto">{{ strip_tags($post->summary ?? $post->content) }}</p>
                                <div class="flex items-center text-xs text-gray-400 font-bold uppercase gap-2 border-t border-slate-100 pt-4">
                                    <span>{{ $post->user->name ?? 'Staff' }}</span> &bull; 
                                    <span>{{ $post->views }} Views</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
            </main>

            <!-- Sidebar -->
            <aside class="space-y-8">
                <!-- Most Popular -->
                <div class="bg-white rounded-xl shadow border border-slate-200 p-6">
                    <h3 class="text-2xl font-gaming font-bold border-b-2 border-slate-200 pb-2 mb-6 uppercase"><span class="border-b-4 border-primary pb-2.5">Most Popular</span></h3>
                    <div class="space-y-5">
                        @foreach(\App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take(5)->get() as $i => $pop)
                        <article class="flex gap-4 group items-center relative">
    <a href="{{ route('frontend.post', $pop->slug) }}" class="absolute inset-0 z-0"></a>
                            <div class="w-20 h-20 shrink-0 rounded-lg overflow-hidden bg-slate-100 border border-slate-200">
                                @if($pop->featured_image)
                                    <img src="{{ Str::startsWith($pop->featured_image, 'http') ? $pop->featured_image : url($pop->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition">
                                @endif
                            </div>
                            <div class="flex flex-col justify-center">
                                <a href="{{ isset($pop->category) ? route('frontend.category', $pop->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] font-black uppercase text-primary tracking-wider mb-1">{{ $pop->category->name ?? 'Trending' }}</span></a>
                                <h4 class="text-sm font-bold leading-snug group-hover:text-primary transition">{{ Str::limit($pop->title, 45) }}</h4>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>

                <!-- Advertising Setup -->
                @php $adSidebar = \App\Models\Setting::get('ad_sidebar_code'); @endphp
                @if($adSidebar)
                    <div class="w-full flex justify-center mb-6 overflow-hidden">
                        {!! $adSidebar !!}
                    </div>
                @else
                    <div class="bg-slate-900 rounded-xl p-6 text-center shadow-xl border border-slate-800">
                        <span class="text-[10px] font-black uppercase text-slate-500 tracking-widest mb-3 block">Advertisement</span>
                        <div class="w-full bg-slate-800 h-[250px] flex items-center justify-center rounded border border-slate-700">
                            <span class="text-slate-600 font-mono text-xs font-bold">300x250 Ad Space</span>
                        </div>
                    </div>
                @endif
            </aside>
        </div>

        <!-- Dark Themed Section (Blogs / Deep Dive) -->
        <div class="mt-16 bg-[#0f172a] rounded-2xl overflow-hidden shadow-2xl p-8 lg:p-12 text-white border-t-4 border-primary relative">
            <h3 class="text-3xl font-gaming font-bold border-b-2 border-slate-700 pb-2 mb-8 uppercase text-white"><span class="border-b-4 border-primary pb-2.5">Deep Dive Blogs</span></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach($latestPosts->skip(6)->take(4) as $index => $post)
                <article class="flex flex-col sm:flex-row gap-6 group items-center bg-slate-800/50 p-4 rounded-xl border border-slate-700/50 hover:bg-slate-800 transition relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                    <div class="w-full sm:w-40 h-32 shrink-0 rounded-lg overflow-hidden bg-slate-800 relative shadow-inner border border-slate-700">
                        @if($post->featured_image)
                            <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition duration-500 group-hover:scale-105">
                        @endif
                        <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="absolute top-2 left-2 bg-blue-600 text-white px-2 py-0.5 text-[9px] font-black uppercase rounded shadow-lg">{{ $post->category->name ?? 'Blog' }}</span></a>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold leading-tight mb-2 group-hover:text-blue-400 transition line-clamp-2">{{ $post->title }}</h4>
                        <p class="text-slate-400 text-sm mb-3 line-clamp-2 leading-relaxed">{{ strip_tags($post->summary ?? $post->content) }}</p>
                        <span class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">{{ $post->user->name ?? 'Writer' }} &bull; {{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </article>
                @endforeach
            </div>
            @if($latestPosts->hasMorePages())
            <div class="mt-10 text-center">
                <a href="{{ $latestPosts->nextPageUrl() }}" class="inline-block px-8 py-3 bg-slate-800 text-white font-bold uppercase tracking-widest text-sm rounded shadow border border-slate-700 hover:bg-slate-700 transition">Load More Content</a>
            </div>
            @endif
        </div>
    </div>

    @include('themes.components.footer')
</body>
</html>
