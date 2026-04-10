<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} - {{ \App\Models\Setting::get('site_title', 'Lens') }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($post->summary ?? $post->content), 150) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#111827');
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Jost', sans-serif; background-color: #ffffff; color: #374151; }
        
        header { background-color: #ffffff !important; border-bottom: 1px solid #f3f4f6 !important; }
        header a, header svg { color: #111827 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: #ffffff !important; }
        header .bg-gray-900 { background-color: #f3f4f6 !important; color: #111827 !important; border: 1px solid #e5e7eb !important;}

        .prose-lens { max-w: 70ch; margin: 0 auto; font-family: 'Lora', serif; font-size: 1.125rem; line-height: 2; color: #4b5563; }
        .prose-lens p { margin-bottom: 2rem; }
        .prose-lens h2, .prose-lens h3 { font-family: 'Jost', sans-serif; color: #111827; font-weight: 500; margin-top: 3.5rem; margin-bottom: 1.5rem; }
        .prose-lens h2 { font-size: 2.25rem; }
        .prose-lens h3 { font-size: 1.5rem; }
        .prose-lens img { width: 100%; height: auto; margin: 3rem 0; background-color: #f9fafb; outline: 1px solid #e5e7eb; outline-offset: -1px; }
        .prose-lens a { color: var(--primary); text-decoration: underline; text-underline-offset: 4px; }
        .prose-lens blockquote { border-left: 1px solid #d1d5db; padding-left: 2rem; font-size: 1.5rem; font-style: italic; margin: 3rem 0; color: #111827; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased selection:bg-gray-900 selection:text-white">
    @include('themes.components.header')

    <main class="min-h-screen">
        <header class="py-16 md:py-24 bg-gray-50 border-b border-gray-100 px-6">
            <div class="max-w-4xl mx-auto text-center">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="inline-block hover:opacity-80 transition mb-6 mt-8">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] border border-gray-200 text-gray-500 px-4 py-1.5 hover:bg-gray-100 transition">{{ $post->category->name ?? 'Photoblog' }}</span>
                </a>
                <h1 class="text-4xl md:text-5xl lg:text-7xl font-medium text-gray-900 leading-[1.1] mb-8">{{ $post->title }}</h1>
                <div class="flex items-center justify-center gap-6 text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">
                    <span>By {{ $post->user->name ?? 'Photographer' }}</span>
                    <span>&bull;</span>
                    <span>{{ $post->created_at->format('M d, Y') }}</span>
                    <span>&bull;</span>
                    <span>{{ number_format($post->views) }} VI</span>
                </div>
            </div>
        </header>

        <!-- Signature Hero Image -->
        @if($post->featured_image)
        <div class="max-w-[1400px] mx-auto px-6 -mt-10 md:-mt-16 mb-20 relative z-10">
            <div class="w-full aspect-[16/9] md:aspect-[21/9] overflow-hidden bg-gray-100 shadow-2xl">
                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover">
            </div>
        </div>
        @else
        <div class="h-20 w-full"></div>
        @endif

        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
                <!-- Share & Meta (Sticky Left) -->
                <div class="hidden lg:block lg:col-span-2 relative">
                    <div class="sticky top-24 space-y-6 flex flex-col items-center border-r border-gray-100 pr-6">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-[0.2em] block mb-2 text-center">Share</span>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-gray-900 hover:border-gray-900 hover:text-white transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-gray-900 hover:border-gray-900 hover:text-white transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-span-1 lg:col-span-7">
                    <article class="prose-lens">
                        {!! $post->content !!}
                    </article>

                    <!-- In-Article Advertisement -->
                    @php $adContent = \App\Models\Setting::get('ad_content_code'); @endphp
                    @if($adContent)
                        <div class="mt-16 py-8 border-t border-b border-gray-100 flex justify-center w-full overflow-hidden">
                            {!! $adContent !!}
                        </div>
                    @endif

                    <!-- Author Box -->
                    <div class="mt-20 bg-gray-50 p-10 text-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name ?? 'Photographer') }}&background=111&color=fff&size=100" class="w-20 h-20 rounded-full mx-auto mb-4 object-cover border border-gray-200">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] block mb-2">Captured By</span>
                        <h3 class="text-xl font-medium text-gray-900 mb-3">{{ $post->user->name ?? 'Photographer' }}</h3>
                        <p class="text-gray-500 text-sm font-light leading-relaxed max-w-lg mx-auto">Lead photographer documenting moments across the world. Specializes in elegant portraiture, editorial fashion, and fine art landscape photography.</p>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-span-1 lg:col-span-3 space-y-16">
                    <!-- Advertising Setup -->
                    @php $adSidebar = \App\Models\Setting::get('ad_sidebar_code'); @endphp
                    @if($adSidebar)
                        <div class="w-full flex justify-center overflow-hidden">
                            {!! $adSidebar !!}
                        </div>
                    @else
                        <div class="bg-gray-50 border border-dashed border-gray-200 p-6 text-center">
                            <span class="text-[10px] uppercase text-gray-400 tracking-[0.2em] mb-4 block font-bold">Featured Partner</span>
                            <div class="w-full bg-gray-200 h-[250px] flex items-center justify-center text-gray-400 text-xs font-mono">
                                300x250 Ad Space
                            </div>
                        </div>
                    @endif

                    <!-- More from Genre -->
                    <div>
                        <h4 class="text-xs font-bold tracking-[0.2em] text-gray-900 uppercase border-b border-gray-200 pb-3 mb-6">Gallery Highlights</h4>
                        <div class="space-y-8">
                            @foreach(\App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->latest()->take(3)->get() as $rel)
                            <article class="group block text-center">
                                <a href="{{ route('frontend.post', $rel->slug) }}" class="block w-full aspect-[4/3] overflow-hidden bg-gray-100 mb-4">
                                    @if($rel->featured_image)
                                        <img src="{{ Str::startsWith($rel->featured_image, 'http') ? $rel->featured_image : url($rel->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                                    @endif
                                </a>
                                <h5 class="text-sm font-medium text-gray-900 leading-snug group-hover:text-gray-500 transition">{{ $rel->title }}</h5>
                            </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


