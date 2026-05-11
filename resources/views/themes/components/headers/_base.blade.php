{{--
    Shared header partial — used by all theme-native headers.
    Variables expected to be set BEFORE @include-ing this file:
      $navBg       (string) — nav background CSS value e.g. 'bg-gray-900'
      $navText     (string) — text color class e.g. 'text-white'
      $logoFilter  (string) — optional Tailwind filter class e.g. 'brightness-0 invert'
      $accentClass (string) — hover/active accent e.g. 'hover:text-yellow-400'
      $borderClass (string) — bottom border e.g. 'border-b border-gray-800'
      $topBarBg    (string) — top bar bg e.g. 'bg-gray-950'
      $ctaBg       (string) — CTA button bg e.g. 'bg-yellow-400'
      $ctaText     (string) — CTA button text color e.g. 'text-black'
--}}
@php
    $logo        = \App\Models\Setting::get('site_logo');
    $siteTitle   = \App\Models\Setting::get('site_title', 'Publish.');
    $logoH       = \App\Models\Setting::get('logo_height', '40');
    $primaryColor= \App\Models\Setting::get('primary_color', '#4f46e5');
    $headerMenu  = \App\Models\Menu::where('location','header')->with('items')->first()
                   ?? \App\Models\Menu::with('items')->first();

    $navBg       = $navBg       ?? 'bg-white';
    $navText     = $navText     ?? 'text-gray-900';
    $logoFilter  = $logoFilter  ?? '';
    $accentClass = $accentClass ?? 'hover:text-primary';
    $borderClass = $borderClass ?? 'border-b border-gray-200';
    $topBarBg    = $topBarBg    ?? 'bg-gray-100';
    $ctaBg       = $ctaBg       ?? '';
    $ctaText     = $ctaText     ?? 'text-white';
@endphp

<header class="site-header sticky top-0 z-50 {{ $navBg }} {{ $borderClass }} shadow-sm">

    {{-- Optional Top Bar --}}
    @if(\App\Models\Setting::get('show_top_bar','0') == '1')
    <div class="{{ $topBarBg }} py-1.5 px-6 text-xs flex justify-between items-center border-b border-black/10">
        <div class="flex items-center gap-4">
            @php
                $topMenuId = \App\Models\Setting::get('top_bar_menu_id');
                $topMenu = $topMenuId ? \App\Models\Menu::with('items')->find($topMenuId) : null;
            @endphp

            @if($topMenu && $topMenu->items->count() > 0)
                <div class="flex items-center gap-4 {{ $navText }} font-medium">
                    @foreach($topMenu->items->whereNull('parent_id')->sortBy('order') as $item)
                        <a href="{{ $item->url }}" class="hover:opacity-100 opacity-70 transition">{{ $item->title }}</a>
                    @endforeach
                </div>
            @else
                <span class="{{ $navText }} opacity-70">{{ \App\Models\Setting::get('top_bar_text','📰 Stay updated') }}</span>
            @endif
        </div>
        
        <div class="hidden md:flex items-center gap-4 {{ $navText }} opacity-60">
            @if(\App\Models\Setting::get('social_facebook'))
                <a href="{{ \App\Models\Setting::get('social_facebook') }}" target="_blank" rel="noopener" class="hover:opacity-100 transition">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
            @endif
            @if(\App\Models\Setting::get('social_twitter'))
                <a href="{{ \App\Models\Setting::get('social_twitter') }}" target="_blank" rel="noopener" class="hover:opacity-100 transition">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                </a>
            @endif
        </div>
    </div>
    @endif

    <nav class="max-w-7xl mx-auto py-3 px-6 flex items-center justify-between gap-6">

        {{-- Logo --}}
        <div class="flex-shrink-0">
            @if($logo)
                <a href="{{ route('home') }}">
                    <img src="{{ Str::startsWith($logo,'http') ? $logo : url($logo) }}"
                         alt="{{ $siteTitle }}"
                         class="{{ $logoFilter }}"
                         style="height:{{ $logoH }}px;width:auto;object-fit:contain;">
                </a>
            @else
                <a href="{{ route('home') }}" class="font-extrabold text-xl tracking-tight {{ $navText }}">
                    {{ $siteTitle }}
                </a>
            @endif
        </div>

        {{-- Desktop Nav --}}
        <div class="hidden lg:flex items-center gap-6 text-sm font-semibold {{ $navText }}">
            @if($headerMenu && $headerMenu->items)
                @foreach($headerMenu->items->whereNull('parent_id')->sortBy('order') as $item)
                    @if($item->children->count() > 0)
                        <div class="relative group">
                            <a href="{{ $item->url }}" class="{{ $accentClass }} transition flex items-center gap-1">
                                {{ $item->title }}
                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="absolute left-0 top-full mt-2 w-48 bg-white border border-gray-100 shadow-xl rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 overflow-hidden">
                                @foreach($item->children->sortBy('order') as $child)
                                    <a href="{{ $child->url }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary transition border-b border-gray-50 last:border-0">{{ $child->title }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item->url }}" class="{{ $accentClass }} transition">{{ $item->title }}</a>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- Right: Search + Auth --}}
        <div class="flex items-center gap-3">
            <button id="hdr-search-btn" onclick="toggleHeaderSearch()" aria-label="Search"
                class="p-2 rounded-lg {{ $navText }} opacity-70 hover:opacity-100 transition">
                <svg id="hdr-search-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <svg id="hdr-close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            @if(\App\Models\Setting::get('show_header_auth_buttons','1') == '1')
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="px-4 py-2 rounded-lg font-bold text-sm transition {{ $ctaBg ? $ctaBg.' '.$ctaText : 'bg-gray-900 text-white hover:bg-gray-700' }}">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden md:inline-block text-sm font-semibold {{ $navText }} opacity-80 hover:opacity-100 transition">Log In</a>
                    @if(\App\Models\Setting::get('enable_registration','1') == '1')
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 rounded-lg font-bold text-sm text-white transition hover:opacity-90"
                           style="background-color:{{ $primaryColor }};">
                            Sign Up
                        </a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    {{-- Search Dropdown --}}
    <div id="hdr-search-panel"
         style="max-height:0;overflow:hidden;transition:max-height .3s ease;"
         class="{{ $navBg }} border-t border-black/10">
        <div class="max-w-7xl mx-auto px-6 pb-4 pt-3">
            <form action="{{ route('frontend.search') }}" method="GET" class="flex gap-2">
                <input id="hdr-search-input" type="text" name="q" value="{{ request('q') }}"
                       placeholder="Search articles, topics…" autocomplete="off"
                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:border-gray-400 transition"
                       onkeydown="if(event.key==='Escape'){toggleHeaderSearch(false);}">
                <button type="submit" class="px-5 py-2.5 rounded-lg font-bold text-sm text-white transition hover:opacity-90"
                        style="background-color:{{ $primaryColor }};">Search</button>
            </form>
        </div>
    </div>
</header>

<x-ad-slot placement="header" class="max-w-7xl mx-auto px-4 mt-4" />

<script>
(function(){
    var open={{ request()->routeIs('frontend.search')?'true':'false' }};
    function applyState(){
        var p=document.getElementById('hdr-search-panel');
        var si=document.getElementById('hdr-search-icon');
        var ci=document.getElementById('hdr-close-icon');
        if(!p)return;
        p.style.maxHeight=open?'80px':'0';
        if(si)si.classList.toggle('hidden',open);
        if(ci)ci.classList.toggle('hidden',!open);
        if(open){setTimeout(function(){var i=document.getElementById('hdr-search-input');if(i)i.focus();},320);}
    }
    window.toggleHeaderSearch=function(f){open=(typeof f!=='undefined')?!!f:!open;applyState();};
    document.addEventListener('DOMContentLoaded',applyState);
    document.addEventListener('click',function(e){
        if(!open)return;
        var btn=document.getElementById('hdr-search-btn');
        var panel=document.getElementById('hdr-search-panel');
        if(btn&&!btn.contains(e.target)&&panel&&!panel.contains(e.target)){open=false;applyState();}
    });
})();
</script>
