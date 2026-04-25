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
    @php $mainFeatured = $featuredPosts->first(); @endphp
    <!-- Minimalist Hero Showcase -->
    <div class="mb-20">
        <div class="flex flex-col lg:flex-row gap-12 items-center">
            <div class="w-full lg:w-1/2">
                <a href="{{ route('frontend.post', $mainFeatured->slug) }}" class="block w-full aspect-[4/5] object-cover bg-gray-100 overflow-hidden group">
                    @if($mainFeatured->featured_image)
                        <img src="{{ Str::startsWith($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : url($mainFeatured->featured_image) }}" class="w-full h-full object-cover transition duration-1000 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">No Image</div>
                    @endif
                </a>
            </div>
            <div class="w-full lg:w-1/2 space-y-6">
                <a href="{{ isset($mainFeatured->category) ? route('frontend.category', $mainFeatured->category->slug) : '#' }}" class="inline-block border border-gray-200 px-4 py-1 text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-gray-900 hover:border-gray-900 transition">{{ $mainFeatured->category->name ?? 'Featured' }}</a>
                <a href="{{ route('frontend.post', $mainFeatured->slug) }}" class="block">
                    <h2 class="text-4xl md:text-5xl lg:text-6xl font-light text-gray-900 leading-[1.1] hover:text-gray-600 transition">{{ $mainFeatured->title }}</h2>
                </a>
                <p class="text-gray-500 text-lg md:text-xl font-light leading-relaxed max-w-xl">{{ strip_tags($mainFeatured->summary ?? $mainFeatured->content) }}</p>
                <div class="flex items-center gap-4 text-xs font-medium text-gray-400 uppercase tracking-widest mt-8">
                    <span>By {{ $mainFeatured->user->name ?? 'Photographer' }}</span>
                    <span>&bull;</span>
                    <span>{{ $mainFeatured->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    @if($featuredPosts->count() > 1)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24 pb-20 border-b border-gray-100">
        @foreach($featuredPosts->skip(1)->take(3) as $post)
        <div class="text-center group block relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            <a href="{{ route('frontend.post', $post->slug) }}" class="block w-full aspect-[3/2] overflow-hidden bg-gray-100 mb-6 relative z-10">
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-105">
                @endif
            </a>
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="text-[10px] uppercase font-bold tracking-widest text-gray-400 mb-2 block hover:text-gray-900 transition relative z-10">{{ $post->category->name ?? 'Gallery' }}</a>
            <a href="{{ route('frontend.post', $post->slug) }}" class="relative z-10">
                <h3 class="text-2xl font-medium text-gray-900 leading-tight mb-2 group-hover:text-gray-500 transition">{{ $post->title }}</h3>
            </a>
        </div>
        @endforeach
    </div>
    @endif
@endif
