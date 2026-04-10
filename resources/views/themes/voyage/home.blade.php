<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Voyage Escapes') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#db2777'); // pink-600
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #fff; color: #1f2937; }
        .font-travel { font-family: 'Poppins', sans-serif; }
        
        header { background-color: rgba(255, 255, 255, 0.95) !important; border-bottom: 2px solid #f3f4f6 !important; backdrop-filter: blur(8px) !important; }
        header a { color: #111827 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .voyage-shadow { box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); }
        .text-shadow { text-shadow: 0 4px 10px rgba(0,0,0,0.5); }
    </style>
</head>
<body class="antialiased selection:bg-pink-500 selection:text-white">

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


    <!-- Hero / Featured -->
    <div class="px-4 py-8 max-w-[1400px] mx-auto">
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
            @php $hero = $featuredPosts->first(); @endphp
            <div class="relative rounded-3xl overflow-hidden h-[600px] w-full flex items-end justify-start group">
                @if($hero->featured_image)
                    <img src="{{ Str::startsWith($hero->featured_image, 'http') ? $hero->featured_image : url($hero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.03] transition duration-[1.5s]">
                @else
                    <div class="absolute inset-0 bg-pink-900"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                
                <div class="relative z-10 p-8 md:p-16 max-w-4xl text-white">
                    <a href="{{ isset($hero->category) ? route('frontend.category', $hero->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="bg-pink-600 text-white px-4 py-2 text-xs font-bold uppercase tracking-widest rounded-full mb-6 inline-block">{{ $hero->category->name ?? 'Destination' }}</span></a>
                    <a href="{{ route('frontend.post', $hero->slug) }}">
                        <h2 class="text-5xl md:text-7xl font-travel font-black tracking-tight leading-none mb-4 text-shadow hover:text-pink-300 transition line-clamp-2">{{ $hero->title }}</h2>
                    </a>
                    <p class="text-gray-200 text-lg md:text-xl font-medium mb-6 line-clamp-2 md:w-3/4">{{ strip_tags($hero->summary ?? $hero->content) }}</p>
                    <div class="flex items-center gap-4 text-sm font-bold uppercase tracking-wider">
                        <div class="w-10 h-10 rounded-full bg-white text-pink-600 flex items-center justify-center -ml-2 border-2 border-white">{{ substr($hero->user->name ?? 'A', 0, 1) }}</div>
                        <span>By {{ $hero->user->name ?? 'Explorer' }}</span>
                        <span class="opacity-50">|</span>
                        <span>{{ $hero->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                @foreach($featuredPosts->skip(1)->take(4) as $post)
                <article class="relative rounded-2xl overflow-hidden aspect-square group relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                    @if($post->featured_image)
                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-gray-900/20"></div>
                    <div class="absolute bottom-0 left-0 p-5 w-full">
                        <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-pink-400 text-[10px] font-black uppercase tracking-widest mb-1 block">{{ $post->category->name ?? 'Travel Guide' }}</span></a>
                        <h3 class="text-white text-xl font-travel font-bold leading-tight group-hover:text-pink-300 transition line-clamp-2">{{ $post->title }}</h3>
                    </div>
                </article>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-12 mt-16">
            <!-- Latest Stories -->
            <main class="lg:col-span-3">
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <div class="flex items-center justify-between mb-10 border-b-2 border-gray-100 pb-4">
                    <h3 class="text-4xl font-travel font-black text-gray-900">Latest Journeys</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($latestPosts as $post)
                    <article class="flex flex-col group">
                        <article class="w-full aspect-[4/5] rounded-3xl overflow-hidden relative voyage-shadow mb-6 relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                            @if($post->featured_image)
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover:scale-105 transition duration-700">
                            @endif
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="absolute top-4 left-4 bg-white/90 backdrop-blur text-gray-900 px-3 py-1.5 text-[10px] font-black tracking-widest uppercase rounded-full shadow-lg">
                                {{ $post->category->name ?? 'Story' }}
                            </div></a>
                        </article>
                        <div class="flex-1">
                            <a href="{{ route('frontend.post', $post->slug) }}">
                                <h4 class="text-2xl font-travel font-bold mb-3 leading-snug text-gray-900 group-hover:text-pink-600 transition line-clamp-2">{{ $post->title }}</h4>
                            </a>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-4">{{ strip_tags($post->summary ?? $post->content) }}</p>
                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                                <span class="text-xs font-bold text-gray-900">{{ $post->user->name ?? 'Traveler' }}</span>
                                <span class="text-xs font-medium text-gray-400">{{ $post->created_at->format('M d') }} &bull; {{ $post->views }} Views</span>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                
                <div class="mt-16 flex justify-center">{{ $latestPosts->links() }}</div>
                @endif
            </main>

            <!-- Sidebar -->
            <aside class="space-y-12">
                <!-- Trending Tags / Filter -->
                <div class="bg-gray-50 rounded-3xl p-8 border border-gray-100">
                    <h3 class="text-2xl font-travel font-black text-gray-900 mb-6">Popular Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(\App\Models\Category::all()->take(8) as $cat)
                            <a href="#" class="px-4 py-2 bg-white text-gray-700 hover:bg-pink-600 hover:text-white transition rounded-full text-xs font-bold shadow-sm border border-gray-200">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                </div>

                <!-- Advertising Placeholder -->
                <div class="bg-pink-600 rounded-3xl p-8 text-center text-white relative overflow-hidden voyage-shadow">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-pink-500 rounded-full blur-2xl"></div>
                    <div class="relative z-10">
                        <svg class="w-10 h-10 mx-auto mb-4 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-2xl font-travel font-black uppercase mb-2">Sponsor Spot</h3>
                        <p class="text-pink-100 text-sm mb-6">Feature your travel brand or gear here.</p>
                        <button class="bg-white text-pink-700 font-bold uppercase tracking-widest text-xs px-6 py-3 rounded-full hover:bg-gray-100 transition shadow-lg w-full">Advertise</button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @include('themes.components.footer')
</body>
</html>
