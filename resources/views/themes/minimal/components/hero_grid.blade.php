@php
    $query = \App\Models\Post::where('status', 'published')->where('is_featured', true);
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $featuredPosts = $query->latest()->take($block['limit'] ?? 4)->get();
@endphp

@if($featuredPosts->count() > 0)
<section class="mb-16">
    <h2 class="text-sm font-bold uppercase tracking-widest text-primary mb-8">{{ $block['title'] ?? 'Featured' }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        @foreach($featuredPosts as $post)
            <article class="group cursor-pointer">
                <a href="{{ route('frontend.post', $post->slug) }}" class="block">
                    @if($post->featured_image)
                        <div class="aspect-video w-full overflow-hidden rounded-lg mb-4">
                            <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-500">
                        </div>
                    @endif
                    <h3 class="text-2xl font-bold mb-2 group-hover:text-primary transition line-clamp-2">{{ $post->title }}</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">{{ Str::limit($post->summary ?? strip_tags($post->content), 120) }}</p>
                    <span class="text-xs font-mono text-gray-400 uppercase">{{ $post->created_at->format('M d, Y') }}</span>
                </a>
            </article>
        @endforeach
    </div>
</section>
@endif
