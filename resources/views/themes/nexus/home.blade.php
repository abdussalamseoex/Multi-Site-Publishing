<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Nexus Tech') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#6366f1'); // Indigo
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .font-tech { font-family: 'Space Grotesk', sans-serif; }
        
        /* Dark Header Override */
        header { background-color: rgba(15, 23, 42, 0.9) !important; border-bottom: 1px solid #1e293b !important; backdrop-filter: blur(12px) !important; }
        header a, header svg { color: #f1f5f9 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
        
        .glass { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .hover-card { transition: all 0.3s ease; }
        .hover-card:hover { transform: translateY(-4px); border-color: var(--primary); box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.3); }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500 selection:text-white">
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
    <div class="max-w-7xl mx-auto px-4 py-12">
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
            <div class="mb-16">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-tech font-bold flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </span>
                        TOP INNOVATIONS
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    @php $mainFeatured = $featuredPosts->first(); @endphp
                    <article class="block relative rounded-2xl overflow-hidden glass hover-card min-h-[400px] group relative">
    <a href="{{ route('frontend.post', $mainFeatured->slug) }}" class="absolute inset-0 z-0"></a>
                        @if($mainFeatured->featured_image)
                            <img src="{{ Str::startsWith($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : url($mainFeatured->featured_image) }}" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-80 transition duration-500">
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 to-slate-900 opacity-80"></div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 p-8">
                            <a href="{{ isset($mainFeatured->category) ? route('frontend.category', $mainFeatured->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="bg-indigo-600 text-white px-3 py-1 text-[10px] font-tech tracking-widest uppercase rounded shadow mb-4 inline-block">{{ $mainFeatured->category->name ?? 'Software' }}</span></a>
                            <h3 class="text-3xl md:text-4xl font-tech font-bold leading-tight mb-3 line-clamp-2">{{ $mainFeatured->title }}</h3>
                            <p class="text-slate-300 text-sm mb-4 line-clamp-2 md:w-5/6">{{ strip_tags($mainFeatured->summary ?? $mainFeatured->content) }}</p>
                            <div class="flex items-center text-xs text-indigo-300 uppercase tracking-wider font-tech gap-3">
                                <span>{{ $mainFeatured->created_at->format('M d, Y') }}</span>
                                <span>&bull;</span>
                                <span>{{ $mainFeatured->views }} Readers</span>
                            </div>
                        </div>
                    </article>

                    <div class="flex flex-col gap-8">
                        @foreach($featuredPosts->skip(1)->take(2) as $post)
                        <article class="flex-1 relative rounded-2xl overflow-hidden glass hover-card flex items-end p-6 group relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                            @if($post->featured_image)
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-60 transition duration-500">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/20"></div>
                            <div class="relative z-10 w-full">
                                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] font-tech text-indigo-400 tracking-widest uppercase mb-2 block">{{ $post->category->name ?? 'Review' }}</span></a>
                                <h3 class="text-2xl font-tech font-bold leading-snug mb-2 line-clamp-2">{{ $post->title }}</h3>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mt-12">
            <!-- Latest Grid -->
            <main class="lg:col-span-2">
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-8">
                    <h3 class="text-2xl font-tech font-bold text-white uppercase tracking-wider">Latest Intel</h3>
                    <span class="text-xs text-slate-400">Auto-updating</span>
                </div>
                
                <div class="space-y-6">
                    @foreach($latestPosts as $post)
                    <article class="glass p-5 rounded-2xl hover-card flex flex-col md:flex-row gap-6 items-center">
                        <a href="{{ route('frontend.post', $post->slug) }}" class="w-full md:w-48 h-32 shrink-0 rounded-xl overflow-hidden bg-slate-800 relative">
                            @if($post->featured_image)
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover opacity-80 hover:opacity-100 transition">
                            @endif
                        </a>
                        <div class="flex-1">
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-indigo-400 text-[10px] font-tech tracking-widest uppercase mb-2 block">{{ $post->category->name ?? 'Report' }}</span></a>
                            <a href="{{ route('frontend.post', $post->slug) }}">
                                <h4 class="text-xl font-tech font-bold mb-2 leading-snug hover:text-indigo-400 transition line-clamp-2">{{ $post->title }}</h4>
                            </a>
                            <p class="text-slate-400 text-sm line-clamp-2 mb-3">{{ strip_tags($post->summary ?? $post->content) }}</p>
                            <div class="text-xs text-slate-500 font-tech">By {{ $post->user->name ?? 'System' }} &bull; {{ $post->created_at->diffForHumans() }}</div>
                        </div>
                    </article>
                    @endforeach
                </div>
                
                <div class="mt-10">{{ $latestPosts->links() }}</div>
                @endif
            </main>

            <!-- Sidebar -->
            <aside class="space-y-10">
                <!-- Trending / Market Watch -->
                <div class="glass p-6 rounded-2xl">
                    <h3 class="text-lg font-tech font-bold border-b border-slate-700 pb-3 mb-5 uppercase tracking-widest text-indigo-400">Trending Now</h3>
                    <div class="space-y-4">
                        @foreach(\App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take(6)->get() as $pop)
                        <a href="{{ route('frontend.post', $pop->slug) }}" class="block group">
                            <h4 class="text-sm font-medium leading-snug group-hover:text-indigo-400 transition">{{ Str::limit($pop->title, 60) }}</h4>
                            <div class="text-[10px] text-slate-500 font-tech mt-1">{{ number_format($pop->views) }} Views</div>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Newsletter (Tech style) -->
                <div class="rounded-2xl p-8 bg-gradient-to-br from-indigo-900/50 to-slate-900 border border-indigo-500/30 text-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    <svg class="w-8 h-8 text-indigo-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    <h3 class="text-xl font-tech font-bold mb-2">Join the Network</h3>
                    <p class="text-slate-400 text-sm mb-6">Receive encrypted weekly reports on SaaS and AI advancements.</p>
                    <div class="flex flex-col gap-3">
                        <input type="email" placeholder="Email Node" class="bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-center focus:border-indigo-500 focus:outline-none">
                        <button class="bg-indigo-600 hover:bg-indigo-500 text-white font-tech font-bold tracking-widest text-xs uppercase py-3 rounded-lg transition shadow-[0_0_15px_rgba(79,70,229,0.3)]">Subscribe</button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @include('themes.components.footer')
</body>
</html>
