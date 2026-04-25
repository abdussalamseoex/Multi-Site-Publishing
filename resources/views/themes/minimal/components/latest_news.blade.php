@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<section>
    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-8">{{ $block['title'] ?? 'Latest Articles' }}</h2>
    <div class="space-y-12">
        @foreach($latestPosts as $post)
            <article class="group border-b pb-12 flex flex-col md:flex-row gap-8">
                <div class="flex-1">
                    <article class="block relative">
                        <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                        <h3 class="text-3xl font-bold mb-3 group-hover:underline decoration-4 underline-offset-4 text-primary line-clamp-2">{{ $post->title }}</h3>
                        <p class="text-gray-600 text-lg leading-relaxed mb-4">{{ Str::limit(strip_tags($post->content), 200) }}</p>
                        <div class="flex items-center text-sm font-mono text-gray-400 uppercase gap-4">
                            <span>{{ $post->created_at->format('F d, Y') }}</span>
                            <span>&bull;</span>
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition z-10 relative"><span>{{ $post->category->name ?? 'Uncategorized' }}</span></a>
                        </div>
                    </article>
                </div>
                @if($post->featured_image)
                    <div class="w-full md:w-1/3 aspect-video md:aspect-square overflow-hidden rounded-lg">
                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="object-cover w-full h-full group-hover:opacity-80 transition">
                    </div>
                @endif
            </article>
        @endforeach
    </div>
</section>
@endif
