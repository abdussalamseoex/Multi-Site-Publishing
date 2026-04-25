@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<div class="mb-12">
    <div class="flex items-center justify-between mb-10 pb-4 border-b border-gray-200">
        <h3 class="text-xl font-medium text-gray-900 tracking-wide uppercase">{{ $block['title'] ?? 'Latest Captures' }}</h3>
    </div>
    
    <!-- Clean Photography Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 lg:gap-12">
        @foreach($latestPosts as $index => $post)
        <article class="photo-card block mb-4 group relative bg-gray-50">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            <a href="{{ route('frontend.post', $post->slug) }}" class="block w-full aspect-square overflow-hidden bg-gray-100 relative z-10">
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100">No Photo</div>
                @endif
            </a>
            <!-- Content Overlay -->
            <div class="photo-overlay absolute inset-0 bg-white/90 backdrop-blur-sm p-8 flex flex-col items-center justify-center text-center opacity-0 z-20 transition-opacity duration-300 pointer-events-none group-hover:pointer-events-auto">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 mb-3 block relative z-30">{{ $post->category->name ?? 'Shots' }}</a>
                <a href="{{ route('frontend.post', $post->slug) }}" class="relative z-30">
                    <h4 class="text-2xl font-medium text-gray-900 leading-tight mb-4 inline-block relative after:content-[''] after:absolute after:w-0 after:h-0.5 after:-bottom-2 after:left-1/2 after:-translate-x-1/2 after:bg-gray-900 group-hover:after:w-16 after:transition-all after:duration-500">{{ $post->title }}</h4>
                </a>
                <div class="flex items-center text-[10px] text-gray-400 uppercase tracking-widest mt-4">
                    <span>{{ $post->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
