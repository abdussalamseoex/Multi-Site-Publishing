@php
    $limit = $block['limit'] ?? 5;
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    
    // First try to get featured posts
    $featuredPosts = (clone $query)->where('is_featured', true)->latest()->take($limit)->get();
    
    // If not enough featured posts, fill the gap with latest regular posts
    if ($featuredPosts->count() < $limit) {
        $remaining = $limit - $featuredPosts->count();
        $regularPosts = (clone $query)->where('is_featured', false)->latest()->take($remaining)->get();
        $featuredPosts = $featuredPosts->merge($regularPosts);
    }
@endphp

@if($featuredPosts->count() >= 5)
<div class="mb-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-1 h-auto lg:h-[480px]">
        <!-- Large Left Item (Col Span 2) -->
        @php $mainHero = $featuredPosts[0]; @endphp
        <div class="lg:col-span-2 relative group overflow-hidden bg-gray-900 border border-white">
            <article class="block w-full h-full relative">
                <a href="{{ route('frontend.post', $mainHero->slug) }}" class="absolute inset-0 z-10" aria-label="{{ $mainHero->title }}"></a>
                @if($mainHero->featured_image)
                    <img src="{{ Str::startsWith($mainHero->featured_image, 'http') ? $mainHero->featured_image : url($mainHero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover hover-img">
                @endif
                <div class="absolute inset-0 overlay-gradient"></div>
                <div class="absolute bottom-0 left-0 p-6 md:p-8 w-full z-20">
                    <a href="{{ isset($mainHero->category) ? route('frontend.category', $mainHero->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-30"><span class="bg-sky-500 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 mb-3 inline-block">{{ $mainHero->category->name ?? 'News' }}</span></a>
                    <a href="{{ route('frontend.post', $mainHero->slug) }}" class="relative z-30 block">
                        <h2 class="text-white text-3xl md:text-4xl font-bold leading-[1.15] mb-3 group-hover:text-blue-200 transition line-clamp-2">{{ $mainHero->title }}</h2>
                    </a>
                    <div class="flex items-center text-xs font-ui text-gray-300 relative z-30">
                        <span class="font-bold text-white">{{ $mainHero->user->name ?? 'Admin' }}</span>
                        <span class="mx-2">-</span>
                        <span>{{ $mainHero->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </article>
        </div>

        <!-- Right Items (2x2 Grid) -->
        <div class="lg:col-span-2 grid grid-cols-2 grid-rows-2 gap-1">
            @foreach($featuredPosts->skip(1)->take(4) as $index => $subHero)
            <div class="relative group overflow-hidden bg-gray-900 border border-white h-[200px] lg:h-auto">
                <article class="block w-full h-full relative">
                    <a href="{{ route('frontend.post', $subHero->slug) }}" class="absolute inset-0 z-10" aria-label="{{ $subHero->title }}"></a>
                    @if($subHero->featured_image)
                        <img src="{{ Str::startsWith($subHero->featured_image, 'http') ? $subHero->featured_image : url($subHero->featured_image) }}" class="absolute inset-0 w-full h-full object-cover hover-img">
                    @endif
                    <div class="absolute inset-0 overlay-gradient opacity-90"></div>
                    <div class="absolute bottom-0 left-0 p-4 z-20">
                        @php 
                            $colors = ['bg-pink-500', 'bg-blue-600', 'bg-green-500', 'bg-orange-500'];
                            $badgeColor = $colors[$index % count($colors)];
                        @endphp
                        <a href="{{ isset($subHero->category) ? route('frontend.category', $subHero->category->slug) : '#' }}" class="hover:opacity-80 transition relative z-30"><span class="{{ $badgeColor }} text-white text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 mb-2 inline-block">{{ $subHero->category->name ?? 'Style' }}</span></a>
                        <a href="{{ route('frontend.post', $subHero->slug) }}" class="relative z-30 block">
                            <h3 class="text-white text-sm md:text-base font-bold leading-tight mb-2 group-hover:text-gray-300 transition line-clamp-3">{{ $subHero->title }}</h3>
                        </a>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
