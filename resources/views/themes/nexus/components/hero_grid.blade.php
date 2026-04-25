@php
    $limit = $block['limit'] ?? 3;
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
<div class="mb-16">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-tech font-bold flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </span>
            {{ $block['title'] ?? 'TOP INNOVATIONS' }}
        </h2>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @php $mainFeatured = $featuredPosts->first(); @endphp
        <article class="block relative rounded-2xl overflow-hidden glass hover-card min-h-[400px] group relative">
            <a href="{{ route('frontend.post', $mainFeatured->slug) }}" class="absolute inset-0 z-0"></a>
            @if($mainFeatured->featured_image)
                <img src="{{ Str::startsWith($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : url($mainFeatured->featured_image) }}" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-80 transition duration-500">
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 to-slate-900 opacity-80"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-8">
                <a href="{{ isset($mainFeatured->category) ? route('frontend.category', $mainFeatured->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-20"><span class="bg-indigo-600 text-white px-3 py-1 text-[10px] font-tech tracking-widest uppercase rounded shadow mb-4 inline-block">{{ $mainFeatured->category->name ?? 'Software' }}</span></a>
                <h3 class="text-3xl md:text-4xl font-tech font-bold leading-tight mb-3 line-clamp-2 relative z-20"><a href="{{ route('frontend.post', $mainFeatured->slug) }}">{{ $mainFeatured->title }}</a></h3>
                <p class="text-slate-300 text-sm mb-4 line-clamp-2 md:w-5/6 relative z-20">{{ strip_tags($mainFeatured->summary ?? $mainFeatured->content) }}</p>
                <div class="flex items-center text-xs text-indigo-300 uppercase tracking-wider font-tech gap-3 relative z-20">
                    <span>{{ $mainFeatured->created_at->format('M d, Y') }}</span>
                    <span>&bull;</span>
                    <span>{{ $mainFeatured->views }} Readers</span>
                </div>
            </div>
        </article>

        <div class="flex flex-col gap-8">
            @foreach($featuredPosts->skip(1)->take(2) as $post)
            <article class="flex-1 relative rounded-2xl overflow-hidden glass hover-card flex items-end p-6 group relative">
                <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-60 transition duration-500">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/20"></div>
                <div class="relative z-10 w-full">
                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-20"><span class="text-[10px] font-tech text-indigo-400 tracking-widest uppercase mb-2 block">{{ $post->category->name ?? 'Review' }}</span></a>
                    <h3 class="text-2xl font-tech font-bold leading-snug mb-2 line-clamp-2 relative z-20"><a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a></h3>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</div>
@endif
