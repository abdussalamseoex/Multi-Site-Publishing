@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<div class="mb-10">
    <h3 class="font-black text-2xl uppercase tracking-wider mb-8">{{ $block['title'] ?? 'Latest Wire' }}</h3>
    <div class="space-y-0 border border-ledger bg-white rounded shadow-sm">
        @foreach($latestPosts as $index => $post)
        <article class="p-6 {{ !$loop->last ? 'border-b border-ledger' : '' }} hover:bg-slate-50 transition card-hover block relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            <div class="flex flex-col sm:flex-row gap-6 relative z-10">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] bg-slate-900 text-white px-2 py-0.5 rounded-sm font-bold uppercase tracking-wider">{{ $post->category->name ?? 'Finance' }}</span></a>
                        <span class="text-[10px] text-slate-500 font-mono-data">{{ $post->created_at->format('M d, H:i') }}</span>
                    </div>
                    <h4 class="text-2xl font-bold mb-2 leading-tight text-slate-900 line-clamp-2"><a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a></h4>
                    <p class="text-slate-600 text-sm mb-3 line-clamp-2 md:w-11/12">{{ strip_tags($post->summary ?? $post->content) }}</p>
                </div>
                @if($post->featured_image)
                    <div class="w-full sm:w-32 h-24 shrink-0 rounded overflow-hidden bg-slate-200 border border-ledger mt-4 sm:mt-0">
                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover grayscale opacity-80 mix-blend-multiply">
                    </div>
                @endif
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
