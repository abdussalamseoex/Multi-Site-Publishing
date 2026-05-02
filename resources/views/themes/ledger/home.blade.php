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
        
        .site-header { background-color: #0f172a !important; border-bottom: 2px solid var(--primary) !important; padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .site-header a { color: #f8fafc !important; }
        .site-header .text-primary { color: #38bdf8 !important; } /* sky-400 */
        .site-header .bg-primary { background-color: var(--primary) !important; color: white !important; }

        .border-ledger { border-color: #cbd5e1; }
        .bg-ledger-dark { background-color: #0f172a; color: white; }
        .card-hover:hover { border-color: var(--primary); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05); }
    </style>
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
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
        @php
            $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');
            $layoutRaw = \App\Models\Setting::get('theme_blocks_' . $activeTheme);
            $blocks = $layoutRaw ? json_decode($layoutRaw, true) : [];
            
            if (empty($blocks)) {
                $blocks = [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Top Stories', 'category_id' => null, 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Wire', 'category_id' => null, 'limit' => 6]
                ];
            }
        @endphp
        <!-- Market Overview / Main News -->
        @foreach($blocks as $block)
            @if($block['type'] === 'hero_grid')
                @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                    @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                @else
                    @include("themes.good.components.{$block['type']}", ['block' => $block])
                @endif
            @endif
        @endforeach

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mt-16 pt-8 border-t-4 border-slate-900">
            <!-- Latest Wire -->
            <main class="lg:col-span-2">
                @foreach($blocks as $block)
                    @if($block['type'] !== 'hero_grid')
                        @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
                            @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
                        @else
                            @include("themes.good.components.{$block['type']}", ['block' => $block])
                        @endif
                    @endif
                @endforeach
            </main>

            <!-- Sidebar -->
            <aside class="space-y-10">
                @php
                    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
                    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
                    
                    if (empty($sidebarBlocks)) {
                        $sidebarBlocks = [
                            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Most Read', 'limit' => 6],
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

    <div class="bg-slate-900 border-t border-slate-700">
        @include('themes.components.footer')
    </div>
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>


