@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $posts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($posts->isNotEmpty())
<section class="mb-10 bg-white p-6 shadow-sm rounded">
    @if(!empty($block['title']))
    <div class="flex items-center mb-6 border-b-2 border-gray-100 pb-2">
        <h3 class="text-xl font-bold uppercase tracking-wide text-dark border-b-2 border-primary -mb-[10px] pb-2">{{ $block['title'] }}</h3>
    </div>
    @endif

    <div class="space-y-6">
        @foreach($posts as $post)
        <article class="flex flex-col sm:flex-row gap-4 group">
            @if($post->featured_image)
            <div class="w-full sm:w-1/3 md:w-1/4 h-40 sm:h-auto rounded overflow-hidden relative">
                <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-10"></a>
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @if($post->category)
                <span class="absolute top-2 left-2 bg-primary text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded z-20">{{ $post->category->name }}</span>
                @endif
            </div>
            @endif
            
            <div class="flex-1 flex flex-col justify-center py-2">
                <h4 class="text-xl font-bold mb-2 group-hover:text-primary transition line-clamp-2">
                    <a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a>
                </h4>
                <div class="flex items-center text-gray-400 text-xs mb-3 gap-4 font-medium uppercase tracking-wide">
                    <span><i class="far fa-user text-primary mr-1"></i> {{ $post->user->name ?? 'Admin' }}</span>
                    <span><i class="far fa-clock text-primary mr-1"></i> {{ $post->created_at->format('F d, Y') }}</span>
                    <span><i class="far fa-eye text-primary mr-1"></i> {{ $post->views }} Views</span>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed line-clamp-2">{{ Str::limit($post->summary ?? strip_tags($post->content), 150) }}</p>
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif
