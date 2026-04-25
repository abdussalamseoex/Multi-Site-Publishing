<footer class="bg-[#111] text-gray-400 mt-16 pt-16 border-t-[5px] border-primary">
    <div class="max-w-[1200px] mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-12">
        
        <!-- About Column -->
        <div>
            <a href="{{ url('/') }}" class="inline-block mb-6">
                @if(\App\Models\Setting::get('site_logo'))
                    <img src="{{ url(\App\Models\Setting::get('site_logo')) }}" alt="Logo" class="h-12 filter grayscale hover:grayscale-0 transition duration-500">
                @else
                    <span class="text-4xl font-black tracking-tighter text-white uppercase">{{ \App\Models\Setting::get('site_title', 'GOOD') }}<span class="text-primary">.</span></span>
                @endif
            </a>
            <p class="text-[13px] mb-6 leading-relaxed text-gray-500">{{ \App\Models\Setting::get('footer_description', \App\Models\Setting::get('site_tagline', 'The Ultimate News Experience bringing you the latest updates around the clock. We cover technology, business, lifestyle, and global trends.')) }}</p>
            
            <div class="flex space-x-3 mt-6">
                <a href="{{ \App\Models\Setting::get('social_facebook', '#') }}" class="w-8 h-8 rounded bg-[#222] flex items-center justify-center hover:bg-primary text-white transition"><i class="fab fa-facebook-f text-sm"></i></a>
                <a href="{{ \App\Models\Setting::get('social_twitter', '#') }}" class="w-8 h-8 rounded bg-[#222] flex items-center justify-center hover:bg-primary text-white transition"><i class="fab fa-twitter text-sm"></i></a>
                <a href="{{ \App\Models\Setting::get('social_instagram', '#') }}" class="w-8 h-8 rounded bg-[#222] flex items-center justify-center hover:bg-primary text-white transition"><i class="fab fa-instagram text-sm"></i></a>
                <a href="{{ \App\Models\Setting::get('social_youtube', '#') }}" class="w-8 h-8 rounded bg-[#222] flex items-center justify-center hover:bg-primary text-white transition"><i class="fab fa-youtube text-sm"></i></a>
            </div>
        </div>

        <!-- Editor's Picks Column -->
        <div>
            <h4 class="text-white font-bold uppercase mb-6 tracking-widest text-[13px] border-b border-[#222] pb-3"><span class="border-b-2 border-primary pb-[13px]">Editor's Picks</span></h4>
            @php
                // Get featured posts for footer
                $footerPosts = \App\Models\Post::where('status', 'published')->where('is_featured', true)->take(3)->get();
                if($footerPosts->isEmpty()) {
                    $footerPosts = \App\Models\Post::where('status', 'published')->latest()->take(3)->get();
                }
            @endphp
            <div class="space-y-5">
                @foreach($footerPosts as $fp)
                <div class="flex gap-4 group items-center">
                    @if($fp->featured_image)
                    <div class="w-16 h-16 rounded overflow-hidden flex-shrink-0">
                        <img src="{{ Str::startsWith($fp->featured_image, 'http') ? $fp->featured_image : url($fp->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    @endif
                    <div>
                        <h5 class="text-gray-300 font-bold text-[13px] leading-snug hover:text-primary transition line-clamp-2">
                            <a href="{{ route('frontend.post', $fp->slug) }}">{{ $fp->title }}</a>
                        </h5>
                        <span class="text-[10px] text-gray-500 uppercase mt-1 block font-medium">{{ $fp->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Popular Categories Column -->
        <div>
            <h4 class="text-white font-bold uppercase mb-6 tracking-widest text-[13px] border-b border-[#222] pb-3"><span class="border-b-2 border-primary pb-[13px]">Top Categories</span></h4>
            <div class="flex flex-wrap gap-2">
                @php
                    $footerCategories = \App\Models\Category::withCount(['posts' => function($query) {
                        $query->where('status', 'published');
                    }])->orderBy('posts_count', 'desc')->take(8)->get();
                @endphp
                @foreach($footerCategories as $cat)
                <a href="{{ route('frontend.category', $cat->slug) }}" class="bg-[#222] hover:bg-primary hover:text-white transition text-xs font-bold px-3 py-1.5 rounded text-gray-400">
                    {{ $cat->name }} <span class="opacity-50 ml-1">{{ $cat->posts_count }}</span>
                </a>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Copyright Bar -->
    <div class="bg-black mt-12 py-6 border-t border-[#222]">
        <div class="max-w-[1200px] mx-auto px-4 flex flex-col md:flex-row justify-between items-center text-[11px] text-gray-500 font-medium uppercase tracking-wider">
            <div>
                {!! \App\Models\Setting::get('footer_copyright_text', '&copy; ' . date('Y') . ' GOOD THEME. ALL RIGHTS RESERVED.') !!} 
                DESIGNED WITH <i class="fas fa-heart text-red-500 mx-1"></i> BY <a href="{{ url('/') }}" class="text-white hover:text-primary transition">{{ \App\Models\Setting::get('site_title', 'YOUR BRAND') }}</a>.
            </div>
            <div class="mt-4 md:mt-0 flex flex-wrap gap-4 items-center">
                <a href="{{ url('/') }}" class="hover:text-primary transition">HOME</a>
                <a href="#" class="hover:text-primary transition">ABOUT US</a>
                <a href="#" class="hover:text-primary transition">CONTACT</a>
                
                <span class="text-gray-700 hidden md:inline">|</span>
                
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-primary text-white transition font-bold"><i class="fas fa-tachometer-alt mr-1"></i> DASHBOARD</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-primary text-white transition font-bold"><i class="fas fa-sign-in-alt mr-1"></i> LOGIN</a>
                    <a href="{{ route('register') }}" class="hover:text-primary text-white transition font-bold"><i class="fas fa-user-plus mr-1"></i> SIGN UP</a>
                @endauth
            </div>
        </div>
    </div>
</footer>
