@php
    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 6)->get();
@endphp

@if($popularPosts->count() > 0)
<div class="glass p-6 rounded-2xl mb-10">
    <h3 class="text-lg font-tech font-bold border-b border-slate-700 pb-3 mb-5 uppercase tracking-widest text-indigo-400">{{ $block['title'] ?? 'Trending Now' }}</h3>
    <div class="space-y-4">
        @foreach($popularPosts as $pop)
        <a href="{{ route('frontend.post', $pop->slug) }}" class="block group relative">
            <h4 class="text-sm font-medium leading-snug group-hover:text-indigo-400 transition">{{ Str::limit($pop->title, 60) }}</h4>
            <div class="text-[10px] text-slate-500 font-tech mt-1">{{ number_format($pop->views) }} Views</div>
        </a>
        @endforeach
    </div>
</div>
@endif
