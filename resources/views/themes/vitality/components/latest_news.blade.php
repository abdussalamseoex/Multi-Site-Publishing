@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<div class="mb-10">
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-3xl font-wellness font-bold text-slate-800 tracking-tight">{{ $block['title'] ?? 'Recent Insights' }}</h3>
    </div>
    
    <div class="space-y-8">
        @foreach($latestPosts as $post)
        <article class="vitality-card p-5 flex flex-col sm:flex-row gap-6 items-center relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            <a href="{{ route('frontend.post', $post->slug) }}" class="w-full sm:w-1/3 aspect-[4/3] rounded-2xl overflow-hidden relative z-10">
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                @endif
            </a>
            <div class="flex-1 py-2 relative z-10">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-emerald-500 text-[10px] font-bold tracking-widest uppercase mb-2 block bg-emerald-50 inline-block px-3 py-1 rounded-full">{{ $post->category->name ?? 'Article' }}</span></a>
                <a href="{{ route('frontend.post', $post->slug) }}">
                    <h4 class="text-2xl font-wellness font-bold mb-3 leading-snug text-slate-800 hover:text-emerald-600 transition line-clamp-2">{{ $post->title }}</h4>
                </a>
                <p class="text-slate-500 text-sm line-clamp-2 mb-4 leading-relaxed">{{ strip_tags($post->summary ?? $post->content) }}</p>
                <div class="flex items-center text-xs text-slate-400 gap-3">
                    <span class="font-medium text-slate-600">{{ $post->user->name ?? 'Expert' }}</span>
                    <span>&bull;</span>
                    <span>{{ $post->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
