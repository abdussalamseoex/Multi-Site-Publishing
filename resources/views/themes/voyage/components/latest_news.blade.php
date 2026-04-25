@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<div class="mb-10 border-b-2 border-gray-100 pb-4 flex items-center justify-between">
    <h3 class="text-4xl font-travel font-black text-gray-900">{{ $block['title'] ?? 'Latest Journeys' }}</h3>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
    @foreach($latestPosts as $post)
    <article class="flex flex-col group relative">
        <article class="w-full aspect-[4/5] rounded-3xl overflow-hidden relative voyage-shadow mb-6 relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            @if($post->featured_image)
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover:scale-105 transition duration-700">
            @endif
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><div class="absolute top-4 left-4 bg-white/90 backdrop-blur text-gray-900 px-3 py-1.5 text-[10px] font-black tracking-widest uppercase rounded-full shadow-lg">
                {{ $post->category->name ?? 'Story' }}
            </div></a>
        </article>
        <div class="flex-1 relative z-10">
            <a href="{{ route('frontend.post', $post->slug) }}">
                <h4 class="text-2xl font-travel font-bold mb-3 leading-snug text-gray-900 group-hover:text-pink-600 transition line-clamp-2">{{ $post->title }}</h4>
            </a>
            <p class="text-gray-500 text-sm line-clamp-2 mb-4">{{ strip_tags($post->summary ?? $post->content) }}</p>
            <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                <span class="text-xs font-bold text-gray-900">{{ $post->user->name ?? 'Traveler' }}</span>
                <span class="text-xs font-medium text-gray-400">{{ $post->created_at->format('M d') }} &bull; {{ $post->views }} Views</span>
            </div>
        </div>
    </article>
    @endforeach
</div>
@endif
