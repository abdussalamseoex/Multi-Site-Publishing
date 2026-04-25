@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<section class="mb-10">
    <h3 class="section-title">{{ $block['title'] ?? 'Latest Articles' }}</h3>
    <div class="space-y-6">
        @foreach($latestPosts as $post)
        <article class="flex flex-col sm:flex-row gap-6 group items-start border-b border-gray-100 pb-6">
            @if($post->featured_image)
                <article class="w-full sm:w-[280px] aspect-[16/10] sm:aspect-[4/3] shrink-0 overflow-hidden relative relative">
                    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover-img">
                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="absolute bottom-0 left-0 bg-black text-white px-2 py-1 text-[9px] font-bold uppercase tracking-wider">{{ $post->category->name ?? 'News' }}</span></a>
                </article>
            @endif
            <div class="flex-1 py-1">
                <a href="{{ route('frontend.post', $post->slug) }}">
                    <h4 class="text-xl md:text-2xl font-bold leading-tight mb-3 group-hover:text-blue-600 transition line-clamp-2">{{ $post->title }}</h4>
                </a>
                <div class="font-ui text-xs text-gray-500 font-medium mb-3">
                    <span class="text-black font-bold">{{ $post->user->name ?? 'Staff' }}</span> - {{ $post->created_at->format('F d, Y') }}
                </div>
                <p class="text-sm text-gray-600 line-clamp-2 md:line-clamp-3 mb-4 leading-relaxed">{{ strip_tags($post->summary ?? $post->content) }}</p>
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif
