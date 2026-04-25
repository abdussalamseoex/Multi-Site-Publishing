@php
    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 4)->get();
@endphp

@if($popularPosts->count() > 0)
<div class="mb-12">
    <h3 class="text-sm font-medium tracking-widest text-gray-900 uppercase border-b border-gray-200 pb-3 mb-6">{{ $block['title'] ?? 'Popular Works' }}</h3>
    <div class="space-y-6">
        @foreach($popularPosts as $pop)
        <article class="flex gap-4 group relative hover:bg-gray-50 p-2 transition -mx-2 rounded">
            <a href="{{ route('frontend.post', $pop->slug) }}" class="absolute inset-0 z-0"></a>
            <div class="w-20 h-20 shrink-0 bg-gray-100 border border-gray-200 relative z-10">
                @if($pop->featured_image)
                    <img src="{{ url($pop->featured_image) }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="flex-1 flex flex-col justify-center relative z-10">
                <h4 class="text-sm font-medium text-gray-800 leading-snug group-hover:text-gray-500 transition line-clamp-2 mb-1"><a href="{{ route('frontend.post', $pop->slug) }}">{{ $pop->title }}</a></h4>
                <span class="text-[10px] font-bold tracking-widest uppercase text-gray-400">{{ $pop->category->name ?? 'Gallery' }}</span>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
