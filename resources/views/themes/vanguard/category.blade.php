<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Vanguard Elite') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;600;700&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#ea580c'); // orange-600 default
        $font = \App\Models\Setting::get('typography', 'Inter');
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: '{{ $font }}', sans-serif; background-color: #f3f4f6; }
        .font-gaming { font-family: 'Teko', sans-serif; }
        /* Dark Header Override */
        header { background-color: #0f172a !important; border-bottom: 2px solid var(--primary) !important; backdrop-filter: none !important; }
        header a, header svg { color: #e2e8f0 !important; }
        header .text-primary { color: var(--primary) !important; }
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }
        header .bg-gray-900 { background-color: #334155 !important; }
        
        .badge-orange { background-color: #f97316; color: white; }
        .badge-blue { background-color: #3b82f6; color: white; }
        .badge-red { background-color: #ef4444; color: white; }
        .badge-green { background-color: #10b981; color: white; }
        .badge-purple { background-color: #8b5cf6; color: white; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="text-gray-900 antialiased">
    @include('themes.components.header')

    @if(isset($category))
    <div class="bg-slate-50/80 border-b border-slate-200" style="background-color: var(--bg-category, #f8fafc); border-bottom: 1px solid var(--border-category, #e2e8f0)">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-16 text-center">
            <span class="text-[10px] font-black uppercase tracking-widest text-primary mb-3 block opacity-80">Category</span>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4" style="color: var(--text-main, #0f172a)">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-lg opacity-70 max-w-2xl mx-auto" style="color: var(--text-muted, #475569)">{{ $category->description }}</p>
            @endif
        </div>
    </div>
    @endif


    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <main class="lg:col-span-3 space-y-12">
                @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($posts as $post)
                    <article class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-200 hover:shadow-xl transition group flex flex-col h-full relative">
                        <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                        @if($post->featured_image)
                            <div class="aspect-[4/3] bg-slate-100 overflow-hidden relative">
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <div class="absolute bottom-0 right-0 bg-gray-900 text-white font-bold text-[11px] px-3 py-1.5 rounded-tl-lg shadow-lg flex items-center gap-1 border-t border-l border-gray-700 z-10">
                                    <span class="text-yellow-400">★</span> {{ number_format(4 + ($post->id % 10) / 10, 1) }}
                                </div>
                            </div>
                        @endif
                        <div class="p-6 flex-1 flex flex-col relative z-10 pointer-events-none">
                            <h3 class="font-gaming font-bold text-xl leading-tight mb-3 text-slate-800 group-hover:text-primary transition pointer-events-auto line-clamp-2"><a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a></h3>
                            <p class="text-slate-500 text-sm line-clamp-3 mb-4 flex-1">{{ strip_tags($post->summary ?? $post->content) }}</p>
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center justify-between border-t border-slate-100 pt-4 mt-auto">
                                <span>{{ $post->user->name ?? 'Author' }}</span>
                                <span>{{ $post->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                
                @if($posts->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $posts->links() }}
                </div>
                @endif
                
                @else
                <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm">
                    <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <h3 class="text-2xl font-gaming font-bold text-slate-700 mb-2">No Posts Found</h3>
                    <p class="text-slate-500">There are no articles published in this category yet.</p>
                </div>
                @endif
            </main>

            <!-- Sidebar -->
            <aside class="space-y-8">
                @php
                    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                    
                    if (empty($sidebarBlocks)) {
                        $sidebarBlocks = [
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Most Popular', 'limit' => 5],
                            ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad']
                        ];
                    }
                @endphp

                @foreach($sidebarBlocks as $block)
                    @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                        @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                    @else
                        @include("themes.good.components.{$block['type']}", ['block' => $block])
                    @endif
                @endforeach
            </aside>
        </div>
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


