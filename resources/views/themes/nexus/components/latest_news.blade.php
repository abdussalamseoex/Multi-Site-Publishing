@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<div class="mb-8">
    <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-8">
        <h3 class="text-2xl font-tech font-bold text-white uppercase tracking-wider">{{ $block['title'] ?? 'Latest Intel' }}</h3>
        <span class="text-xs text-slate-400">Auto-updating</span>
    </div>
    
    <div class="space-y-6">
        @foreach($latestPosts as $post)
        <article class="glass p-5 rounded-2xl hover-card flex flex-col md:flex-row gap-6 items-center">
            <a href="{{ route('frontend.post', $post->slug) }}" class="w-full md:w-48 h-32 shrink-0 rounded-xl overflow-hidden bg-slate-800 relative">
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover opacity-80 hover:opacity-100 transition">
                @endif
            </a>
            <div class="flex-1">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="text-indigo-400 text-[10px] font-tech tracking-widest uppercase mb-2 block">{{ $post->category->name ?? 'Report' }}</span></a>
                <a href="{{ route('frontend.post', $post->slug) }}" class="relative z-10">
                    <h4 class="text-xl font-tech font-bold mb-2 leading-snug hover:text-indigo-400 transition line-clamp-2">{{ $post->title }}</h4>
                </a>
                <p class="text-slate-400 text-sm line-clamp-2 mb-3 relative z-10">{{ strip_tags($post->summary ?? $post->content) }}</p>
                <div class="text-xs text-slate-500 font-tech">By {{ $post->user->name ?? 'System' }} &bull; {{ $post->created_at->diffForHumans() }}</div>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
