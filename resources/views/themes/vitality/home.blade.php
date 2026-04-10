<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Vitality Wellness') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#10b981'); // emerald-500
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        .font-wellness { font-family: 'Outfit', sans-serif; }
        
        header { background-color: #ffffff !important; border-bottom: 1px solid #e2e8f0 !important; }
        header a { color: #475569 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
        header .bg-gray-900 { background-color: #f1f5f9 !important; color: #475569 !important; }

        .vitality-card { background: white; border-radius: 1.5rem; box-shadow: 0 10px 40px -10px rgba(16, 185, 129, 0.08); transition: all 0.3s ease; border: 1px solid #f1f5f9;}
        .vitality-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.2); }
    </style>
</head>
<body class="antialiased selection:bg-emerald-500 selection:text-white">

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
    <div class="max-w-7xl mx-auto px-4 py-10">
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
            @php $hero = $featuredPosts->first(); @endphp
            <div class="relative rounded-[2rem] overflow-hidden mb-16 h-[500px] shadow-2xl flex items-center group border-4 border-white">
                @if($hero->featured_image)
                    <img src="{{ Str::startsWith($hero->featured_image, 'http') ? $hero->featured_image : url($hero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-1000">
                @else
                    <div class="absolute inset-0 bg-emerald-900"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/90 via-emerald-900/60 to-transparent"></div>
                
                <div class="relative z-10 max-w-2xl px-10 md:px-16 text-white">
                    <a href="{{ isset($hero->category) ? route('frontend.category', $hero->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="bg-white text-emerald-700 px-4 py-1.5 text-xs font-wellness font-bold uppercase tracking-widest rounded-full shadow-lg mb-6 inline-block">{{ $hero->category->name ?? 'Wellness' }}</span></a>
                    <a href="{{ route('frontend.post', $hero->slug) }}">
                        <h2 class="text-4xl md:text-6xl font-wellness font-bold leading-tight mb-4 drop-shadow-md line-clamp-2">{{ $hero->title }}</h2>
                    </a>
                    <p class="text-emerald-50 text-lg mb-8 font-light line-clamp-2 leading-relaxed">{{ strip_tags($hero->summary ?? $hero->content) }}</p>
                    <a href="{{ route('frontend.post', $hero->slug) }}" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold px-6 py-3 rounded-full transition shadow-lg shadow-emerald-500/30">
                        Read Guide <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                @foreach($featuredPosts->skip(1)->take(3) as $post)
                <article class="vitality-card p-4 flex flex-col group relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                    <div class="w-full aspect-video rounded-xl overflow-hidden mb-5 relative">
                        @if($post->featured_image)
                            <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                        <div class="absolute inset-0 bg-emerald-900/10 group-hover:bg-transparent transition"></div>
                    </div>
                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest mb-2 px-2">{{ $post->category->name ?? 'Health' }}</span></a>
                    <h3 class="text-xl font-wellness font-bold leading-tight mb-3 px-2 group-hover:text-emerald-600 transition line-clamp-2">{{ $post->title }}</h3>
                </article>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mt-12">
            <!-- Latest Wellness Articles -->
            <main class="lg:col-span-2">
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-3xl font-wellness font-bold text-slate-800 tracking-tight">Recent Insights</h3>
                </div>
                
                <div class="space-y-8">
                    @foreach($latestPosts as $post)
                    <article class="vitality-card p-5 flex flex-col sm:flex-row gap-6 items-center">
                        <a href="{{ route('frontend.post', $post->slug) }}" class="w-full sm:w-1/3 aspect-[4/3] rounded-2xl overflow-hidden relative">
                            @if($post->featured_image)
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                            @endif
                        </a>
                        <div class="flex-1 py-2">
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-emerald-500 text-[10px] font-bold tracking-widest uppercase mb-2 block bg-emerald-50 inline-block px-3 py-1 rounded-full">{{ $post->category->name ?? 'Article' }}</span></a>
                            <a href="{{ route('frontend.post', $post->slug) }}">
                                <h4 class="text-2xl font-wellness font-bold mb-3 leading-snug text-slate-800 hover:text-emerald-600 transition line-clamp-2">{{ $post->title }}</h4>
                            </a>
                            <p class="text-slate-500 text-sm line-clamp-2 mb-4 leading-relaxed">{{ strip_tags($post->summary ?? $post->content) }}</p>
                            <div class="flex items-center text-xs text-slate-400 gap-3">
                                <span class="font-medium text-slate-600">{{ $post->user->name ?? 'Expert' }}</span>
                                <span>&bull;</span>
                                <span>{{ $post->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                
                <div class="mt-12">{{ $latestPosts->links() }}</div>
                @endif
            </main>

            <!-- Sidebar -->
            <aside class="space-y-8">
                <!-- Trending Wellness -->
                <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <h3 class="text-xl font-wellness font-bold text-slate-800 mb-6">Trending Topics</h3>
                    <div class="space-y-6">
                        @foreach(\App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take(5)->get() as $pop)
                        <article class="flex gap-4 group relative">
    <a href="{{ route('frontend.post', $pop->slug) }}" class="absolute inset-0 z-0"></a>
                            <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0">
                                @if($pop->featured_image)
                                    <img src="{{ url($pop->featured_image) }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-slate-700 leading-snug group-hover:text-emerald-600 transition">{{ Str::limit($pop->title, 50) }}</h4>
                                <a href="{{ isset($pop->category) ? route('frontend.category', $pop->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">{{ $pop->category->name ?? 'Lifestyle' }}</div></a>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="bg-emerald-50 rounded-[2rem] p-8 text-center border border-emerald-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-200 rounded-full blur-3xl opacity-50 -mr-10 -mt-10"></div>
                    <h3 class="text-2xl font-wellness font-bold text-emerald-900 mb-2 relative z-10">Stay Healthy</h3>
                    <p class="text-emerald-700 text-sm mb-6 relative z-10 leading-relaxed">Join our community for weekly tips on wellness and natural living.</p>
                    <div class="flex flex-col gap-3 relative z-10">
                        <input type="email" placeholder="Your best email" class="w-full bg-white border-0 rounded-xl px-4 py-3 text-sm text-center shadow-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        <button class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm py-3 rounded-xl transition shadow-lg shadow-emerald-600/30">Join Free</button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @include('themes.components.footer')
</body>
</html>

