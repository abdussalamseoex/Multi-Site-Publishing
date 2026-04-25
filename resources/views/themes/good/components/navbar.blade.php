<!-- Top Bar -->
<div class="bg-dark text-white text-xs py-2 px-4 flex justify-between items-center max-w-[1200px] mx-auto w-full">
    <div class="flex space-x-4">
        <a href="{{ \App\Models\Setting::get('social_facebook', '#') }}" class="hover:text-primary transition"><i class="fab fa-facebook-f"></i></a>
        <a href="{{ \App\Models\Setting::get('social_twitter', '#') }}" class="hover:text-primary transition"><i class="fab fa-twitter"></i></a>
        <a href="{{ \App\Models\Setting::get('social_instagram', '#') }}" class="hover:text-primary transition"><i class="fab fa-instagram"></i></a>
        <a href="{{ \App\Models\Setting::get('social_youtube', '#') }}" class="hover:text-primary transition"><i class="fab fa-youtube"></i></a>
    </div>
    <div class="hidden md:block">
        <a href="#" class="font-bold hover:text-primary transition">GOOD</a> THEMES &bull; TRENDING NEWS TODAY
    </div>
</div>

<!-- Main Header -->
<header class="bg-dark shadow-sm sticky top-0 z-50">
    <div class="max-w-[1200px] mx-auto w-full px-4 flex justify-between items-center h-20">
        
        <!-- Logo -->
        <a href="{{ url('/') }}" class="flex items-center">
            @if(\App\Models\Setting::get('site_logo'))
                <img src="{{ url(\App\Models\Setting::get('site_logo')) }}" alt="Logo" class="h-8 md:h-10">
            @else
                <span class="text-3xl font-black tracking-tight text-white uppercase">{{ \App\Models\Setting::get('site_title', 'GOOD') }}</span>
            @endif
        </a>

        <!-- Desktop Menu -->
        <nav class="hidden md:flex space-x-6">
            <a href="{{ url('/') }}" class="text-white font-semibold text-sm hover:text-primary transition uppercase tracking-wide">Home</a>
            
            @php
                $menu = \App\Models\Menu::with('items')->first();
            @endphp
            @if($menu && $menu->items)
                @foreach($menu->items->where('parent_id', null) as $item)
                    <a href="{{ $item->url }}" class="text-white font-semibold text-sm hover:text-primary transition uppercase tracking-wide">{{ $item->title }}</a>
                @endforeach
            @else
                <a href="{{ route('frontend.category', 'technology') }}" class="text-white font-semibold text-sm hover:text-primary transition uppercase tracking-wide">Technology</a>
                <a href="{{ route('frontend.category', 'business') }}" class="text-white font-semibold text-sm hover:text-primary transition uppercase tracking-wide">Business</a>
                <a href="{{ route('frontend.category', 'lifestyle') }}" class="text-white font-semibold text-sm hover:text-primary transition uppercase tracking-wide">Lifestyle</a>
            @endif
        </nav>

        <!-- Search & Mobile Toggle -->
        <div class="flex items-center space-x-4 text-white">
            <button class="hover:text-primary transition"><i class="fas fa-search"></i></button>
            <button class="md:hidden hover:text-primary transition text-xl"><i class="fas fa-bars"></i></button>
        </div>
    </div>
</header>
