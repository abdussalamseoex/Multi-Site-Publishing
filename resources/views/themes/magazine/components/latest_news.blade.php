@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
    <h3 class="text-2xl font-bold uppercase tracking-widest border-b-2 border-black pb-2 mb-8 mt-12">{{ $block['title'] ?? 'The Latest' }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
        @foreach($latestPosts as $post)
            <article>
                @if($post->featured_image)
                    <a href="{{ route('frontend.post', $post->slug) }}">
                        <div class="aspect-[4/5] bg-gray-200 mb-4 rounded-md overflow-hidden relative">
                            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-10"></a>
                            <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover:scale-110 transition duration-500">
                        </div>
                    </a>
                @endif
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-20"><div class="text-xs font-bold uppercase tracking-widest text-primary mb-2 mt-4">{{ $post->category->name ?? 'Style' }}</div></a>
                <a href="{{ route('frontend.post', $post->slug) }}">
                    <h4 class="text-2xl font-bold leading-tight mb-2 hover:text-primary transition line-clamp-2">{{ $post->title }}</h4>
                </a>
                <p class="text-gray-600 text-sm mb-3 font-sans">{{ Str::limit(strip_tags($post->content), 100) }}</p>
                <div class="text-xs text-gray-400 font-bold uppercase font-sans">By {{ $post->user->name ?? 'Editor' }}</div>
            </article>
        @endforeach
    </div>
@endif
