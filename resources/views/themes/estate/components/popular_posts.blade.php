@php
    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 4)->get();
@endphp

@if($popularPosts->count() > 0)
<div class="mb-12">
    <h3 class="text-xl font-elegant font-bold border-b border-stone-200 pb-3 mb-6">{{ $block['title'] ?? 'Trending Areas' }}</h3>
    <ul class="space-y-4">
        @foreach($popularPosts as $pop)
        <li class="flex items-center gap-4 group relative">
            <a href="{{ route('frontend.post', $pop->slug) }}" class="absolute inset-0 z-0"></a>
            <a href="{{ route('frontend.post', $pop->slug) }}" class="w-24 h-24 shrink-0 overflow-hidden bg-stone-100 flex-1 relative z-10">
                @if($pop->featured_image)
                    <img src="{{ url($pop->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                @endif
            </a>
            <div class="flex-[2] relative z-10">
                <a href="{{ route('frontend.post', $pop->slug) }}" class="font-elegant font-bold text-lg leading-snug group-hover:text-amber-700 transition">{{ $pop->title }}</a>
            </div>
        </li>
        @endforeach
    </ul>
</div>
@endif
