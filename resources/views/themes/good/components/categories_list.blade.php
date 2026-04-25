@php
    $categories = \App\Models\Category::withCount(['posts' => function($query) {
        $query->where('status', 'published');
    }])->orderBy('posts_count', 'desc')->take($block['limit'] ?? 8)->get();
@endphp

@if($categories->count() > 0)
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5">{{ $block['title'] ?? 'Categories' }}</h3>
    <ul class="space-y-3">
        @foreach($categories as $category)
        <li>
            <a href="{{ route('frontend.category', $category->slug) }}" class="flex items-center justify-between group">
                <span class="text-sm font-medium text-gray-700 group-hover:text-primary transition">{{ $category->name }}</span>
                <span class="bg-gray-50 text-gray-500 text-xs py-1 px-2.5 rounded-full font-bold group-hover:bg-primary/10 group-hover:text-primary transition">{{ $category->posts_count }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif
