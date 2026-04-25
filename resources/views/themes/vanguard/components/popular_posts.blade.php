@php
    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 5)->get();
@endphp

@if($popularPosts->count() > 0)
<div class="bg-white rounded-xl shadow border border-slate-200 p-6 mb-8">
    <h3 class="text-2xl font-gaming font-bold border-b-2 border-slate-200 pb-2 mb-6 uppercase"><span class="border-b-4 border-primary pb-2.5">{{ $block['title'] ?? 'Most Popular' }}</span></h3>
    <div class="space-y-5">
        @foreach($popularPosts as $i => $pop)
        <article class="flex gap-4 group items-center relative">
            <a href="{{ route('frontend.post', $pop->slug) }}" class="absolute inset-0 z-0"></a>
            <div class="w-20 h-20 shrink-0 rounded-lg overflow-hidden bg-slate-100 border border-slate-200">
                @if($pop->featured_image)
                    <img src="{{ Str::startsWith($pop->featured_image, 'http') ? $pop->featured_image : url($pop->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition">
                @endif
            </div>
            <div class="flex flex-col justify-center">
                <a href="{{ isset($pop->category) ? route('frontend.category', $pop->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="text-[10px] font-black uppercase text-primary tracking-wider mb-1">{{ $pop->category->name ?? 'Trending' }}</span></a>
                <h4 class="text-sm font-bold leading-snug group-hover:text-primary transition">{{ Str::limit($pop->title, 45) }}</h4>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
