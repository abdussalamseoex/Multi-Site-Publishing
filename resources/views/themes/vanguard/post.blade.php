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
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-xl font-gaming font-bold border-b-2 border-slate-100 pb-2 mb-6 uppercase"><span class="border-b-4 border-primary pb-2.5">Follow Us</span></h3>
                <div class="flex gap-4">
                    <a href="#" class="flex-1 bg-[#1877f2] text-white p-3 rounded-lg text-center hover:opacity-90 transition shadow border border-[#1877f2]">
                        <svg class="h-5 w-5 mx-auto fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="flex-1 bg-[#1da1f2] text-white p-3 rounded-lg text-center hover:opacity-90 transition shadow border border-[#1da1f2]">
                        <svg class="h-5 w-5 mx-auto fill-current" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                </div>
            </div>

            <div class="bg-slate-900 rounded-xl p-8 text-center text-white relative overflow-hidden shadow-2xl border-2 border-slate-700">
                <div class="absolute inset-0 bg-primary opacity-20"></div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-gaming font-bold uppercase mb-2">Pro Access</h3>
                    <p class="text-slate-300 text-sm mb-6 leading-relaxed">Experience zero ads and gain early access to our premium editorial piece.</p>
                    <a href="#" class="inline-block bg-primary text-white font-bold uppercase tracking-widest text-[11px] px-6 py-3 rounded shadow-lg hover:bg-orange-500 hover:-translate-y-1 transition transform duration-200">Go Premium</a>
                </div>
            </div>
        </aside>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



