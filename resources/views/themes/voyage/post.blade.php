<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <title>{{ $post->meta_title ?? $post->title }}</title>
    <meta name="description" content="{{ $post->meta_description }}">
    @if($post->meta_keywords)
    <meta name="keywords" content="{{ $post->meta_keywords }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    
    <meta property="og:title" content="{{ $post->meta_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->meta_description }}">
    @if($post->featured_image)
    <meta property="og:image" content="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#db2777');
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #fff; color: #1f2937; }
        .font-travel { font-family: 'Poppins', sans-serif; }
        
        header { background-color: rgba(255, 255, 255, 0.95) !important; border-bottom: 2px solid #f3f4f6 !important; backdrop-filter: blur(8px) !important; }
        header a, header svg { color: #111827 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .prose p { margin-bottom: 1.5rem; font-size: 1.1875rem; line-height: 1.8; color: #4b5563; }
        .prose img { width: 100%; border-radius: 1.5rem; margin: 3rem 0; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); }
        .prose h2 { font-family: 'Poppins', sans-serif; font-size: 2.25rem; font-weight: 800; color: #111827; margin-top: 3.5rem; margin-bottom: 1rem; line-height: 1.2; letter-spacing: -0.02em; }
        .prose h3 { font-family: 'Poppins', sans-serif; font-size: 1.75rem; font-weight: 700; color: #1f2937; margin-top: 2.5rem; margin-bottom: 1rem; }
        .prose a { color: var(--primary); text-decoration: none; font-weight: 600; box-shadow: inset 0 -2px 0 0 var(--primary); transition: all 0.2s; }
        .prose a:hover { box-shadow: inset 0 -10px 0 0 var(--primary); color: white; }
        .prose blockquote { border: none; font-style: italic; color: #111827; font-size: 1.5rem; margin: 3rem 0; text-align: center; font-family: 'Poppins', sans-serif; font-weight: 600; }
        .prose blockquote::before { content: '"'; font-size: 3rem; color: var(--primary); display: block; height: 1rem; }
        .prose ul { padding-left: 1.5rem; list-style-type: disc; color: #4b5563; margin-bottom: 2rem; font-size: 1.1875rem; }
        .prose li { margin-bottom: 0.5rem; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased">

    @include('themes.components.header')

    <!-- Post Cover Full Width -->
    <div class="relative w-full h-[60vh] min-h-[400px] bg-gray-900 flex items-center justify-center">
        @if($post->featured_image)
            <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="absolute inset-0 w-full h-full object-cover opacity-70">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/30 to-transparent"></div>
        
        <div class="relative z-10 text-center px-4 max-w-4xl mt-16">
            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="bg-white/20 backdrop-blur text-white px-4 py-2 text-xs font-bold uppercase tracking-widest rounded-full mb-6 inline-block shadow-sm">{{ $post->category->name ?? 'Story' }}</span></a>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-travel font-black text-white leading-tight mb-8">{{ $post->title }}</h1>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 -mt-10 relative z-20">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-10 flex items-center justify-between mb-12">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center font-bold text-xl">{{ substr($post->user->name ?? 'A', 0, 1) }}</div>
                <div>
                    <h5 class="font-bold text-gray-900">{{ $post->user->name ?? 'Traveler' }}</h5>
                    <p class="text-xs text-gray-500 font-medium">{{ $post->created_at->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Impact</p>
                <div class="font-travel font-black text-2xl text-pink-600">{{ number_format($post->views) }}</div>
            </div>
        </div>

        <article class="prose max-w-none">
            {!! $post->content !!}
        </article>

        <!-- More Destinations -->
        <div class="mt-24 pt-12 border-t border-gray-100 text-center mb-16">
            <h3 class="text-3xl font-travel font-black text-gray-900 mb-10">Discover More</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-left">
                @foreach(\App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->take(2)->get() as $rel)
                    <a href="{{ route('frontend.post', $rel->slug) }}" class="group block">
                        @if($rel->featured_image)
                            <div class="w-full aspect-[4/5] bg-gray-100 mb-4 rounded-3xl overflow-hidden shadow-lg group-hover:shadow-2xl transition">
                                <img src="{{ url($rel->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                            </div>
                        @else
                            <div class="w-full aspect-[4/5] bg-pink-50 mb-4 rounded-3xl border-2 border-pink-100"></div>
                        @endif
                        <h4 class="font-travel font-black text-2xl text-gray-900 group-hover:text-pink-600 transition leading-snug line-clamp-2">{{ $rel->title }}</h4>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>



