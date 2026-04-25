@php
    $limit = $block['limit'] ?? 5;
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
    <div class="relative rounded-3xl overflow-hidden h-[600px] w-full flex items-end justify-start group">
        @if($hero->featured_image)
            <img src="{{ Str::startsWith($hero->featured_image, 'http') ? $hero->featured_image : url($hero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.03] transition duration-[1.5s]">
        @else
            <div class="absolute inset-0 bg-pink-900"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
        
        <div class="relative z-10 p-8 md:p-16 max-w-4xl text-white">
            <a href="{{ isset($hero->category) ? route('frontend.category', $hero->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-20"><span class="bg-pink-600 text-white px-4 py-2 text-xs font-bold uppercase tracking-widest rounded-full mb-6 inline-block">{{ $hero->category->name ?? 'Destination' }}</span></a>
            <a href="{{ route('frontend.post', $hero->slug) }}" class="relative z-20">
                <h2 class="text-5xl md:text-7xl font-travel font-black tracking-tight leading-none mb-4 text-shadow hover:text-pink-300 transition line-clamp-2">{{ $hero->title }}</h2>
            </a>
            <p class="text-gray-200 text-lg md:text-xl font-medium mb-6 line-clamp-2 md:w-3/4 relative z-20">{{ strip_tags($hero->summary ?? $hero->content) }}</p>
            <div class="flex items-center gap-4 text-sm font-bold uppercase tracking-wider relative z-20">
                <div class="w-10 h-10 rounded-full bg-white text-pink-600 flex items-center justify-center -ml-2 border-2 border-white">{{ substr($hero->user->name ?? 'A', 0, 1) }}</div>
                <span>By {{ $hero->user->name ?? 'Explorer' }}</span>
                <span class="opacity-50">|</span>
                <span>{{ $hero->created_at->format('M d, Y') }}</span>
            </div>
        </div>
    </div>
    
    @if($featuredPosts->count() > 1)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
        @foreach($featuredPosts->skip(1) as $post)
        <article class="relative rounded-2xl overflow-hidden aspect-square group relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            @if($post->featured_image)
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-700">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-gray-900/20"></div>
            <div class="absolute bottom-0 left-0 p-5 w-full">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="text-pink-400 text-[10px] font-black uppercase tracking-widest mb-1 block">{{ $post->category->name ?? 'Travel Guide' }}</span></a>
                <h3 class="text-white text-xl font-travel font-bold leading-tight group-hover:text-pink-300 transition line-clamp-2 relative z-10"><a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a></h3>
            </div>
        </article>
        @endforeach
    </div>
    @endif
@endif
