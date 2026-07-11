<!DOCTYPE html>
<html lang="en">
<head>
    @include('themes.components.meta_tags')
    
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,800;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#b45309');
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Lato', sans-serif; background-color: #fafafa; color: #1c1917; }
        .font-elegant { font-family: 'Playfair Display', serif; }
        
        .site-header { background-color: #1c1917 !important; border-bottom: none !important; padding: 1rem 0;}
        .site-header a, .site-header svg { color: #f5f5f4 !important; }
        .site-header .text-primary { color: #d97706 !important; }
        .site-header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .prose p { margin-bottom: 2rem; font-size: 1.125rem; line-height: 1.9; color: #44403c; font-weight: 300; }
        .prose p:first-of-type::first-letter { font-family: 'Playfair Display', serif; font-size: 3.5rem; float: left; line-height: 0.8; padding-right: 0.5rem; color: #1c1917; font-weight: 800; }
        .prose img { width: 100%; margin: 3rem 0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e7e5e4; }
        .prose h2 { font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 800; color: #1c1917; margin-top: 4rem; margin-bottom: 1.5rem; }
        .prose h3 { font-family: 'Playfair Display', serif; font-size: 1.875rem; font-weight: 600; color: #1c1917; margin-top: 3rem; margin-bottom: 1rem; font-style: italic; }
        .prose a { color: var(--primary); text-decoration: underline; text-underline-offset: 4px; }
        .prose blockquote { border-top: 1px solid #d97706; border-bottom: 1px solid #d97706; padding: 2rem 0; font-style: italic; color: #57534e; font-size: 1.5rem; margin: 3rem 0; font-family: 'Playfair Display', serif; text-align: center; }
        .prose ul { padding-left: 2rem; list-style-type: square; color: #44403c; margin-bottom: 2rem; }
        .prose li { margin-bottom: 0.5rem; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased">

    @include('themes.components.header')

    <div class="max-w-4xl mx-auto px-4 py-10 md:py-14">
        
        <header class="text-center mb-10">
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-amber-600 font-bold uppercase tracking-[0.2em] text-[10px] mb-4 block">{{ $post->category->name ?? 'Editorial' }}</span></a>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-elegant font-bold leading-snug mb-6 text-stone-900 max-w-4xl mx-auto break-words">{{ $post->title }}</h1>
            
            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-8 text-[11px] text-stone-400 tracking-[0.1em] uppercase font-bold border-t border-b border-stone-200 py-4">
                <span>By <a href="{{ route('frontend.author', $post->user->id ?? 0) }}" class="text-amber-700 hover:text-amber-900 transition">{{ $post->user->name ?? 'Agent' }}</a></span>
                <span>{{ $post->created_at->format('F d, Y') }}</span>
                <span>Visits: {{ number_format($post->views) }}</span>
            </div>
        </header>

        @if($post->featured_image)
            <div class="w-full aspect-video md:aspect-[21/9] bg-stone-100 mb-12 relative rounded-lg overflow-hidden shadow">
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="max-w-3xl mx-auto">
            <article class="prose max-w-none">
                {!! \App\Helpers\AdHelper::injectInArticleAds($post->content) !!}
            </article>

            {{-- Related Posts --}}
            @php
                $relatedPosts = \App\Models\Post::where('status', 'published')
                    ->where('id', '!=', $post->id)
                    ->when($post->category_id, function($q) use ($post) {
                        return $q->where('category_id', $post->category_id);
                    })
                    ->latest()
                    ->take(3)
                    ->get();
            @endphp

            @if($relatedPosts->count() > 0)
                <div class="mt-16 pt-12 border-t border-stone-200">
                    <h3 class="text-2xl font-elegant font-bold text-stone-900 mb-8 text-center italic">Related Articles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($relatedPosts as $rel)
                            <article class="group">
                                <a href="{{ route('frontend.post', $rel->slug) }}" class="block aspect-[16/10] bg-stone-100 overflow-hidden mb-3 rounded shadow-sm">
                                    @if($rel->featured_image)
                                        <img src="{{ Str::startsWith($rel->featured_image, 'http') ? $rel->featured_image : url($rel->featured_image) }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @endif
                                </a>
                                <a href="{{ route('frontend.post', $rel->slug) }}">
                                    <h4 class="font-elegant font-bold text-base text-stone-900 group-hover:text-amber-700 transition line-clamp-2 leading-snug mb-1">{{ $rel->title }}</h4>
                                </a>
                                <p class="text-xs text-stone-500 line-clamp-2">{{ Str::limit(strip_tags($rel->summary ?? $rel->content), 80) }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



