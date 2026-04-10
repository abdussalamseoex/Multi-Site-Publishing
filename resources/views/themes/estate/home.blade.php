<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Estate Premier') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,800;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#b45309'); // amber-700
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Lato', sans-serif; background-color: #fafafa; color: #1c1917; }
        .font-elegant { font-family: 'Playfair Display', serif; }
        
        header { background-color: #1c1917 !important; border-bottom: none !important; padding: 1rem 0;}
        header a, header svg { color: #f5f5f4 !important; }
        header .text-primary { color: #d97706 !important; } /* amber-600 */
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .gold-border { border-color: #d97706; }
        .bg-gold { background-color: #d97706; color: white; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased">

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


    <!-- Hero Showcase -->
    @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
        @php $hero = $featuredPosts->first(); @endphp
        <div class="relative w-full h-[70vh] min-h-[500px] flex items-center justify-center">
            @if($hero->featured_image)
                <img src="{{ Str::startsWith($hero->featured_image, 'http') ? $hero->featured_image : url($hero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="absolute inset-0 bg-stone-800"></div>
            @endif
            <div class="absolute inset-0 bg-stone-900/60"></div>
            
            <div class="relative z-10 text-center max-w-4xl px-4 mt-16">
                <a href="{{ isset($hero->category) ? route('frontend.category', $hero->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-amber-500 font-bold tracking-[0.2em] uppercase text-xs mb-4 block">{{ $hero->category->name ?? 'Exclusive' }}</span></a>
                <a href="{{ route('frontend.post', $hero->slug) }}">
                    <h2 class="text-5xl md:text-7xl font-elegant font-bold text-white leading-tight mb-6 line-clamp-2">{{ $hero->title }}</h2>
                </a>
                <p class="text-stone-200 text-lg md:text-xl font-light mb-8">{{ Str::limit(strip_tags($hero->summary ?? $hero->content), 120) }}</p>
                <div class="flex items-center justify-center gap-6 text-stone-300 text-sm tracking-widest uppercase">
                    <span>{{ $hero->user->name ?? 'Broker' }}</span>
                    <span>|</span>
                    <span>{{ $hero->created_at->format('F Y') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 py-16">
        
        <!-- Featured Properties / Guides -->
        @if($featuredPosts->count() > 1)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20 -mt-24 relative z-20">
            @foreach($featuredPosts->skip(1)->take(3) as $post)
            <article class="bg-white shadow-2xl shadow-stone-200/50 block group relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                <div class="aspect-[4/3] overflow-hidden relative">
                    @if($post->featured_image)
                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                    @endif
                    <div class="absolute inset-0 bg-stone-900/20 group-hover:bg-transparent transition"></div>
                </div>
                <div class="p-8 text-center border-b-4 border-transparent group-hover:gold-border transition duration-300">
                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] text-amber-600 font-bold uppercase tracking-[0.15em] mb-3 block">{{ $post->category->name ?? 'Design' }}</span></a>
                    <h3 class="text-2xl font-elegant font-bold text-stone-900 leading-snug line-clamp-2">{{ $post->title }}</h3>
                </div>
            </article>
            @endforeach
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 mt-12">
            <!-- Latest Articles -->
            <main class="lg:col-span-8 space-y-12">
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <div class="border-b border-stone-200 pb-4 mb-10 flex items-center justify-between">
                    <h3 class="text-3xl font-elegant font-bold text-stone-900 italic">Market Insights</h3>
                </div>
                
                <div class="space-y-12">
                    @foreach($latestPosts as $post)
                    <article class="flex flex-col md:flex-row gap-8 group">
                        <a href="{{ route('frontend.post', $post->slug) }}" class="w-full md:w-5/12 aspect-[4/3] relative overflow-hidden bg-stone-100">
                            @if($post->featured_image)
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                            @endif
                        </a>
                        <div class="w-full md:w-7/12 flex flex-col justify-center">
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-xs text-amber-600 font-bold uppercase tracking-[0.15em] mb-3 block">{{ $post->category->name ?? 'Market' }}</span></a>
                            <a href="{{ route('frontend.post', $post->slug) }}">
                                <h4 class="text-3xl font-elegant font-bold mb-4 text-stone-900 leading-tight group-hover:text-amber-700 transition line-clamp-2">{{ $post->title }}</h4>
                            </a>
                            <p class="text-stone-500 font-light leading-relaxed mb-6">{{ strip_tags($post->summary ?? $post->content) }}</p>
                            <div class="flex items-center text-[11px] text-stone-400 tracking-[0.1em] uppercase">
                                <span>{{ $post->created_at->format('M d, Y') }}</span>
                                <span class="mx-3 border-l border-stone-300 h-3"></span>
                                <span>By {{ $post->user->name ?? 'Agent' }}</span>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                
                <div class="mt-16 text-center border-t border-stone-200 pt-8">{{ $latestPosts->links() }}</div>
                @endif
            </main>

            <!-- Sidebar -->
            <aside class="lg:col-span-4 space-y-12">
                <!-- Agency Widget -->
                <div class="bg-stone-900 text-center p-10 text-white relative">
                    <div class="w-16 h-16 bg-amber-600 rounded-full mx-auto flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h3 class="text-2xl font-elegant font-bold mb-3 italic">List With Us</h3>
                    <p class="text-stone-400 font-light text-sm mb-8">Exclusive marketing for exceptional properties worldwide.</p>
                    <a href="#" class="inline-block border border-amber-600 text-amber-500 hover:bg-amber-600 hover:text-white transition px-8 py-3 text-xs tracking-[0.2em] uppercase font-bold">Contact Agent</a>
                </div>

                <!-- Popular Local -->
                <div>
                    <h3 class="text-xl font-elegant font-bold border-b border-stone-200 pb-3 mb-6">Trending Areas</h3>
                    <ul class="space-y-4">
                        @foreach(\App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take(4)->get() as $pop)
                        <li class="flex items-center gap-4 group">
                            <a href="{{ route('frontend.post', $pop->slug) }}" class="w-24 h-24 shrink-0 overflow-hidden bg-stone-100 flex-1">
                                @if($pop->featured_image)
                                    <img src="{{ url($pop->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                @endif
                            </a>
                            <div class="flex-[2]">
                                <a href="{{ route('frontend.post', $pop->slug) }}" class="font-elegant font-bold text-lg leading-snug group-hover:text-amber-700 transition">{{ $pop->title }}</a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


