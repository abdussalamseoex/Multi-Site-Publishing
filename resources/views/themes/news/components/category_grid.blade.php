@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $gridPosts = $query->latest()->take($block['limit'] ?? 4)->get();
@endphp

@if($gridPosts->count() > 0)
<section class="mb-10">
    <h3 class="section-title">{{ $block['title'] ?? 'Lifestyle News' }}</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        @foreach($gridPosts as $post)
        <div class="group">
            <article class="block relative">
                <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                @if($post->featured_image)
                    <div class="aspect-[16/10] overflow-hidden mb-3 relative">
                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover-img">
                        <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="absolute bottom-0 left-0 bg-black text-white px-2 py-1 text-[9px] font-bold uppercase tracking-wider">{{ $post->category->name ?? 'Style' }}</span></a>
                    </div>
                @endif
                <h4 class="text-lg font-bold leading-snug mb-2 group-hover:text-blue-600 transition line-clamp-2">{{ $post->title }}</h4>
            </article>
            <div class="font-ui text-[11px] text-gray-400 font-medium">
                <span class="text-black font-bold">{{ $post->user->name ?? 'Editor' }}</span> - {{ $post->created_at->format('M d, Y') }}
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif
