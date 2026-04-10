<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <title>{{ $post->meta_title ?? $post->title }}</title>
    <meta name="description" content="{{ $post->meta_description }}">
    @if($post->canonical_url)
    <link rel="canonical" href="{{ $post->canonical_url }}">
    @endif
    
    <meta property="og:title" content="{{ $post->meta_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->meta_description }}">
    @if($post->featured_image)
    <meta property="og:image" content="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#0ea5e9'); // Default blue
    @endphp
    <style> 
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Roboto', sans-serif; background-color: #fbfbfb; color: #111; }
        .font-ui { font-family: 'Inter', sans-serif; }
        
        header { background-color: #fff !important; border-bottom: 3px solid #000 !important; }
        header a { color: #111 !important; font-weight: 600; font-family: 'Inter', sans-serif; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .section-title { font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #111; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 20px; position: relative; }
        .section-title::after { content: absolute; left: 0; bottom: -2px; width: 40px; height: 2px; background-color: var(--primary); }

        .prose { font-family: 'Roboto', sans-serif; font-size: 1.125rem; line-height: 1.7; color: #333; }
        .prose p { margin-bottom: 1.5rem; }
        .prose img { width: 100%; margin: 2.5rem 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .prose h2 { font-size: 1.8rem; font-weight: 700; color: #111; margin-top: 3rem; margin-bottom: 1rem; }
        .prose h3 { font-size: 1.4rem; font-weight: 700; color: #222; margin-top: 2rem; margin-bottom: 1rem; }
        .prose a { color: var(--primary); text-decoration: none; font-weight: 600; transition: color 0.2s; }
        .prose a:hover { color: #111; text-decoration: underline; }
        .prose blockquote { border-left: 3px solid var(--primary); padding-left: 1.5rem; font-style: italic; color: #555; font-size: 1.4rem; margin: 3rem 0; font-weight: 500;}
        .prose ul { padding-left: 1.5rem; list-style-type: square; margin-bottom: 1.5rem; }
        .prose li { margin-bottom: 0.5rem; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased selection:bg-sky-500 selection:text-white">

    @include('themes.components.header')

    <div class="max-w-[1200px] mx-auto px-4 py-10">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LEFT COLUMN (Main Content - 2/3 width) -->
            <div class="lg:col-span-2">
                <header class="mb-6">
                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="bg-black text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 mb-4 inline-block hover:bg-sky-600 transition cursor-pointer">{{ $post->category->name ?? 'News' }}</span></a>
                    <h1 class="text-3xl md:text-5xl font-bold leading-[1.15] text-[#111] mb-5 tracking-tight line-clamp-2">{{ $post->title }}</h1>
                    
                    <div class="flex items-center text-xs font-ui text-gray-500 border-b border-gray-200 pb-4 mb-6">
                        <span class="font-bold text-black uppercase tracking-wider">{{ $post->user->name ?? 'Admin' }}</span>
                        <span class="mx-3">-</span>
                        <span class="uppercase tracking-wider">{{ $post->created_at->format('M d, Y') }}</span>
                        <span class="mx-3">-</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            {{ number_format($post->views) }}
                        </span>
                    </div>
                </header>

                @if($post->featured_image)
                    <figure class="mb-8">
                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-auto object-cover max-h-[600px]">
                    </figure>
                @endif

                <div class="flex">
                    <!-- Social Share Side -->
                    <div class="hidden sm:block w-12 mr-6 shrink-0 pt-2">
                        <div class="sticky top-20 flex flex-col gap-2">
                            <a href="#" class="w-10 h-10 bg-[#3b5998] text-white flex items-center justify-center hover:opacity-90"><svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                            <a href="#" class="w-10 h-10 bg-[#1da1f2] text-white flex items-center justify-center hover:opacity-90"><svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></a>
                            <a href="#" class="w-10 h-10 bg-[#cb2027] text-white flex items-center justify-center hover:opacity-90"><svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.356-.29 1.172-.329 1.332-.053.219-.17.265-.401.157-1.495-.695-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.951-7.252 4.168 0 7.41 2.967 7.41 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg></a>
                        </div>
                    </div>
                    
                    <!-- Content Area -->
                    <div class="flex-1">
                        <article class="prose max-w-none mb-12">
                            {!! $post->content !!}
                        </article>

                        <!-- Author Box -->
                        <div class="bg-gray-50 border border-gray-200 p-6 flex flex-col sm:flex-row gap-6 items-center sm:items-start mb-12">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name ?? 'Admin') }}&background=0ea5e9&color=fff&size=100" class="w-20 h-20 shrink-0">
                            <div class="text-center sm:text-left">
                                <h4 class="font-bold text-lg mb-1">{{ $post->user->name ?? 'Staff Editor' }}</h4>
                                <p class="text-gray-600 text-sm mb-3">Professional journalist and editor specializing in breaking news, tech trends, and lifestyle analysis.</p>
                                <a href="#" class="text-sky-600 font-bold text-xs uppercase tracking-widest hover:text-black transition">More from author</a>
                            </div>
                        </div>

                        <!-- Related Articles -->
                        <h3 class="section-title">Related Articles</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-12">
                            @foreach(\App\Models\Post::where('category_id', $post->category_id)->where('id', '!=', $post->id)->where('status', 'published')->take(2)->get() as $rel)
                                <div class="group">
                                    <a href="{{ route('frontend.post', $rel->slug) }}" class="block">
                                        @if($rel->featured_image)
                                            <div class="aspect-[16/10] overflow-hidden mb-3 relative">
                                                <img src="{{ url($rel->featured_image) }}" class="w-full h-full object-cover transition transform duration-500 group-hover:scale-105">
                                            </div>
                                        @endif
                                        <h4 class="text-base font-bold leading-snug mb-1 group-hover:text-sky-600 transition line-clamp-2">{{ $rel->title }}</h4>
                                        <div class="font-ui text-[10px] text-gray-400 font-medium">{{ $rel->created_at->format('M d, Y') }}</div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (Sidebar - 1/3 width) -->
            <div class="lg:col-span-1 space-y-10">
                <!-- ADVERTISEMENT -->
                @php $adSidebar = \App\Models\Setting::get('ad_sidebar_code'); @endphp
                @if($adSidebar)
                    <div class="widget text-center overflow-hidden flex justify-center">
                        {!! $adSidebar !!}
                    </div>
                @else
                    <div class="widget text-center">
                        <div class="text-[9px] text-gray-400 uppercase tracking-widest mb-1">- Advertisement -</div>
                        <div class="w-full bg-gray-200 h-[250px] flex items-center justify-center font-bold text-gray-400 text-sm border border-gray-300">
                            300 x 250 Banner
                        </div>
                    </div>
                @endif

                <!-- LATEST NEWS -->
                <div class="widget">
                    <h3 class="section-title">Latest News</h3>
                    <div class="space-y-4">
                        @foreach(\App\Models\Post::where('status', 'published')->latest()->take(5)->get() as $pop)
                        <a href="{{ route('frontend.post', $pop->slug) }}" class="flex gap-4 group items-center">
                            @if($pop->featured_image)
                                <div class="w-16 h-16 shrink-0 overflow-hidden relative">
                                    <img src="{{ url($pop->featured_image) }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition"></div>
                                </div>
                            @endif
                            <div>
                                <h4 class="text-sm font-bold leading-snug group-hover:text-blue-600 transition line-clamp-2 mb-1">{{ $pop->title }}</h4>
                                <div class="font-ui text-[10px] text-gray-400">{{ $pop->created_at->format('M d, Y') }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="mt-8">
        @include('themes.components.footer')
    </div>
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


