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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#10b981');
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; }
        .font-wellness { font-family: 'Outfit', sans-serif; }
        
        header { background-color: #ffffff !important; border-bottom: 1px solid #e2e8f0 !important; }
        header a { color: #475569 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .prose { font-family: 'Inter', sans-serif; }
        .prose p { margin-bottom: 1.5rem; font-size: 1.125rem; line-height: 1.8; color: #475569; }
        .prose img { width: 100%; border-radius: 1.5rem; margin: 3rem 0; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05); }
        .prose h2 { font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 700; color: #1e293b; margin-top: 3rem; margin-bottom: 1.5rem; line-height: 1.2; }
        .prose h3 { font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 600; color: #334155; margin-top: 2rem; margin-bottom: 1rem; }
        .prose a { color: var(--primary); text-decoration: underline; font-weight: 500; text-underline-offset: 4px; }
        .prose blockquote { border-left: 4px solid var(--primary); padding-left: 1.5rem; font-style: italic; color: #0f766e; font-size: 1.25rem; margin: 2rem 0; background: #ecfdf5; padding: 2rem; border-radius: 0 1rem 1rem 0; }
        .prose ul { list-style-type: disc; margin-bottom: 1.5rem; padding-left: 1.5rem; color: #475569; font-size: 1.125rem; }
        .prose li { margin-bottom: 0.5rem; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased">

    @include('themes.components.header')

    <div class="bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-4 py-16 text-center">
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-4 py-1.5 text-xs font-bold uppercase tracking-widest rounded-full mb-6 inline-block">{{ $post->category->name ?? 'Lifestyle' }}</span></a>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-wellness font-bold leading-tight mb-8 text-slate-800 line-clamp-2">{{ $post->title }}</h1>
            
            <div class="flex items-center justify-center gap-4 text-slate-500 text-sm font-medium">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">{{ substr($post->user->name ?? 'A', 0, 1) }}</div>
                    <span class="text-slate-700 font-bold">{{ $post->user->name ?? 'Health Editor' }}</span>
                </div>
                <span class="text-slate-300">|</span>
                <span>{{ $post->created_at->format('F d, Y') }}</span>
                <span class="text-slate-300">|</span>
                <span>{{ $post->views }} Reads</span>
            </div>
        </div>
    </div>

    <!-- Post Content Container -->
    <div class="max-w-4xl mx-auto px-4 py-12">
        
        @if($post->featured_image)
            <div class="w-full rounded-[2rem] overflow-hidden mb-16 shadow-2xl shadow-emerald-900/5 -mt-24 border-4 border-white lg:-mx-12 lg:w-auto relative z-10">
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-auto max-h-[600px] object-cover">
            </div>
        @endif

        <article class="prose max-w-none">
            {!! $post->content !!}
        </article>

        <!-- Related Reading -->
        <div class="mt-24 pt-12 border-t border-slate-200">
            <h3 class="text-2xl font-wellness font-bold text-slate-800 mb-8 text-center text-emerald-800">Continue Your Journey</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                @foreach(\App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->take(2)->get() as $rel)
                    <article class="group block relative">
    <a href="{{ route('frontend.post', $rel->slug) }}" class="absolute inset-0 z-0"></a>
                        @if($rel->featured_image)
                            <div class="aspect-video rounded-2xl overflow-hidden mb-4 shadow-md group-hover:shadow-xl transition">
                                <img src="{{ url($rel->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </div>
                        @endif
                        <a href="{{ isset($rel->category) ? route('frontend.category', $rel->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest mb-2 block">{{ $rel->category->name ?? 'Wellness' }}</span></a>
                        <h4 class="font-wellness font-bold text-xl text-slate-800 group-hover:text-emerald-600 transition leading-snug line-clamp-2">{{ $rel->title }}</h4>
                    </article>
                @endforeach
            </div>
        </div>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



