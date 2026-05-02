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

    
    <meta property="og:title" content="{{ $post->meta_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->meta_description }}">
    @if($post->featured_image)
    <meta property="og:image" content="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#0369a1');
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .font-mono-data { font-family: 'Roboto Mono', monospace; }
        
        .site-header { background-color: #0f172a !important; border-bottom: 2px solid var(--primary) !important; padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .site-header a { color: #f8fafc !important; }
        .site-header .text-primary { color: #38bdf8 !important; }
        .site-header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .prose { font-family: 'Inter', sans-serif; }
        .prose p { margin-bottom: 1.5rem; font-size: 1.125rem; line-height: 1.8; color: #334155; }
        .prose img { width: 100%; margin: 2rem 0; border: 1px solid #cbd5e1; border-radius: 4px; }
        .prose h2 { font-family: 'Inter', sans-serif; font-size: 2.25rem; font-weight: 800; color: #0f172a; margin-top: 3.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.5rem; letter-spacing: -0.025em;}
        .prose h3 { font-family: 'Inter', sans-serif; font-size: 1.75rem; font-weight: 700; color: #0f172a; margin-top: 2.5rem; margin-bottom: 1.25rem; }
        .prose a { color: var(--primary); text-decoration: none; font-weight: 700; border-bottom: 2px solid rgba(3, 105, 161, 0.2); transition: border-color 0.2s; }
        .prose a:hover { border-bottom-color: var(--primary); }
        .prose blockquote { border-left: 5px solid #0f172a; padding-left: 2rem; font-style: italic; color: #1e293b; font-size: 1.35rem; margin: 3rem 0; font-family: 'Inter', sans-serif; font-weight: 500; }
        .prose ul { list-style-type: square; margin-bottom: 1.5rem; padding-left: 1.5rem; color: #334155;}
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased">

    <!-- Ticker -->
    <div class="bg-[#0f172a] text-[10px] font-mono-data tracking-[0.2em] text-slate-400 py-2 border-b border-slate-800 text-center uppercase">
        <span class="animate-pulse">●</span> Market data is delayed by 15 minutes
    </div>

    @include('themes.components.header')

    <div class="max-w-5xl mx-auto px-4 py-16">
        
        <div class="mb-12">
            <nav class="flex items-center space-x-2 text-[11px] font-bold uppercase tracking-widest mb-6">
                <a href="{{ route('home') }}" class="text-slate-400 hover:text-sky-700 transition">Home</a>
                <span class="text-slate-300">/</span>
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="text-sky-700 hover:opacity-80 transition">{{ $post->category->name ?? 'Finance' }}</a>
            </nav>

            <h1 class="text-4xl md:text-7xl font-black leading-[1.05] mb-10 text-slate-900 tracking-tightest">{{ $post->title }}</h1>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between py-6 border-y-2 border-slate-900 gap-6">
                <div class="flex items-center gap-6 font-mono-data text-[11px] text-slate-500 uppercase tracking-wider">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-300">BY</span>
                        <span class="text-slate-900 font-bold">{{ $post->user->name ?? 'THE DESK' }}</span>
                    </div>
                    <div class="w-1 h-1 bg-slate-300 rounded-full"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-300">PUBLISHED</span>
                        <span class="text-slate-900 font-bold">{{ $post->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-end">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Article Volume</span>
                        <span class="text-lg font-black text-slate-900 font-mono-data">{{ number_format($post->views) }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if($post->featured_image)
            <figure class="mb-10">
                <div class="w-full bg-slate-200 border border-slate-300 p-1">
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-auto object-cover max-h-[500px]">
                </div>
                <figcaption class="text-right text-[10px] text-slate-400 font-mono-data mt-2 uppercase">Image Source / Visual Data</figcaption>
            </figure>
        @endif

        <article class="prose max-w-none">
            {!! \App\Helpers\AdHelper::injectInArticleAds($post->content) !!}
        </article>

        <!-- Related Finance Data -->
        <div class="mt-24 border-t-4 border-slate-900 pt-8">
            <h3 class="text-2xl font-black uppercase tracking-wider mb-6">Further Analysis</h3>
            
            <div class="flex flex-col divide-y divide-slate-200 border-t border-b border-slate-200">
                @foreach(\App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->take(3)->get() as $rel)
                    <a href="{{ route('frontend.post', $rel->slug) }}" class="py-4 flex justify-between items-center group hover:bg-slate-50 transition px-2">
                        <div class="flex-1">
                            <h4 class="font-bold text-lg text-slate-800 group-hover:text-sky-700 transition line-clamp-2">{{ $rel->title }}</h4>
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono-data ml-4 shrink-0 uppercase">{{ $rel->created_at->format('M d') }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-slate-900 border-t border-slate-700">
        @include('themes.components.footer')
    </div>
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



