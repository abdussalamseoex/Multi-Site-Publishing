@php
    $popularPosts = \App\Models\Post::where('status', 'published')
        ->orderBy('views', 'desc')
        ->take($block['limit'] ?? 5)
        ->get();
@endphp

@if($popularPosts->count() > 0)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5">{{ $block['title'] ?? 'Most Popular' }}</h3>
    <div class="space-y-5">
        @foreach($popularPosts as $index => $post)
        <article class="flex gap-4 items-center group">
            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 font-bold shrink-0 group-hover:bg-primary group-hover:text-white transition">
                {{ $index + 1 }}
            </div>
            <div>
                <h4 class="text-sm font-semibold text-gray-800 leading-snug group-hover:text-primary transition line-clamp-2">
                    <a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a>
                </h4>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($post->views) }} views</p>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
