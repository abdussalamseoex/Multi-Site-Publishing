@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
<section class="mb-12">
    <h3 class="text-3xl font-gaming font-bold border-b-2 border-slate-300 pb-2 mb-6 uppercase text-gray-900"><span class="border-b-4 border-primary pb-3">{{ $block['title'] ?? 'Latest Reviews' }}</span></h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($latestPosts as $index => $post)
        @php 
            $colors = ['badge-orange', 'badge-blue', 'badge-red', 'badge-green', 'badge-purple'];
            $badgeClass = $colors[$index % count($colors)];
        @endphp
        <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden group hover:shadow-xl transition flex flex-col h-full">
            <article class="block relative h-56 overflow-hidden relative">
                <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                    <div class="w-full h-full bg-slate-200"></div>
                @endif
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="{{ $badgeClass }} absolute top-3 left-3 px-3 py-1 text-[11px] font-black uppercase tracking-widest rounded shadow-lg">{{ $post->category->name ?? 'Review' }}</span></a>
                <div class="absolute bottom-0 right-0 bg-gray-900 text-white font-bold text-[11px] px-3 py-1.5 rounded-tl-lg shadow-lg flex items-center gap-1 border-t border-l border-gray-700">
                    <span class="text-yellow-400">★</span> {{ number_format(4 + ($post->id % 10) / 10, 1) }}
                </div>
            </article>
            <div class="p-6 flex-1 flex flex-col">
                <a href="{{ route('frontend.post', $post->slug) }}" class="mb-auto">
                    <h4 class="text-xl font-black mb-3 group-hover:text-primary transition leading-tight line-clamp-2">{{ $post->title }}</h4>
                </a>
                <p class="text-gray-500 text-sm mb-4 line-clamp-2 mt-auto">{{ strip_tags($post->summary ?? $post->content) }}</p>
                <div class="flex items-center text-xs text-gray-400 font-bold uppercase gap-2 border-t border-slate-100 pt-4">
                    <span>{{ $post->user->name ?? 'Staff' }}</span> &bull; 
                    <span>{{ $post->views }} Views</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif
