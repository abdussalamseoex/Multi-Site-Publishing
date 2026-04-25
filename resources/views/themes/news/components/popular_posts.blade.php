@php
    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 5)->get();
@endphp

@if($popularPosts->count() > 0)
<div class="widget mb-10">
    <h3 class="section-title">{{ $block['title'] ?? 'Most Popular' }}</h3>
    <div class="space-y-4">
        @foreach($popularPosts as $pop)
        <a href="{{ route('frontend.post', $pop->slug) }}" class="flex gap-4 group items-center">
            @if($pop->featured_image)
                <div class="w-16 h-16 shrink-0 overflow-hidden relative">
                    <img src="{{ Str::startsWith($pop->featured_image, 'http') ? $pop->featured_image : url($pop->featured_image) }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition"></div>
                </div>
            @endif
            <div>
                <h4 class="text-sm font-bold leading-snug group-hover:text-blue-600 transition line-clamp-2 mb-1">{{ $pop->title }}</h4>
                <div class="font-ui text-[10px] text-gray-400">{{ $pop->created_at->format('M d, Y') }}</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif
