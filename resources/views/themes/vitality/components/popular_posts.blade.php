@php
    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 5)->get();
@endphp

@if($popularPosts->count() > 0)
<div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 mb-8">
    <h3 class="text-xl font-wellness font-bold text-slate-800 mb-6">{{ $block['title'] ?? 'Trending Topics' }}</h3>
    <div class="space-y-6">
        @foreach($popularPosts as $pop)
        <article class="flex gap-4 group relative">
            <a href="{{ route('frontend.post', $pop->slug) }}" class="absolute inset-0 z-0"></a>
            <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0 relative z-10">
                @if($pop->featured_image)
                    <img src="{{ Str::startsWith($pop->featured_image, 'http') ? $pop->featured_image : url($pop->featured_image) }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="flex-1 relative z-10">
                <h4 class="text-sm font-bold text-slate-700 leading-snug group-hover:text-emerald-600 transition"><a href="{{ route('frontend.post', $pop->slug) }}">{{ Str::limit($pop->title, 50) }}</a></h4>
                <a href="{{ isset($pop->category) ? route('frontend.category', $pop->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">{{ $pop->category->name ?? 'Lifestyle' }}</div></a>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
