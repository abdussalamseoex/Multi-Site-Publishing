<!-- Top Bar -->
<div class="bg-[#111] text-gray-400 text-[11px] py-2 px-4 flex justify-between items-center w-full border-b border-[#222]">
    <div class="max-w-[1200px] mx-auto w-full flex justify-between items-center">
        <div class="flex space-x-4">
            <span class="text-white font-bold mr-2"><i class="fas fa-bolt text-primary mr-1"></i> TRENDING:</span>
            @php
                $trendingTop = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->first();
            @endphp
            @if($trendingTop)
                <a href="{{ route('frontend.post', $trendingTop->slug) }}" class="hover:text-primary transition line-clamp-1">{{ $trendingTop->title }}</a>
            @endif
        </div>
        <div class="hidden md:flex space-x-4 items-center">
            <span>{{ now()->format('l, F d, Y') }}</span>
            <div class="flex space-x-3 ml-4 border-l border-gray-700 pl-4">
                <a href="{{ \App\Models\Setting::get('social_facebook', '#') }}" class="hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                <a href="{{ \App\Models\Setting::get('social_twitter', '#') }}" class="hover:text-white transition"><i class="fab fa-twitter"></i></a>
                <a href="{{ \App\Models\Setting::get('social_instagram', '#') }}" class="hover:text-white transition"><i class="fab fa-instagram"></i></a>
                <a href="{{ \App\Models\Setting::get('social_youtube', '#') }}" class="hover:text-white transition"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="bg-white shadow-sm sticky top-0 z-50 border-b-2 border-primary">
    <div class="max-w-[1200px] mx-auto w-full px-4 flex justify-between items-center h-[90px]">
        
        <!-- Logo -->
        <a href="{{ url('/') }}" class="flex items-center flex-shrink-0">
            @if(\App\Models\Setting::get('site_logo'))
                <img src="{{ url(\App\Models\Setting::get('site_logo')) }}" alt="Logo" class="h-10 md:h-12 object-contain">
            @else
                <span class="text-4xl font-black tracking-tighter text-dark uppercase">{{ \App\Models\Setting::get('site_title', 'GOOD') }}<span class="text-primary">.</span></span>
            @endif
        </a>

        <!-- Desktop Menu -->
        <nav class="hidden md:flex space-x-8 items-center h-full">
            <a href="{{ url('/') }}" class="text-dark font-bold text-[13px] hover:text-primary transition uppercase tracking-widest flex items-center h-full border-b-2 border-transparent hover:border-primary">HOME</a>
            
            @php
                $menu = \App\Models\Menu::with('items')->first();
            @endphp
            @if($menu && $menu->items && $menu->items->count() > 0)
                @foreach($menu->items->where('parent_id', null) as $item)
                    <a href="{{ $item->url }}" class="text-dark font-bold text-[13px] hover:text-primary transition uppercase tracking-widest flex items-center h-full border-b-2 border-transparent hover:border-primary">{{ $item->title }}</a>
                @endforeach
            @else
                <!-- Fallback dummy menu if no menu created -->
                <a href="{{ route('frontend.category', 'technology') }}" class="text-dark font-bold text-[13px] hover:text-primary transition uppercase tracking-widest flex items-center h-full border-b-2 border-transparent hover:border-primary">TECHNOLOGY</a>
                <a href="{{ route('frontend.category', 'business') }}" class="text-dark font-bold text-[13px] hover:text-primary transition uppercase tracking-widest flex items-center h-full border-b-2 border-transparent hover:border-primary">BUSINESS</a>
                <a href="{{ route('frontend.category', 'lifestyle') }}" class="text-dark font-bold text-[13px] hover:text-primary transition uppercase tracking-widest flex items-center h-full border-b-2 border-transparent hover:border-primary">LIFESTYLE</a>
            @endif
        </nav>

        <!-- Search & Mobile Toggle -->
        <div class="flex items-center space-x-5 text-dark">
            <div class="hidden md:flex items-center space-x-3 border-r border-gray-200 pr-5 mr-1">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-xs font-bold uppercase tracking-widest hover:text-primary transition"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold uppercase tracking-widest hover:text-primary transition">Login</a>
                    <a href="{{ route('register') }}" class="bg-primary text-white text-xs font-bold uppercase tracking-widest px-4 py-2 rounded hover:bg-dark transition">Sign Up</a>
                @endauth
            </div>
            <button class="hover:text-primary transition text-lg"><i class="fas fa-search"></i></button>
            <button class="md:hidden hover:text-primary transition text-2xl"><i class="fas fa-bars"></i></button>
        </div>
    </div>
</header>
