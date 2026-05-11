@php
    $logo = \App\Models\Setting::get('site_logo');
    $headerMenu = \App\Models\Menu::where('location', 'header')->with('items')->first() ?? \App\Models\Menu::with('items')->first();
    $siteTitle = \App\Models\Setting::get('site_title', 'Publish.');
    $primaryColor = \App\Models\Setting::get('primary_color', '#4f46e5');
@endphp

<header class="site-header sticky top-0 z-50 bg-gray-950 border-b border-gray-800 shadow-lg shadow-black/30">

    {{-- Top bar --}}
    @if(\App\Models\Setting::get('show_top_bar', '0') == '1')
    <div class="bg-gray-900 border-b border-gray-800 py-1.5 px-6 text-xs text-gray-400 flex justify-between items-center">
        <div class="hidden md:flex items-center gap-6">
            @if(\App\Models\Setting::get('top_bar_text'))
                <span>{{ \App\Models\Setting::get('top_bar_text') }}</span>
            @else
                <span>📰 Stay updated with the latest news</span>
            @endif
        </div>
        <div class="flex items-center gap-4 ml-auto">
            @if(\App\Models\Setting::get('social_facebook'))
                <a href="{{ \App\Models\Setting::get('social_facebook') }}" target="_blank" rel="noopener" class="hover:text-white transition">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
            @endif
            @if(\App\Models\Setting::get('social_twitter'))
                <a href="{{ \App\Models\Setting::get('social_twitter') }}" target="_blank" rel="noopener" class="hover:text-white transition">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                </a>
            @endif
            @if(\App\Models\Setting::get('social_instagram'))
                <a href="{{ \App\Models\Setting::get('social_instagram') }}" target="_blank" rel="noopener" class="hover:text-white transition">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.20 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Main nav --}}
    <nav class="max-w-7xl mx-auto py-3 px-6 flex items-center justify-between gap-6">

        {{-- Logo --}}
        <div class="flex-shrink-0">
            @if($logo)
                <a href="{{ route('home') }}">
                    <img src="{{ Str::startsWith($logo, 'http') ? $logo : url($logo) }}" alt="{{ $siteTitle }}" style="height: {{ \App\Models\Setting::get('logo_height', '40') }}px; width:auto; object-fit:contain; filter: brightness(1.1);">
                </a>
            @else
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="font-extrabold text-2xl tracking-tight text-white">{{ $siteTitle }}</span>
                </a>
            @endif
        </div>

        {{-- Desktop Nav --}}
        <div class="hidden lg:flex items-center gap-6 text-sm font-semibold text-gray-300">
            @if($headerMenu && $headerMenu->items)
                @foreach($headerMenu->items->whereNull('parent_id')->sortBy('order') as $item)
                    @if($item->children->count() > 0)
                        <div class="relative group">
                            <a href="{{ $item->url }}" class="hover:text-white transition flex items-center gap-1">
                                {{ $item->title }}
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="absolute left-0 top-full mt-2 w-48 bg-gray-900 border border-gray-700 shadow-2xl rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 overflow-hidden">
                                @foreach($item->children->sortBy('order') as $child)
                                    <a href="{{ $child->url }}" class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition border-b border-gray-800 last:border-0">{{ $child->title }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item->url }}" class="hover:text-white transition">{{ $item->title }}</a>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- Right side --}}
        <div class="flex items-center gap-3">
            {{-- Search --}}
            <button id="hdr-search-btn" aria-label="Toggle search" onclick="toggleHeaderSearch()"
                class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition flex-shrink-0">
                <svg id="hdr-search-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <svg id="hdr-close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Auth --}}
            @if(\App\Models\Setting::get('show_header_auth_buttons', '1') == '1')
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-white/10 border border-white/20 text-white rounded-lg font-bold text-sm hover:bg-white/20 transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden md:inline-block text-gray-300 font-semibold text-sm hover:text-white transition">Log In</a>
                    @if(\App\Models\Setting::get('enable_registration', '1') == '1')
                        <a href="{{ route('register') }}" class="px-4 py-2 text-white rounded-lg font-bold text-sm hover:opacity-90 transition shadow-lg" style="background-color: {{ $primaryColor }};">Sign Up</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    {{-- Search panel --}}
    <div id="hdr-search-panel" style="max-height:0; overflow:hidden; transition:max-height .3s ease; background:#111827; border-top:1px solid #1f2937;">
        <div class="max-w-7xl mx-auto px-6 pb-4 pt-3">
            <form action="{{ route('frontend.search') }}" method="GET" class="flex gap-2">
                <input id="hdr-search-input" type="text" name="q" value="{{ request('q') }}"
                    placeholder="Search articles, topics…" autocomplete="off"
                    class="flex-1 px-4 py-2.5 border border-gray-700 rounded-lg text-sm bg-gray-800 text-white placeholder-gray-500 focus:outline-none focus:border-gray-500 transition"
                    onkeydown="if(event.key==='Escape'){toggleHeaderSearch(false);}">
                <button type="submit" class="px-5 py-2.5 rounded-lg font-bold text-sm text-white transition" style="background-color:{{ $primaryColor }};">Search</button>
            </form>
        </div>
    </div>
</header>

{{-- Header Ad Slot --}}
<x-ad-slot placement="header" class="max-w-7xl mx-auto px-4 mt-4" />

<script>
(function () {
    var open = {{ request()->routeIs('frontend.search') ? 'true' : 'false' }};
    function applyState() {
        var panel = document.getElementById('hdr-search-panel');
        var si    = document.getElementById('hdr-search-icon');
        var ci    = document.getElementById('hdr-close-icon');
        if (!panel) return;
        panel.style.maxHeight = open ? '80px' : '0';
        if (si) si.classList.toggle('hidden', open);
        if (ci) ci.classList.toggle('hidden', !open);
        if (open) { setTimeout(function () { var inp = document.getElementById('hdr-search-input'); if (inp) inp.focus(); }, 320); }
    }
    window.toggleHeaderSearch = function (force) { open = (typeof force !== 'undefined') ? !!force : !open; applyState(); };
    document.addEventListener('DOMContentLoaded', applyState);
    document.addEventListener('click', function (e) {
        if (!open) return;
        var btn = document.getElementById('hdr-search-btn');
        var panel = document.getElementById('hdr-search-panel');
        if (btn && !btn.contains(e.target) && panel && !panel.contains(e.target)) { open = false; applyState(); }
    });
})();
</script>
