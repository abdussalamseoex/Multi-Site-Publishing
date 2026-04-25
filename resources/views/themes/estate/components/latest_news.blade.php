@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<div class="mb-12">
    <div class="border-b border-stone-200 pb-4 mb-10 flex items-center justify-between">
        <h3 class="text-3xl font-elegant font-bold text-stone-900 italic">{{ $block['title'] ?? 'Market Insights' }}</h3>
    </div>
    
    <div class="space-y-12">
        @foreach($latestPosts as $post)
        <article class="flex flex-col md:flex-row gap-8 group relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            <a href="{{ route('frontend.post', $post->slug) }}" class="w-full md:w-5/12 aspect-[4/3] relative overflow-hidden bg-stone-100 z-10">
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                @endif
            </a>
            <div class="w-full md:w-7/12 flex flex-col justify-center relative z-10">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-xs text-amber-600 font-bold uppercase tracking-[0.15em] mb-3 block">{{ $post->category->name ?? 'Market' }}</span></a>
                <a href="{{ route('frontend.post', $post->slug) }}">
                    <h4 class="text-3xl font-elegant font-bold mb-4 text-stone-900 leading-tight group-hover:text-amber-700 transition line-clamp-2">{{ $post->title }}</h4>
                </a>
                <p class="text-stone-500 font-light leading-relaxed mb-6">{{ strip_tags($post->summary ?? $post->content) }}</p>
                <div class="flex items-center text-[11px] text-stone-400 tracking-[0.1em] uppercase">
                    <span>{{ $post->created_at->format('M d, Y') }}</span>
                    <span class="mx-3 border-l border-stone-300 h-3"></span>
                    <span>By {{ $post->user->name ?? 'Agent' }}</span>
                </div>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
