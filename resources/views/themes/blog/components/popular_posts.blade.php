@php
    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 5)->get();
@endphp

@if($popularPosts->count())
<div class="mt-8">
    <h3 class="font-bold text-lg border-b pb-2 mb-4">{{ $block['title'] ?? 'Popular Posts' }}</h3>
    <ul class="space-y-3">
        @foreach($popularPosts as $post)
            <li>
                <a href="{{ route('frontend.post', $post->slug) }}" class="text-indigo-600 hover:underline text-sm font-medium">{{ $post->title }}</a>
            </li>
        @endforeach
    </ul>
</div>
@endif
