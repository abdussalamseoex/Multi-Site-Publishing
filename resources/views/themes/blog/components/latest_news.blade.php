@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    $latestPosts = $query->latest()->take($block['limit'] ?? 6)->get();
@endphp

@if($latestPosts->count() > 0)
    <h2 class="text-2xl font-bold border-b border-gray-300 pb-2 mb-6">{{ $block['title'] ?? 'Latest Articles' }}</h2>
    
    <div class="space-y-8">
        @foreach($latestPosts as $post)
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="text-xs font-bold text-indigo-500 uppercase mb-2">{{ $post->category->name ?? 'Uncategorized' }}</div></a>
                <h3 class="text-2xl font-bold mb-2">
                    <a href="{{ route('frontend.post', $post->slug) }}" class="hover:text-indigo-600">{{ $post->title }}</a>
                </h3>
                <p class="text-gray-600 mb-4">{{ Str::limit(strip_tags($post->content), 150) }}</p>
                <div class="text-sm text-gray-400">By {{ $post->user->name ?? 'Author' }} &bull; {{ $post->created_at->format('M d, Y') }}</div>
            </div>
        @endforeach
    </div>
@endif
