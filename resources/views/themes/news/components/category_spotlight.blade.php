@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    // Spotlight uses random or latest, let's use latest
    $dontMissPosts = $query->latest()->take($block['limit'] ?? 5)->get();
@endphp

@if($dontMissPosts->count() > 0)
<section class="mb-10">
    <h3 class="section-title">{{ $block['title'] ?? "Don't Miss" }}</h3>
    <div class="flex flex-col md:flex-row gap-6">
        @php $firstMiss = $dontMissPosts->first(); @endphp
        <!-- Big Item -->
        <div class="flex-1 group">
            <a href="{{ route('frontend.post', $firstMiss->slug) }}" class="block">
                @if($firstMiss->featured_image)
                    <div class="aspect-video overflow-hidden mb-4">
                        <img src="{{ Str::startsWith($firstMiss->featured_image, 'http') ? $firstMiss->featured_image : url($firstMiss->featured_image) }}" class="w-full h-full object-cover hover-img">
                    </div>
                @endif
                <h4 class="text-2xl font-bold leading-snug mb-2 group-hover:text-blue-600 transition line-clamp-2">{{ $firstMiss->title }}</h4>
            </a>
            <p class="text-sm text-gray-600 line-clamp-3 mb-3">{{ strip_tags($firstMiss->summary ?? $firstMiss->content) }}</p>
            <div class="font-ui text-[11px] text-gray-400 font-medium">
                <span class="text-black font-bold">{{ $firstMiss->user->name ?? 'Editor' }}</span> - {{ $firstMiss->created_at->format('M d, Y') }}
            </div>
        </div>
        
        <!-- List Items -->
        <div class="flex-1 flex flex-col gap-4">
            @foreach($dontMissPosts->skip(1) as $missPost)
            <a href="{{ route('frontend.post', $missPost->slug) }}" class="flex gap-4 group items-center">
                @if($missPost->featured_image)
                    <div class="w-24 h-[70px] shrink-0 overflow-hidden">
                        <img src="{{ Str::startsWith($missPost->featured_image, 'http') ? $missPost->featured_image : url($missPost->featured_image) }}" class="w-full h-full object-cover hover-img">
                    </div>
                @endif
                <div>
                    <h5 class="text-sm font-bold leading-snug group-hover:text-blue-600 transition line-clamp-2 mb-1">{{ $missPost->title }}</h5>
                    <div class="font-ui text-[10px] text-gray-400">{{ $missPost->created_at->format('M d, Y') }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
