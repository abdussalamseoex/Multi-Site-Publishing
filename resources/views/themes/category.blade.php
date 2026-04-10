<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category->name }} - {{ \App\Models\Setting::get('site_title', 'Network') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#3b82f6');
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        header { background-color: #fff; border-bottom: 2px solid #e2e8f0; }
        header a { color: #1e293b; font-weight: 600; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
    </style>
</head>
<body class="antialiased">

    @include('themes.components.header')

    <div class="max-w-7xl mx-auto px-4 py-16">
        <!-- Category Details -->
        <div class="text-center md:text-left mb-16 border-b border-slate-200 pb-10">
            <span class="bg-blue-100 text-blue-700 font-bold uppercase tracking-widest text-xs px-3 py-1 rounded-full mb-4 inline-block">Category</span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 mb-4 tracking-tight">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-lg text-slate-600 max-w-3xl">{{ $category->description }}</p>
            @endif
        </div>

        <!-- Post Grid -->
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <article class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow duration-300 border border-slate-100 overflow-hidden flex flex-col">
                        @if($post->featured_image)
                            <a href="{{ route('frontend.post', $post->slug) }}" class="block aspect-video w-full overflow-hidden">
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                            </a>
                        @endif
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center text-xs text-slate-400 font-medium mb-3">
                                <span>{{ $post->created_at->format('M d, Y') }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ number_format($post->views) }} Reads</span>
                            </div>
                            <a href="{{ route('frontend.post', $post->slug) }}">
                                <h2 class="text-xl font-bold text-slate-900 leading-snug mb-3 hover:text-blue-600 transition-colors">{{ $post->title }}</h2>
                            </a>
                            <p class="text-slate-600 text-sm line-clamp-3 mb-4 flex-1">{{ strip_tags($post->summary ?? $post->content) }}</p>
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
        @else
            <div class="text-center py-20 bg-white rounded-2xl border border-slate-100">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No Articles Found</h3>
                <p class="text-slate-500">There are currently no articles published in this category.</p>
                <a href="/" class="inline-block mt-6 px-6 py-2 bg-slate-900 text-white font-semibold rounded-full hover:bg-slate-800 transition">Return Home</a>
            </div>
        @endif
    </div>

    @include('themes.components.footer')
</body>
</html>
