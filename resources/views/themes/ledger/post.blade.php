<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <title>{{ $post->meta_title ?? $post->title }}</title>
    <meta name="description" content="{{ $post->meta_description }}">
    @if($post->canonical_url)
    <link rel="canonical" href="{{ $post->canonical_url }}">
    @endif
    
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
        
        header { background-color: #0f172a !important; border-bottom: 2px solid var(--primary) !important; padding-top: 0.5rem; padding-bottom: 0.5rem; }
        header a { color: #f8fafc !important; }
        header .text-primary { color: #38bdf8 !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .prose { font-family: 'Inter', serif; }
        .prose p { margin-bottom: 1.5rem; font-size: 1.125rem; line-height: 1.7; color: #334155; }
        .prose img { width: 100%; margin: 2rem 0; border: 1px solid #cbd5e1; }
        .prose h2 { font-family: 'Inter', sans-serif; font-size: 2rem; font-weight: 800; color: #0f172a; margin-top: 3rem; margin-bottom: 1.25rem; border-bottom: 1px solid #cbd5e1; padding-bottom: 0.5rem;}
        .prose h3 { font-family: 'Inter', sans-serif; font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem; }
        .prose a { color: var(--primary); text-decoration: underline; font-weight: 600; }
        .prose blockquote { border-left: 4px solid var(--primary); padding-left: 1.5rem; font-style: italic; color: #475569; font-size: 1.25rem; margin: 2rem 0; font-family: Georgia, serif; }
        .prose ul { list-style-type: square; margin-bottom: 1.5rem; padding-left: 1.5rem; color: #334155;}
    </style>
</head>
<body class="antialiased">

    <!-- Ticker -->
    <div class="bg-slate-900 text-[10px] font-mono-data tracking-widest text-slate-400 py-1.5 border-b border-slate-700 text-center">
        MARKET DATA IS DELAYED BY 15 MINUTES.
    </div>

    @include('themes.components.header')

    <div class="max-w-4xl mx-auto px-4 py-12">
        
        <header class="mb-10 text-center md:text-left border-b-2 border-slate-900 pb-8">
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-sky-700 font-bold uppercase tracking-wider text-[11px] mb-4 block">{{ $post->category->name ?? 'Finance Report' }}</span></a>
            <h1 class="text-4xl md:text-6xl font-black leading-[1.1] mb-6 text-slate-900 tracking-tight line-clamp-2">{{ $post->title }}</h1>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 font-mono-data text-xs text-slate-500 uppercase">
                <div class="flex gap-4">
                    <span>AUTH: {{ $post->user->name ?? 'DESK' }}</span>
                    <span>|</span>
                    <span>PUB: {{ $post->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="bg-slate-200 px-3 py-1 rounded text-slate-700">
                    VOL: {{ number_format($post->views) }}
                </div>
            </div>
        </header>

        @if($post->featured_image)
            <figure class="mb-10">
                <div class="w-full bg-slate-200 border border-slate-300 p-1">
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-auto object-cover max-h-[500px]">
                </div>
                <figcaption class="text-right text-[10px] text-slate-400 font-mono-data mt-2 uppercase">Image Source / Visual Data</figcaption>
            </figure>
        @endif

        <article class="prose max-w-none">
            {!! $post->content !!}
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
</body>
</html>

