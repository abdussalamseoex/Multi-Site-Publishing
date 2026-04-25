@php
    $limit = $block['limit'] ?? 1;
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
    <div class="relative bg-black text-white rounded-lg overflow-hidden mb-16 group h-[60vh] flex items-end p-8 md:p-16">
        @if($hero->featured_image)
            <img src="{{ Str::startsWith($hero->featured_image, 'http') ? $hero->featured_image : url($hero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-700">
        @endif
        <div class="absolute inset-0 bg-gradient-to-tr from-black via-gray-900 to-transparent opacity-80 z-10"></div>
        
        <div class="relative z-20 max-w-2xl">
            <a href="{{ isset($hero->category) ? route('frontend.category', $hero->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-20"><span class="px-3 py-1 bg-white text-black text-xs font-bold uppercase tracking-widest mb-4 inline-block">{{ $hero->category->name ?? 'Lifestyle' }}</span></a>
            <a href="{{ route('frontend.post', $hero->slug) }}" class="relative z-20">
                <h2 class="text-5xl md:text-7xl font-black mb-4 leading-tight hover:text-gray-300 transition line-clamp-2">{{ $hero->title }}</h2>
            </a>
            <p class="text-lg md:text-xl text-gray-200">{{ Str::limit($hero->summary ?? strip_tags($hero->content), 150) }}</p>
        </div>
    </div>
@endif
