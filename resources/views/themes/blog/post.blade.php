<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $post->meta_title ?? $post->title }}</title>
    <meta name="description" content="{{ $post->meta_description }}">
    @if($post->canonical_url)
    <link rel="canonical" href="{{ $post->canonical_url }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

    @include('themes.components.header')

    <div class="max-w-4xl mx-auto px-4 py-12">
        <article class="bg-white p-8 md:p-12 rounded-lg shadow-sm border border-gray-200">
            <header class="mb-8 border-b pb-8">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="text-indigo-600 font-bold uppercase tracking-wider text-sm mb-2">{{ $post->category->name ?? 'Uncategorized' }}</div></a>
                <h1 class="text-4xl font-extrabold mb-4 line-clamp-2">{{ $post->title }}</h1>
                <div class="text-gray-500">
                    By <strong>{{ $post->user->name ?? 'Author' }}</strong> &bull; {{ $post->created_at->format('F d, Y') }}
                </div>
            </header>

            <div class="prose max-w-none text-gray-700 leading-loose" style="font-size: 1.1rem;">
                {!! $post->content !!}
            </div>
        </article>
    </div>

    @include('themes.components.footer')
</body>
</html>
