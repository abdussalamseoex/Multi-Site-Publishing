<!DOCTYPE html>
<html lang="en">
<head>
    @include('themes.components.meta_tags')
    
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#111827');
        $font = \App\Models\Setting::get('typography', 'Inter');
        $logo = \App\Models\Setting::get('site_logo');
        $menu = \App\Models\Menu::where('location', 'header')->with('items')->first() ?? \App\Models\Menu::with('items')->first();
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: '{{ $font }}', sans-serif; }
        .text-primary { color: var(--primary); }
        .prose a { color: var(--primary); text-decoration: underline; font-weight: bold; }
        .prose p { margin-bottom: 1.5em; line-height: 1.8; font-size: 1.125rem; }
        .prose h2 { font-size: 2rem; font-weight: bold; margin-top: 2em; margin-bottom: 0.5em; color: var(--primary); }
        .prose h3 { font-size: 1.5rem; font-weight: bold; margin-top: 1.5em; margin-bottom: 0.5em; }
        .prose blockquote { border-left: 4px solid var(--primary); padding-left: 1rem; font-style: italic; color: #4b5563; }
        .prose img { border-radius: 0.5rem; margin: 2rem 0; width: 100%; height: auto; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    @include('themes.components.header')
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-12 pb-20 mt-4">
        <article class="lg:col-span-2 bg-white shadow-xl rounded-2xl overflow-hidden pt-12 md:px-16 pb-12 border border-gray-100">
            
            <header class="mb-12">
                <div class="flex items-center text-xs font-bold uppercase tracking-widest text-primary mb-4 gap-2">
                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span>{{ $post->category->name ?? 'Uncategorized' }}</span></a>
                    <span class="text-gray-300">&bull;</span>
                    <span class="text-gray-400">{{ $post->created_at->format('M d, Y') }}</span>
                </div>
                
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight leading-snug mb-6 text-gray-900 break-words">
                    {{ $post->title }}
                </h1>
                
                <div class="flex items-center gap-3">
                    <div>
                        <p class="font-medium text-sm font-bold text-gray-700">By <a href="{{ route('frontend.author', $post->user->slug ?? ($post->user->id ?? 1)) }}" class="text-primary hover:underline transition">{{ $post->user->name ?? 'Author' }}</a></p>
                        <p class="text-xs text-gray-500">{{ $post->views }} Views</p>
                    </div>
                </div>
            </header>

            @if($post->featured_image)
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-auto rounded-lg mb-12 shadow-sm">
            @endif

            <div class="prose max-w-none text-gray-700">
                {!! \App\Helpers\AdHelper::injectInArticleAds($post->content) !!}
            </div>
            
            <!-- Related Posts -->
            <div class="mt-16 border-t pt-10">
                <h3 class="text-2xl font-bold mb-6">Related Reading</h3>
                @php 
                    $related = clone $post; 
                    $relatedList = \App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->take(2)->get();
                @endphp
                @if($relatedList->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($relatedList as $rel)
                            <a href="{{ route('frontend.post', $rel->slug) }}" class="block group bg-gray-50 p-6 rounded-xl border border-gray-100 h-full">
                                <h4 class="font-bold text-lg group-hover:text-primary transition leading-tight line-clamp-2">{{ $rel->title }}</h4>
                                <p class="text-sm text-gray-500 mt-2">{{ Str::limit(strip_tags($rel->content), 80) }}</p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 italic">No related posts found.</p>
                @endif
            </div>
        </article>

        <!-- Sidebar -->
        <aside class="space-y-8">
            @include('themes.components.dynamic_sidebar')
        </aside>
    </div>
    
    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



