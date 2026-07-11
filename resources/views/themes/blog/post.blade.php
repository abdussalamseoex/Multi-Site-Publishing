<!DOCTYPE html>
<html lang="en">
<head>
    @include('themes.components.meta_tags')

    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="bg-gray-100 text-gray-800">

    @include('themes.components.header')

    <div class="max-w-4xl mx-auto px-4 py-12">
        <article class="bg-white p-8 md:p-12 rounded-lg shadow-sm border border-gray-200">
            <header class="mb-8 border-b pb-8">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="text-indigo-600 font-bold uppercase tracking-wider text-sm mb-2">{{ $post->category->name ?? 'Uncategorized' }}</div></a>
                <h1 class="text-3xl sm:text-4xl font-extrabold mb-4 break-words">{{ $post->title }}</h1>
                <div class="text-gray-500">
                    By <a href="{{ route('frontend.author', $post->user->slug ?? ($post->user->id ?? 1)) }}" class="font-bold text-indigo-600 hover:text-indigo-800 transition">{{ $post->user->name ?? 'Author' }}</a> &bull; {{ $post->created_at->format('F d, Y') }}
                </div>
            </header>

            <div class="prose max-w-none text-gray-700 leading-loose" style="font-size: 1.1rem;">
                {!! \App\Helpers\AdHelper::injectInArticleAds($post->content) !!}
            </div>

            {{-- Related Posts --}}
            @php
                $relatedPosts = \App\Models\Post::where('status', 'published')
                    ->where('id', '!=', $post->id)
                    ->when($post->category_id, fn($q) => $q->where('category_id', $post->category_id))
                    ->latest()
                    ->take(3)
                    ->get();
            @endphp
            @if($relatedPosts->count() > 0)
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Related Articles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($relatedPosts as $rel)
                            <div class="group">
                                @if($rel->featured_image)
                                    <a href="{{ route('frontend.post', $rel->slug) }}" class="block aspect-[16/10] bg-gray-100 rounded overflow-hidden mb-3">
                                        <img src="{{ Str::startsWith($rel->featured_image, 'http') ? $rel->featured_image : url($rel->featured_image) }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    </a>
                                @endif
                                <a href="{{ route('frontend.post', $rel->slug) }}">
                                    <h4 class="font-bold text-sm text-gray-900 group-hover:text-indigo-600 transition line-clamp-2">{{ $rel->title }}</h4>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



