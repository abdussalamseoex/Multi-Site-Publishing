<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $query ? 'Search: '.e($query) : 'Search' }} &mdash; {{ \App\Models\Setting::get('site_title', 'Network') }}</title>
    <meta name="description" content="Search results for &ldquo;{{ e($query) }}&rdquo; on {{ \App\Models\Setting::get('site_title', 'Network') }}">
    <meta name="robots" content="noindex, follow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#3b82f6');
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .btn-primary { background-color: var(--primary); color: #fff; }
        .btn-primary:hover { opacity: 0.88; }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .ring-primary:focus { --tw-ring-color: var(--primary); }
        /* Search input focus ring */
        #search-input:focus { outline: none; box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 25%, transparent); border-color: var(--primary); }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased">

    @include('themes.components.header')

    <div class="max-w-7xl mx-auto px-4 py-14">

        {{-- ── Search Bar ─────────────────────────────── --}}
        <div class="mb-12">
            <form action="{{ route('frontend.search') }}" method="GET" class="flex gap-3 max-w-2xl mx-auto">
                <input
                    id="search-input"
                    type="text"
                    name="q"
                    value="{{ e($query) }}"
                    placeholder="Search articles, topics&hellip;"
                    autofocus
                    class="flex-1 px-5 py-3.5 border border-slate-200 rounded-xl text-sm bg-white shadow-sm transition-all"
                >
                <button type="submit" class="btn-primary px-6 py-3.5 rounded-xl font-bold text-sm transition-opacity flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    Search
                </button>
            </form>
        </div>

        {{-- ── Results Header ─────────────────────────── --}}
        <div class="mb-10 border-b border-slate-200 pb-8">
            @if($query)
                <span class="bg-blue-100 text-blue-700 font-bold uppercase tracking-widest text-xs px-3 py-1 rounded-full mb-4 inline-block">Results</span>
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 tracking-tight">
                    &ldquo;{{ e($query) }}&rdquo;
                </h1>
                <p class="text-slate-500 mt-2 text-sm">
                    {{ $posts->total() }} {{ Str::plural('article', $posts->total()) }} found
                </p>
            @else
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 tracking-tight">Search</h1>
                <p class="text-slate-500 mt-2 text-sm">Enter a keyword above to find articles.</p>
            @endif
        </div>

        {{-- ── Post Grid ───────────────────────────────── --}}
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <article class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow duration-300 border border-slate-100 overflow-hidden flex flex-col">
                        @if($post->featured_image)
                            <a href="{{ route('frontend.post', $post->slug) }}" class="block aspect-video w-full overflow-hidden">
                                <img
                                    src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}"
                                    alt="{{ $post->title }}"
                                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                    loading="lazy"
                                >
                            </a>
                        @endif

                        <div class="p-6 flex-1 flex flex-col">
                            {{-- Category badge --}}
                            @if($post->category)
                                <a href="{{ route('frontend.category', $post->category->slug) }}"
                                   class="text-xs font-bold uppercase tracking-widest text-primary mb-2 inline-block hover:opacity-70 transition">
                                    {{ $post->category->name }}
                                </a>
                            @endif

                            {{-- Meta --}}
                            <div class="flex items-center text-xs text-slate-400 font-medium mb-3">
                                <span>{{ $post->created_at->format('M d, Y') }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ number_format($post->views) }} reads</span>
                            </div>

                            {{-- Title with query highlight --}}
                            <a href="{{ route('frontend.post', $post->slug) }}">
                                <h2 class="text-lg font-bold text-slate-900 leading-snug mb-3 hover:text-primary transition-colors">
                                    {!! $query
                                        ? preg_replace('/(' . preg_quote(e($query), '/') . ')/iu',
                                            '<mark class="bg-yellow-100 text-slate-900 rounded px-0.5">$1</mark>',
                                            e($post->title))
                                        : e($post->title) !!}
                                </h2>
                            </a>

                            <p class="text-slate-500 text-sm line-clamp-3 mb-4 flex-1">
                                {{ Str::limit(strip_tags($post->summary ?? $post->content), 130) }}
                            </p>

                            {{-- Author --}}
                            <div class="flex items-center mt-auto border-t border-slate-50 pt-4">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs mr-3">
                                    {{ substr($post->user->name ?? 'A', 0, 1) }}
                                </div>
                                <span class="text-sm font-semibold text-slate-700">{{ $post->user->name ?? 'Staff' }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-16 text-center">
                {{ $posts->links() }}
            </div>

        @elseif($query)
            {{-- Empty State --}}
            <div class="text-center py-24 bg-white rounded-2xl border border-slate-100">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No results for &ldquo;{{ e($query) }}&rdquo;</h3>
                <p class="text-slate-400 text-sm mb-6">Try a different keyword or check your spelling.</p>
                <a href="/" class="inline-block px-6 py-2 bg-slate-900 text-white font-semibold rounded-full hover:bg-slate-800 transition text-sm">
                    Return Home
                </a>
            </div>
        @endif
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>
