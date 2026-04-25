@php
    $logo = \App\Models\Setting::get('site_logo');
    $headerMenu = \App\Models\Menu::where('location', 'header')->with('items')->first() ?? \App\Models\Menu::with('items')->first();
    $headerBg = \App\Models\Setting::get('header_bg_color', '#ffffff');
    $headerText = \App\Models\Setting::get('header_text_color', '#1f2937');
@endphp

<header class="sticky top-0 z-50 backdrop-blur-md shadow-sm" style="background-color: {{ $headerBg }}e6 !important; border-bottom: 1px solid {{ $headerBg === '#ffffff' ? '#f3f4f6' : 'rgba(255,255,255,0.1)' }} !important;">
    <nav class="max-w-7xl mx-auto py-4 px-6 flex justify-between items-center">
        <!-- Logo -->
        <div class="flex-shrink-0">
            @if($logo)
                <a href="{{ route('home') }}">
                    <img src="{{ Str::startsWith($logo, 'http') ? $logo : url($logo) }}" alt="Logo" style="height: {{ \App\Models\Setting::get('logo_height', '40') }}px; width: auto; object-fit: contain;">
                </a>
            @else
                <a href="{{ route('home') }}" class="font-extrabold text-2xl tracking-tight flex items-center gap-2" style="color: {{ $headerText }} !important;">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    {{ \App\Models\Setting::get('site_title', 'Publish.') }}
                </a>
            @endif
        </div>

        <!-- Desktop Menu -->
        <div class="hidden lg:flex items-center gap-8 text-sm font-semibold" style="color: {{ $headerText }} !important;">
            @if($headerMenu && $headerMenu->items)
                @foreach($headerMenu->items->whereNull('parent_id')->sortBy('order') as $item)
                    @if($item->children->count() > 0)
                        <div class="relative group">
                            <a href="{{ $item->url }}" class="hover:opacity-70 transition-opacity duration-200 flex items-center gap-1" style="color: {{ $headerText }} !important;">
                                {{ $item->title }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </a>
                            <div class="absolute left-0 mt-2 w-48 bg-white border border-gray-100 shadow-xl rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 overflow-hidden" style="color: #1f2937 !important;">
                                @foreach($item->children->sortBy('order') as $child)
                                    <a href="{{ $child->url }}" class="block px-4 py-3 text-sm hover:bg-gray-50 hover:text-primary transition-colors border-b border-gray-50 last:border-0" style="color: #1f2937 !important;">{{ $child->title }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item->url }}" class="hover:opacity-70 transition-opacity duration-200" style="color: {{ $headerText }} !important;">{{ $item->title }}</a>
                    @endif
                @endforeach
            @endif
        </div>
        
        <!-- Auth Buttons -->
        <div class="flex items-center gap-4">
            @auth
                <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-gray-900 text-white rounded-lg font-bold hover:bg-gray-800 transition shadow-sm text-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="hidden md:inline-block font-bold text-sm text-gray-700 hover:opacity-80 transition" style="color: {{ $headerText }} !important;">Log In</a>
                @if(\App\Models\Setting::get('enable_registration', '1') == '1')
                    <a href="{{ route('register') }}" class="px-5 py-2.5 bg-primary text-white rounded-lg font-bold hover:opacity-90 transition shadow-md shadow-primary/30 text-sm">Sign Up</a>
                @endif
            @endauth
        </div>
    </nav>
</header>

<!-- Header Ad Slot -->
<x-ad-slot placement="header" class="max-w-7xl mx-auto px-4 mt-4" />

