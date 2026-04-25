@php
    $query = \App\Models\Post::where('status', 'published')->where('is_featured', true);
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $featuredPosts = $query->latest()->take($block['limit'] ?? 4)->get();
@endphp

@if($featuredPosts->count() > 0)
    @php $hero = $featuredPosts->first(); @endphp
    <div class="relative rounded-[2rem] overflow-hidden mb-16 h-[500px] shadow-2xl flex items-center group border-4 border-white">
        @if($hero->featured_image)
            <img src="{{ Str::startsWith($hero->featured_image, 'http') ? $hero->featured_image : url($hero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-1000">
        @else
            <div class="absolute inset-0 bg-emerald-900"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/90 via-emerald-900/60 to-transparent"></div>
        
        <div class="relative z-10 max-w-2xl px-10 md:px-16 text-white">
            <a href="{{ isset($hero->category) ? route('frontend.category', $hero->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-20"><span class="bg-white text-emerald-700 px-4 py-1.5 text-xs font-wellness font-bold uppercase tracking-widest rounded-full shadow-lg mb-6 inline-block">{{ $hero->category->name ?? 'Wellness' }}</span></a>
            <a href="{{ route('frontend.post', $hero->slug) }}" class="relative z-20">
                <h2 class="text-4xl md:text-6xl font-wellness font-bold leading-tight mb-4 drop-shadow-md line-clamp-2">{{ $hero->title }}</h2>
            </a>
            <p class="text-emerald-50 text-lg mb-8 font-light line-clamp-2 leading-relaxed relative z-20">{{ strip_tags($hero->summary ?? $hero->content) }}</p>
            <a href="{{ route('frontend.post', $hero->slug) }}" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold px-6 py-3 rounded-full transition shadow-lg shadow-emerald-500/30 relative z-20">
                Read Guide <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </div>
    
    @if($featuredPosts->count() > 1)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
        @foreach($featuredPosts->skip(1)->take(3) as $post)
        <article class="vitality-card p-4 flex flex-col group relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            <div class="w-full aspect-video rounded-xl overflow-hidden mb-5 relative">
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @endif
                <div class="absolute inset-0 bg-emerald-900/10 group-hover:bg-transparent transition"></div>
            </div>
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-10"><span class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest mb-2 px-2">{{ $post->category->name ?? 'Health' }}</span></a>
            <h3 class="text-xl font-wellness font-bold leading-tight mb-3 px-2 group-hover:text-emerald-600 transition line-clamp-2 relative z-10"><a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a></h3>
        </article>
        @endforeach
    </div>
    @endif
@endif
