@php
    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 6)->get();
@endphp

@if($popularPosts->count() > 0)
<div class="border border-ledger bg-white p-6 rounded shadow-sm relative overflow-hidden mb-10">
    <div class="absolute top-0 right-0 p-4 opacity-5 bg-sky-700 w-32 h-32 rounded-full -mr-16 -mt-16 blur-sm"></div>
    <h3 class="font-black text-lg uppercase tracking-wider border-b-2 border-slate-900 pb-2 mb-4 relative z-10">{{ $block['title'] ?? 'Most Read' }}</h3>
    <div class="space-y-4">
        @foreach($popularPosts as $pop)
        <a href="{{ route('frontend.post', $pop->slug) }}" class="flex items-start gap-4 group">
            <div class="text-2xl font-mono-data font-black text-slate-300 group-hover:text-sky-700 transition">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
            <div>
                <h4 class="text-sm font-bold leading-snug text-slate-800">{{ Str::limit($pop->title, 60) }}</h4>
                <div class="text-[10px] text-slate-500 font-mono-data mt-1">{{ number_format($pop->views) }} VOL</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif
