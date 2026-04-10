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
    
    <!-- Open Graph & SEO -->
    <meta property="og:title" content="{{ $post->meta_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->meta_description }}">
    <meta property="og:url" content="{{ request()->url() }}">
    @if($post->featured_image)
    <meta property="og:image" content="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#6366f1'); // Indigo
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .font-tech { font-family: 'Space Grotesk', sans-serif; }
        
        header { background-color: rgba(15, 23, 42, 0.9) !important; border-bottom: 1px solid #1e293b !important; backdrop-filter: blur(12px) !important; }
        header a, header svg { color: #f1f5f9 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); }

        .prose p { margin-bottom: 1.5rem; font-size: 1.125rem; line-height: 1.7; color: #cbd5e1; }
        .prose img { width: 100%; border-radius: 0.5rem; margin-top: 2rem; margin-bottom: 2rem; border: 1px solid #334155; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5); }
        .prose h2 { font-family: 'Space Grotesk', sans-serif; font-size: 2.25rem; font-weight: 700; color: #f8fafc; margin-top: 3rem; margin-bottom: 1.25rem; }
        .prose h3 { font-family: 'Space Grotesk', sans-serif; font-size: 1.75rem; font-weight: 600; color: #e2e8f0; margin-top: 2rem; margin-bottom: 1rem; }
        .prose a { color: var(--primary); text-decoration: none; border-bottom: 1px dashed var(--primary); transition: all 0.2s; }
        .prose a:hover { color: #818cf8; border-bottom-style: solid; }
        .prose blockquote { border-left: 3px solid var(--primary); padding-left: 1.5rem; font-style: italic; color: #94a3b8; font-size: 1.25rem; margin: 2rem 0; background: rgba(255, 255, 255, 0.02); padding: 1.5rem; rounded-r-lg; }
        .prose strong { color: #f8fafc; font-weight: 700; }
        .prose ul, .prose ol { color: #cbd5e1; margin-bottom: 1.5rem; padding-left: 1.5rem; font-size: 1.125rem;}
        .prose li { margin-bottom: 0.5rem; }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500 selection:text-white pb-12">

    @include('themes.components.header')

    <div class="max-w-4xl mx-auto px-4 py-16">
        <div class="text-center mb-12">
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-indigo-400 font-tech tracking-widest text-[10px] uppercase border border-indigo-500/30 px-3 py-1 rounded-full bg-indigo-500/10 mb-6 inline-block">{{ $post->category->name ?? 'Technology' }}</span></a>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-tech font-bold leading-tight mb-8 line-clamp-2">{{ $post->title }}</h1>
            
            <div class="flex items-center justify-center gap-6 text-slate-400 text-xs font-tech tracking-wide border-y border-slate-800 py-4">
                <span class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-slate-800 rounded flex items-center justify-center text-indigo-400 border border-slate-700">{{ substr($post->user->name ?? 'S', 0, 1) }}</div>
                    {{ $post->user->name ?? 'System Admin' }}
                </span>
                <span>/</span>
                <span>{{ $post->created_at->format('M d, Y') }}</span>
                <span>/</span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    {{ $post->views }}
                </span>
            </div>
        </div>

        @if($post->featured_image)
            <div class="w-full mb-16 rounded-xl overflow-hidden glass border border-slate-700 p-2 shadow-2xl">
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-auto object-cover rounded-lg">
            </div>
        @endif

        <article class="prose max-w-none">
            {!! $post->content !!}
        </article>

        <!-- Related Modules -->
        <div class="mt-20 pt-10 border-t border-slate-800">
            <h3 class="text-2xl font-tech font-bold text-white mb-8 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                Related Architectures
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach(\App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->take(2)->get() as $rel)
                    <a href="{{ route('frontend.post', $rel->slug) }}" class="glass p-5 rounded-xl hover:border-indigo-500/50 transition group flex items-start gap-4">
                        @if($rel->featured_image)
                            <img src="{{ url($rel->featured_image) }}" class="w-20 h-20 rounded bg-slate-800 object-cover shrink-0">
                        @endif
                        <div>
                            <h4 class="font-bold text-sm text-slate-200 leading-snug group-hover:text-indigo-400 transition line-clamp-2">{{ $rel->title }}</h4>
                            <div class="text-[10px] text-slate-500 font-tech mt-2">{{ $rel->created_at->format('M d, Y') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @include('themes.components.footer')
</body>
</html>

