@php
    $query = \App\Models\Post::where('status', 'published')->where('is_featured', true);
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $featuredPosts = $query->latest()->take($block['limit'] ?? 5)->get();
@endphp

@if($featuredPosts->count() > 0)
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
    @php $mainFeatured = $featuredPosts->first(); @endphp
    <div class="lg:col-span-8">
        <article class="block group relative">
            <a href="{{ route('frontend.post', $mainFeatured->slug) }}" class="absolute inset-0 z-0"></a>
            @if($mainFeatured->featured_image)
                <div class="w-full aspect-[16/9] bg-slate-200 overflow-hidden mb-4 border border-ledger rounded">
                    <img src="{{ Str::startsWith($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : url($mainFeatured->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 grayscale group-hover:grayscale-0">
                </div>
            @else
                <div class="w-full h-64 bg-slate-800 mb-4 border border-ledger rounded flex items-center justify-center text-slate-600 font-mono-data text-xs">NO MARKET IMAGE AVAILABLE</div>
            @endif
            <a href="{{ isset($mainFeatured->category) ? route('frontend.category', $mainFeatured->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="text-xs font-bold text-sky-700 uppercase tracking-wider mb-2 block border-l-4 border-sky-700 pl-2">{{ $mainFeatured->category->name ?? 'Markets' }}</span></a>
            <h2 class="text-4xl md:text-5xl font-black tracking-tight leading-tight mb-3 line-clamp-2 relative z-10"><a href="{{ route('frontend.post', $mainFeatured->slug) }}">{{ $mainFeatured->title }}</a></h2>
            <p class="text-slate-600 text-lg mb-4 relative z-10">{{ strip_tags($mainFeatured->summary ?? $mainFeatured->content) }}</p>
            <div class="text-xs text-slate-500 font-mono-data uppercase relative z-10">{{ $mainFeatured->user->name ?? 'Editor' }} | {{ $mainFeatured->created_at->format('Y-m-d') }}</div>
        </article>
    </div>

    <div class="lg:col-span-4 flex flex-col justify-between">
        <h3 class="font-black text-xl uppercase tracking-wider border-b-2 border-slate-900 pb-2 mb-4">{{ $block['title'] ?? 'Market Watch' }}</h3>
        <div class="flex-1 space-y-6">
            @foreach($featuredPosts->skip(1) as $post)
            <article class="block group border-b border-ledger pb-4 relative">
                <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="text-[10px] font-bold text-sky-700 uppercase tracking-wider mb-1 block">{{ $post->category->name ?? 'Update' }}</span></a>
                <h4 class="text-lg font-bold leading-snug group-hover:text-sky-700 transition line-clamp-2 relative z-10"><a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a></h4>
                <div class="text-[10px] text-slate-500 font-mono-data uppercase mt-2 relative z-10">{{ $post->created_at->diffForHumans() }}</div>
            </article>
            @endforeach
        </div>
    </div>
</div>
@endif
