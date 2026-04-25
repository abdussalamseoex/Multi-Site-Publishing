@php
    $footerMenu = \App\Models\Menu::where('location', 'footer')->with('items')->first();
    $footerCategoriesMenu = \App\Models\Menu::where('location', 'footer_categories')->with('items')->first();
    $aboutText = \App\Models\Setting::get('site_description', 'We are a premier publishing platform dedicated to bringing you the best content from talented authors around the globe.');
    $siteTitle = \App\Models\Setting::get('site_title', 'Publish.');
    $logo = \App\Models\Setting::get('site_logo');
@endphp

<footer class="bg-white border-t border-gray-200 pt-16 pb-8 mt-16">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
        
        <!-- Brand & About -->
        <div class="space-y-4">
            @if($logo)
                <a href="{{ route('home') }}">
                    <img src="{{ Str::startsWith($logo, 'http') ? $logo : url($logo) }}" alt="Logo" class="mb-6 opacity-80 hover:opacity-100 transition" style="height: {{ \App\Models\Setting::get('logo_height', '40') }}px; width: auto; object-fit: contain;">
                </a>
            @else
                <a href="{{ route('home') }}" class="font-extrabold text-2xl tracking-tight text-primary block mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    {{ $siteTitle }}
                </a>
            @endif
            <p class="text-gray-500 text-sm leading-relaxed">{{ Str::limit($aboutText, 150) }}</p>
            
            <div class="flex items-center gap-4 pt-2">
                @if(\App\Models\Setting::get('social_facebook'))
                <a href="{{ \App\Models\Setting::get('social_facebook') }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-blue-600 transition">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                @endif
                
                @if(\App\Models\Setting::get('social_twitter'))
                <a href="{{ \App\Models\Setting::get('social_twitter') }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-black transition">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 4.15H5.078z"/></svg>
                </a>
                @endif
                
                @if(\App\Models\Setting::get('social_instagram'))
                <a href="{{ \App\Models\Setting::get('social_instagram') }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-pink-600 transition">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.20 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
                @endif
                
                @if(\App\Models\Setting::get('social_youtube'))
                <a href="{{ \App\Models\Setting::get('social_youtube') }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-red-600 transition">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                </a>
                @endif
            </div>
        </div>

        <!-- Legal / Footer Menu -->
        <div>
            <h3 class="font-bold text-gray-900 mb-6 uppercase tracking-wider text-sm">Legal & Links</h3>
            <ul class="space-y-3 text-sm text-gray-500 font-medium">
                @if($footerMenu && $footerMenu->items)
                    @foreach($footerMenu->items->whereNull('parent_id')->sortBy('order') as $item)
                        <li>
                            <a href="{{ $item->url }}" class="hover:text-primary transition-colors flex items-center gap-2"><span class="text-primary hover-opacity-80">&#8250;</span> {{ $item->title }}</a>
                            @if($item->children->count() > 0)
                                <ul class="ml-4 mt-2 space-y-2 border-l border-gray-200 pl-2">
                                    @foreach($item->children->sortBy('order') as $child)
                                        <li><a href="{{ $child->url }}" class="hover:text-primary transition-colors flex items-center gap-2 text-xs"><span class="text-gray-300">-</span> {{ $child->title }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                @else
                    <li><a href="/" class="hover:text-primary transition-colors">Home</a></li>
                @endif
            </ul>
        </div>

        <!-- Categories / Quick Links -->
        <div>
            <h3 class="font-bold text-gray-900 mb-6 uppercase tracking-wider text-sm">Categories</h3>
            <ul class="space-y-3 text-sm text-gray-500 font-medium">
                @if($footerCategoriesMenu && $footerCategoriesMenu->items->count() > 0)
                    @foreach($footerCategoriesMenu->items->whereNull('parent_id')->sortBy('order') as $item)
                        <li>
                            <a href="{{ $item->url }}" class="hover:text-primary transition-colors flex items-center gap-2"><span class="text-primary">&#8250;</span> {{ $item->title }}</a>
                            @if($item->children->count() > 0)
                                <ul class="ml-4 mt-2 space-y-2 border-l border-gray-200 pl-2">
                                    @foreach($item->children->sortBy('order') as $child)
                                        <li><a href="{{ $child->url }}" class="hover:text-primary transition-colors flex items-center gap-2 text-xs"><span class="text-gray-300">-</span> {{ $child->title }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                @else
                    @foreach(\App\Models\Category::whereNull('parent_id')->take(5)->get() as $cat)
                        <li><a href="{{ route('frontend.category', $cat->slug) }}" class="hover:text-primary transition-colors flex items-center gap-2"><span class="text-primary">&#8250;</span> {{ $cat->name }}</a></li>
                    @endforeach
                @endif
            </ul>
        </div>

        <!-- Footer Ad Space -->
        <x-ad-slot placement="footer" />
    </div>

    <div class="max-w-7xl mx-auto px-6 mt-16 pt-8 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-gray-500 font-medium">
        <p>{!! \App\Models\Setting::get('footer_copyright_text', '&copy; ' . date('Y') . ' ' . $siteTitle . '. All rights reserved.') !!}</p>
        <p class="flex items-center gap-1">
            Developed with <span class="text-red-500">❤️</span> by 
            <a href="{{ \App\Models\Setting::get('footer_credit_url', '#') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-gray-900 hover:text-primary transition-colors ml-1">
                {{ \App\Models\Setting::get('footer_credit_text', 'Abdus Salam SEO Expert') }}
            </a>
        </p>
    </div>

    <x-social-contact-widgets />

    <!-- FLOATING ADS (DESKTOP ONLY) -->
    <div class="hidden lg:block">
        @if(!empty(\App\Models\Setting::get('ad_placement_floating_left')))
            <div style="position: fixed; left: 10px; top: 50%; transform: translateY(-50%); z-index: 40; max-width: 160px; text-align:center;">
                <span class="text-[8px] text-gray-400 block uppercase mb-1 leading-none">Advertisement</span>
                <x-ad-slot placement="floating_left" />
            </div>
        @endif
        @if(!empty(\App\Models\Setting::get('ad_placement_floating_right')))
            <div style="position: fixed; right: 10px; top: 50%; transform: translateY(-50%); z-index: 40; max-width: 160px; text-align:center;">
                <span class="text-[8px] text-gray-400 block uppercase mb-1 leading-none">Advertisement</span>
                <x-ad-slot placement="floating_right" />
            </div>
        @endif
    </div>

    <!-- GLOBAL BACKGROUND SCRIPTS -->
    @if(\App\Models\Setting::get('enable_popunder') == '1')
        {!! \App\Models\Setting::get('ad_code_popunder', '') !!}
    @endif
    @if(\App\Models\Setting::get('enable_socialbar') == '1')
        {!! \App\Models\Setting::get('ad_code_socialbar', '') !!}
    @endif

</footer>

