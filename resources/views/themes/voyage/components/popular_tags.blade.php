<div class="bg-gray-50 rounded-3xl p-8 border border-gray-100 mb-12">
    <h3 class="text-2xl font-travel font-black text-gray-900 mb-6">{{ $block['title'] ?? 'Popular Tags' }}</h3>
    <div class="flex flex-wrap gap-2">
        @foreach(\App\Models\Category::all()->take($block['limit'] ?? 8) as $cat)
            <a href="{{ route('frontend.category', $cat->slug) }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-pink-600 hover:text-white transition rounded-full text-xs font-bold shadow-sm border border-gray-200">{{ $cat->name }}</a>
        @endforeach
    </div>
</div>
