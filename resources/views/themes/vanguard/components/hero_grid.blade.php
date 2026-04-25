@php
    $query = \App\Models\Post::where('status', 'published')->where('is_featured', true);
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $featuredPosts = $query->latest()->take($block['limit'] ?? 3)->get();
@endphp

@if($featuredPosts->count() > 0)
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
    @php 
        $mainFeatured = $featuredPosts->first(); 
        $otherFeatured = $featuredPosts->skip(1)->take(2);
    @endphp
    
    <div class="lg:col-span-2 relative rounded-xl overflow-hidden shadow-2xl group min-h-[400px]">
        @if($mainFeatured->featured_image)
            <img src="{{ Str::startsWith($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : url($mainFeatured->featured_image) }}" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-105">
        @else
            <div class="absolute inset-0 w-full h-full bg-slate-800"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
        <div class="absolute bottom-0 left-0 p-8 w-full z-10">
            <span class="badge-orange px-3 py-1 text-xs font-black uppercase tracking-wider rounded border border-orange-500/50 shadow-lg mb-3 inline-block">Trending</span>
            <a href="{{ route('frontend.post', $mainFeatured->slug) }}">
                <h2 class="text-white text-4xl md:text-5xl font-gaming font-bold leading-tight mb-2 group-hover:text-primary transition line-clamp-2">{{ $mainFeatured->title }}</h2>
            </a>
            <div class="flex items-center text-gray-300 text-xs font-bold uppercase gap-4">
                <span>By {{ $mainFeatured->user->name ?? 'Admin' }}</span>
                <span>{{ $mainFeatured->created_at->format('M d, Y') }}</span>
                <span class="flex text-yellow-400 text-sm">★★★★★</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        @foreach($otherFeatured as $post)
        <div class="relative rounded-xl overflow-hidden shadow-xl group h-1/2 min-h-[190px]">
            @if($post->featured_image)
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-105">
            @else
                <div class="absolute inset-0 w-full h-full bg-slate-800"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-5 w-full z-10">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-20"><span class="badge-blue px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded border border-blue-500/50 mb-2 inline-block shadow-lg">{{ $post->category->name ?? 'News' }}</span></a>
                <a href="{{ route('frontend.post', $post->slug) }}" class="relative z-20">
                    <h3 class="text-white text-2xl font-gaming font-bold leading-tight mb-1 group-hover:text-primary transition line-clamp-2">{{ $post->title }}</h3>
                </a>
                <div class="flex items-center text-gray-300 text-[10px] font-bold uppercase gap-2 relative z-20">
                    <span>{{ $post->created_at->format('M d') }}</span>
                    <span class="flex text-yellow-400">★★★★☆</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
