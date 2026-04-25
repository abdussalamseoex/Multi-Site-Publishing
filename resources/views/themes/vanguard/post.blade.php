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
    <meta property="og:url" content="{{ request()->url() }}">
    @if($post->featured_image)
    <meta property="og:image" content="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;600;700&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#ea580c');
        $font = \App\Models\Setting::get('typography', 'Inter');
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: '{{ $font }}', sans-serif; background-color: #f1f5f9; }
        .font-gaming { font-family: 'Teko', sans-serif; }
        
        /* Dark Header Override */
        header { background-color: #0f172a !important; border-bottom: 2px solid var(--primary) !important; backdrop-filter: none !important; }
        header a, header svg { color: #e2e8f0 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
        header .bg-gray-900 { background-color: #334155 !important; }

        .prose p { margin-bottom: 1.5rem; font-size: 1.125rem; line-height: 1.8; color: #334155; }
        .prose img { width: 100%; border-radius: 0.75rem; margin-top: 2.5rem; margin-bottom: 2.5rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .prose h2 { font-family: 'Teko', sans-serif; font-size: 3rem; font-weight: bold; color: #0f172a; margin-top: 3rem; margin-bottom: 1rem; line-height: 1.1; text-transform: uppercase; letter-spacing: 0.025em; }
        .prose h3 { font-family: 'Teko', sans-serif; font-size: 2.25rem; font-weight: 600; color: #1e293b; margin-top: 2rem; margin-bottom: 1rem; }
        .prose a { color: var(--primary); text-decoration: none; font-weight: bold; border-bottom: 2px solid transparent; transition: border-color 0.2s; }
        .prose a:hover { border-bottom-color: var(--primary); }
        .prose blockquote { border-left: 4px solid var(--primary); padding-left: 1.5rem; font-style: italic; color: #64748b; font-size: 1.25rem; margin-top: 2rem; margin-bottom: 2rem; background-color: #f8fafc; padding: 1.5rem; border-radius: 0 0.5rem 0.5rem 0;}
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="text-slate-900 antialiased">
    @include('themes.components.header')

    <!-- Post Header (Dark) -->
    <div class="relative bg-[#0f172a] pt-20 pb-32 border-b-4 shadow-xl" style="border-color: var(--primary);">
        <div class="absolute inset-0 overflow-hidden opacity-20 pointer-events-none">
            @if($post->featured_image)
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover blur-md scale-110">
            @endif
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/40"></div>
        <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="bg-primary text-white px-4 py-1 text-xs font-black uppercase tracking-widest rounded shadow-xl border border-white/20 mb-6 inline-block">{{ $post->category->name ?? 'Article' }}</span></a>
            <h1 class="text-4xl md:text-6xl font-gaming font-bold text-white leading-tight mb-8">{{ $post->title }}</h1>
            <div class="flex items-center justify-center gap-6 text-slate-300 text-sm font-bold uppercase tracking-wider font-sans bg-slate-800/50 inline-flex px-6 py-2 rounded-full border border-slate-700">
                <span class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-white text-[10px]">{{ substr($post->user->name ?? 'A', 0, 1) }}</div>
                    {{ $post->user->name ?? 'Editorial Staff' }}
                </span>
                <span class="text-slate-500">|</span>
                <span>{{ $post->created_at->format('M d, Y') }}</span>
                <span class="text-slate-500">|</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> {{ $post->views }}</span>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-0 grid grid-cols-1 lg:grid-cols-4 gap-12 -mt-16 relative z-20">
        <article class="lg:col-span-3">
            @if($post->featured_image)
                <div class="w-full mb-12 rounded-2xl overflow-hidden shadow-2xl border-4 border-white bg-slate-100">
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-auto object-cover max-h-[600px] hover:scale-105 transition duration-700">
                </div>
            @endif

            <div class="bg-white p-8 md:p-12 rounded-2xl shadow border border-slate-200 font-sans">
                <div class="prose max-w-none">
                    {!! \App\Helpers\AdHelper::injectInArticleAds($post->content) !!}
                </div>
                
                <!-- Interaction Bar -->
                <div class="mt-12 pt-8 border-t border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex gap-2">
                        @foreach(\App\Models\Category::all()->take(3) as $cat)
                            <a href="#" class="px-3 py-1.5 bg-slate-100 border border-slate-200 hover:bg-primary hover:border-primary hover:text-white transition rounded text-[10px] font-black uppercase tracking-widest text-slate-500">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- More Like This (Vanguard Style) -->
            <div class="mt-16 bg-white p-8 rounded-2xl shadow border border-slate-200">
                <h3 class="text-3xl font-gaming font-bold border-b-2 border-slate-200 pb-2 mb-8 uppercase text-slate-900"><span class="border-b-4 border-primary pb-2.5">More Recommended</span></h3>
                @php 
                    $relatedList = \App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->take(3)->get();
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach($relatedList as $rel)
                        <article class="group block bg-slate-50 rounded-xl overflow-hidden border border-slate-200 hover:border-primary hover:shadow-xl transition relative">
    <a href="{{ route('frontend.post', $rel->slug) }}" class="absolute inset-0 z-0"></a>
                            @if($rel->featured_image)
                                <div class="aspect-[4/3] bg-slate-200 overflow-hidden relative">
                                    <img src="{{ url($rel->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                </div>
                            @endif
                            <div class="p-5 border-t border-slate-200">
                                <a href="{{ isset($rel->category) ? route('frontend.category', $rel->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[9px] font-black uppercase text-slate-500 tracking-widest mb-2 block group-hover:text-primary transition">{{ $rel->category->name ?? 'Article' }}</span></a>
                                <h4 class="font-bold text-lg leading-tight text-slate-800 transition">{{ Str::limit($rel->title, 50) }}</h4>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </article>

        <!-- Sidebar -->
        <aside class="space-y-8 mt-24">
            @php
                $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                
                if (empty($sidebarBlocks)) {
                    $sidebarBlocks = [
                        ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Most Popular', 'limit' => 5],
                        ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad']
                    ];
                }
            @endphp

            @foreach($sidebarBlocks as $block)
                @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                    @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                @else
                    @include("themes.good.components.{$block['type']}", ['block' => $block])
                @endif
            @endforeach
        </aside>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



