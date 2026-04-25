@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $posts = $query->latest()->take($block['limit'] ?? 4)->get();
@endphp

@if($posts->isNotEmpty())
<section class="mb-10">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        <!-- Large Main Post -->
        @if($posts->first())
        <div class="relative group cursor-pointer overflow-hidden rounded bg-dark h-[400px]">
            <a href="{{ route('frontend.post', $posts->first()->slug) }}" class="absolute inset-0 z-10"></a>
            @if($posts->first()->featured_image)
            <img src="{{ Str::startsWith($posts->first()->featured_image, 'http') ? $posts->first()->featured_image : url($posts->first()->featured_image) }}" class="object-cover w-full h-full opacity-80 group-hover:scale-105 transition duration-700">
            @endif
            <div class="absolute bottom-0 left-0 p-6 z-20 bg-gradient-to-t from-black/90 to-transparent w-full">
                @if($posts->first()->category)
                <span class="bg-primary text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded mb-2 inline-block">{{ $posts->first()->category->name }}</span>
                @endif
                <h2 class="text-white text-2xl md:text-3xl font-bold leading-tight line-clamp-2 hover:text-primary transition">{{ $posts->first()->title }}</h2>
                <div class="flex items-center text-gray-300 text-xs mt-3 gap-3">
                    <span><i class="far fa-user mr-1"></i> {{ $posts->first()->user->name ?? 'Admin' }}</span>
                    <span><i class="far fa-clock mr-1"></i> {{ $posts->first()->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Smaller Posts Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($posts->skip(1) as $post)
            <div class="relative group cursor-pointer overflow-hidden rounded bg-dark h-[192px]">
                <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-10"></a>
                @if($post->featured_image)
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="object-cover w-full h-full opacity-70 group-hover:scale-105 transition duration-700">
                @endif
                <div class="absolute bottom-0 left-0 p-4 z-20 bg-gradient-to-t from-black/90 to-transparent w-full">
                    @if($post->category)
                    <span class="text-primary text-[10px] font-bold uppercase tracking-wider mb-1 block">{{ $post->category->name }}</span>
                    @endif
                    <h3 class="text-white text-sm font-bold leading-snug line-clamp-2">{{ $post->title }}</h3>
                    <span class="text-gray-400 text-[10px] mt-2 block">{{ $post->created_at->format('M d, Y') }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
