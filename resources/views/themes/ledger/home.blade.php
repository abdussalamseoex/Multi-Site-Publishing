<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'Ledger Finance') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#0369a1'); // sky-700
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .font-mono-data { font-family: 'Roboto Mono', monospace; }
        
        header { background-color: #0f172a !important; border-bottom: 2px solid var(--primary) !important; padding-top: 0.5rem; padding-bottom: 0.5rem; }
        header a { color: #f8fafc !important; }
        header .text-primary { color: #38bdf8 !important; } /* sky-400 */
        header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .border-ledger { border-color: #cbd5e1; }
        .bg-ledger-dark { background-color: #0f172a; color: white; }
        .card-hover:hover { border-color: var(--primary); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05); }
    </style>
</head>
<body class="antialiased">
    <!-- Ticker Tape (Static Example) -->
    <div class="bg-ledger-dark text-[10px] font-mono-data tracking-widest text-slate-400 py-1.5 border-b border-slate-700 flex justify-between px-4 overflow-hidden">
        <span class="flex gap-6 whitespace-nowrap animate-pulse">
            <span class="text-green-400">BTC +2.4%</span> <span class="text-red-400">ETH -0.8%</span> <span>S&P500 +1.1%</span> <span>NASDAQ +0.9%</span> <span class="text-green-400">GOLD +3.2%</span>
        </span>
        <span class="hidden md:inline">{{ date('Y-m-d H:i T') }}</span>
    </div>

    @include('themes.components.header')

    @if(isset($isCategory) && $isCategory)
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


    <div class="max-w-7xl mx-auto px-4 py-10">
        <!-- Market Overview / Main News -->
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
                
                @php $mainFeatured = $featuredPosts->first(); @endphp
                <div class="lg:col-span-8">
                    <article class="block group relative">
    <a href="{{ route('frontend.post', $mainFeatured->slug) }}" class="absolute inset-0 z-0"></a>
                        @if($mainFeatured->featured_image)
                            <div class="w-full aspect-[16/9] bg-slate-200 overflow-hidden mb-4 border border-ledger rounded">
                                <img src="{{ Str::startsWith($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : url($mainFeatured->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 grayscale group-hover:grayscale-0">
                            </div>
                        @else
                            <div class="w-full h-64 bg-slate-800 mb-4 border border-ledger rounded flex items-center justify-center text-slate-600 font-mono-data text-xs">NO MARKET IMAGE AVAILABLE</div>
                        @endif
                        <a href="{{ isset($mainFeatured->category) ? route('frontend.category', $mainFeatured->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-xs font-bold text-sky-700 uppercase tracking-wider mb-2 block border-l-4 border-sky-700 pl-2">{{ $mainFeatured->category->name ?? 'Markets' }}</span></a>
                        <h2 class="text-4xl md:text-5xl font-black tracking-tight leading-tight mb-3 line-clamp-2">{{ $mainFeatured->title }}</h2>
                        <p class="text-slate-600 text-lg mb-4">{{ strip_tags($mainFeatured->summary ?? $mainFeatured->content) }}</p>
                        <div class="text-xs text-slate-500 font-mono-data uppercase">{{ $mainFeatured->user->name ?? 'Editor' }} | {{ $mainFeatured->created_at->format('Y-m-d') }}</div>
                    </article>
                </div>

                <div class="lg:col-span-4 flex flex-col justify-between">
                    <h3 class="font-black text-xl uppercase tracking-wider border-b-2 border-slate-900 pb-2 mb-4">Market Watch</h3>
                    <div class="flex-1 space-y-6">
                        @foreach($featuredPosts->skip(1)->take(4) as $post)
                        <article class="block group border-b border-ledger pb-4 relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                            <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] font-bold text-sky-700 uppercase tracking-wider mb-1 block">{{ $post->category->name ?? 'Update' }}</span></a>
                            <h4 class="text-lg font-bold leading-snug group-hover:text-sky-700 transition line-clamp-2">{{ $post->title }}</h4>
                            <div class="text-[10px] text-slate-500 font-mono-data uppercase mt-2">{{ $post->created_at->diffForHumans() }}</div>
                        </article>
                        @endforeach
                    </div>
                </div>

            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mt-16 pt-8 border-t-4 border-slate-900">
            <!-- Latest Wire -->
            <main class="lg:col-span-2">
                @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
                <h3 class="font-black text-2xl uppercase tracking-wider mb-8">Latest Wire</h3>
                <div class="space-y-0 border border-ledger bg-white rounded shadow-sm">
                    @foreach($latestPosts as $index => $post)
                    <article class="p-6 {{ !$loop->last ? 'border-b border-ledger' : '' }} hover:bg-slate-50 transition card-hover block">
                        <article class="flex flex-col sm:flex-row gap-6 relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span class="text-[10px] bg-slate-900 text-white px-2 py-0.5 rounded-sm font-bold uppercase tracking-wider">{{ $post->category->name ?? 'Finance' }}</span></a>
                                    <span class="text-[10px] text-slate-500 font-mono-data">{{ $post->created_at->format('M d, H:i') }}</span>
                                </div>
                                <h4 class="text-2xl font-bold mb-2 leading-tight text-slate-900 line-clamp-2">{{ $post->title }}</h4>
                                <p class="text-slate-600 text-sm mb-3 line-clamp-2 md:w-11/12">{{ strip_tags($post->summary ?? $post->content) }}</p>
                            </div>
                            @if($post->featured_image)
                                <div class="w-full sm:w-32 h-24 shrink-0 rounded overflow-hidden bg-slate-200 border border-ledger mt-4 sm:mt-0">
                                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover grayscale opacity-80 mix-blend-multiply">
                                </div>
                            @endif
                        </article>
                    </article>
                    @endforeach
                </div>
                
                <div class="mt-8 font-mono-data">{{ $latestPosts->links() }}</div>
                @endif
            </main>

            <!-- Sidebar -->
            <aside class="space-y-10">
                <!-- Most Read index -->
                <div class="border border-ledger bg-white p-6 rounded shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 bg-sky-700 w-32 h-32 rounded-full -mr-16 -mt-16 blur-sm"></div>
                    <h3 class="font-black text-lg uppercase tracking-wider border-b-2 border-slate-900 pb-2 mb-4 relative z-10">Most Read</h3>
                    <div class="space-y-4">
                        @foreach(\App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take(6)->get() as $pop)
                        <a href="{{ route('frontend.post', $pop->slug) }}" class="flex items-start gap-4 group">
                            <div class="text-2xl font-mono-data font-black text-slate-300 group-hover:text-sky-700 transition">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                            <div>
                                <h4 class="text-sm font-bold leading-snug text-slate-800">{{ Str::limit($pop->title, 60) }}</h4>
                                <div class="text-[10px] text-slate-500 font-mono-data mt-1">{{ number_format($pop->views) }} VOL</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Advertising Setup -->
                @php $adSidebar = \App\Models\Setting::get('ad_sidebar_code'); @endphp
                @if($adSidebar)
                    <div class="w-full overflow-hidden mb-6 flex justify-center">
                        {!! $adSidebar !!}
                    </div>
                @else
                    <div class="bg-slate-100 rounded border border-ledger p-4 text-center">
                        <span class="text-[10px] font-mono-data text-slate-400 block mb-2">ADVERTISEMENT</span>
                        <div class="h-[250px] bg-slate-200 flex items-center justify-center mx-auto border border-dashed border-slate-400">
                            <span class="text-slate-400 text-sm font-mono-data">300x250</span>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>

    <div class="bg-slate-900 border-t border-slate-700">
        @include('themes.components.footer')
    </div>
</body>
</html>

