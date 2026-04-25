@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $posts = $query->latest()->take($block['limit'] ?? 5)->get();
@endphp

@if($posts->isNotEmpty())
<section class="mb-10 bg-dark p-6 shadow-sm rounded text-white">
    @if(!empty($block['title']))
    <div class="flex items-center mb-6 border-b border-gray-700 pb-2">
        <h3 class="text-xl font-bold uppercase tracking-wide border-b-2 border-primary -mb-[9px] pb-2">{{ $block['title'] }}</h3>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Large Left Post -->
        @if($posts->first())
        <div class="relative group cursor-pointer overflow-hidden rounded bg-black h-full min-h-[300px]">
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
                    <span><i class="far fa-user text-primary mr-1"></i> {{ $posts->first()->user->name ?? 'Admin' }}</span>
                    <span><i class="far fa-clock text-primary mr-1"></i> {{ $posts->first()->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Right Small Posts List -->
        <div class="space-y-4">
            @foreach($posts->skip(1) as $post)
            <article class="flex gap-4 group items-center">
                @if($post->featured_image)
                <div class="w-24 h-24 rounded overflow-hidden relative flex-shrink-0">
                    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-10"></a>
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                </div>
                @endif
                
                <div class="flex-1">
                    @if($post->category)
                    <span class="text-primary text-[10px] font-bold uppercase tracking-wider mb-1 block">{{ $post->category->name }}</span>
                    @endif
                    <h4 class="text-base font-bold mb-1 group-hover:text-primary transition line-clamp-2">
                        <a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a>
                    </h4>
                    <span class="text-gray-400 text-xs"><i class="far fa-clock text-primary mr-1"></i> {{ $post->created_at->format('M d, Y') }}</span>
                </div>
            </article>
            @endforeach
        </div>

    </div>
</section>
@endif
