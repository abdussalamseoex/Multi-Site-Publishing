@php
    $limit = $block['limit'] ?? 4;
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    
    // First try to get featured posts
    $featuredPosts = (clone $query)->where('is_featured', true)->latest()->take($limit)->get();
    
    // If not enough featured posts, fill the gap with latest regular posts
    if ($featuredPosts->count() < $limit) {
        $remaining = $limit - $featuredPosts->count();
        $regularPosts = (clone $query)->where('is_featured', false)->latest()->take($remaining)->get();
        $featuredPosts = $featuredPosts->merge($regularPosts);
    }
@endphp

@if($featuredPosts->count() > 0)
    @php $hero = $featuredPosts->first(); @endphp
    <!-- Hero Showcase -->
    <div class="relative w-full h-[70vh] min-h-[500px] flex items-center justify-center">
        @if($hero->featured_image)
            <img src="{{ Str::startsWith($hero->featured_image, 'http') ? $hero->featured_image : url($hero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover">
        @else
            <div class="absolute inset-0 bg-stone-800"></div>
        @endif
        <div class="absolute inset-0 bg-stone-900/60"></div>
        
        <div class="relative z-10 text-center max-w-4xl px-4 mt-16">
            <a href="{{ isset($hero->category) ? route('frontend.category', $hero->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-20"><span class="text-amber-500 font-bold tracking-[0.2em] uppercase text-xs mb-4 block">{{ $hero->category->name ?? 'Exclusive' }}</span></a>
            <a href="{{ route('frontend.post', $hero->slug) }}" class="relative z-20">
                <h2 class="text-5xl md:text-7xl font-elegant font-bold text-white leading-tight mb-6 line-clamp-2">{{ $hero->title }}</h2>
            </a>
            <p class="text-stone-200 text-lg md:text-xl font-light mb-8 relative z-20">{{ Str::limit(strip_tags($hero->summary ?? $hero->content), 120) }}</p>
            <div class="flex items-center justify-center gap-6 text-stone-300 text-sm tracking-widest uppercase relative z-20">
                <span>{{ $hero->user->name ?? 'Broker' }}</span>
                <span>|</span>
                <span>{{ $hero->created_at->format('F Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Featured Properties / Guides -->
    @if($featuredPosts->count() > 1)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20 -mt-24 relative z-20 max-w-7xl mx-auto px-4">
        @foreach($featuredPosts->skip(1)->take(3) as $post)
        <article class="bg-white shadow-2xl shadow-stone-200/50 block group relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            <div class="aspect-[4/3] overflow-hidden relative">
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                @endif
                <div class="absolute inset-0 bg-stone-900/20 group-hover:bg-transparent transition"></div>
            </div>
            <div class="p-8 text-center border-b-4 border-transparent group-hover:gold-border transition duration-300 relative z-10">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] text-amber-600 font-bold uppercase tracking-[0.15em] mb-3 block">{{ $post->category->name ?? 'Design' }}</span></a>
                <h3 class="text-2xl font-elegant font-bold text-stone-900 leading-snug line-clamp-2"><a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a></h3>
            </div>
        </article>
        @endforeach
    </div>
    @endif
@endif
