@php
    // Read specific sidebar blocks
    $sidebarRaw = \App\Models\Setting::get('theme_sidebar_good');
    $sidebarBlocks = $sidebarRaw ? json_decode($sidebarRaw, true) : [];
    
    // Default sidebar structure if empty
    if (empty($sidebarBlocks)) {
        $sidebarBlocks = [
            ['type' => 'social_counter', 'title' => 'Stay Connected'],
            ['type' => 'ad_block', 'title' => 'Sidebar Ad'],
            ['type' => 'popular_posts', 'title' => 'Most Popular', 'limit' => 5],
            ['type' => 'categories_list', 'title' => 'Categories', 'limit' => 6],
            ['type' => 'newsletter', 'title' => 'Subscribe']
        ];
    }
@endphp

<aside class="space-y-10 sticky top-24">
    
    @foreach($sidebarBlocks as $block)
        
        <!-- Social Counter -->
        @if($block['type'] === 'social_counter')
        <div class="bg-white p-6 shadow-sm rounded">
            <div class="flex items-center mb-6 border-b-2 border-gray-100 pb-2">
                <h3 class="text-lg font-bold uppercase tracking-wide text-dark border-b-2 border-primary -mb-[10px] pb-2">{{ $block['title'] ?? 'Stay Connected' }}</h3>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ \App\Models\Setting::get('social_facebook', '#') }}" class="bg-[#3b5998] hover:opacity-90 transition flex flex-col items-center justify-center p-4 rounded text-white group">
                    <i class="fab fa-facebook-f text-2xl mb-1"></i>
                    <span class="text-xs font-bold">Fans</span>
                </a>
                <a href="{{ \App\Models\Setting::get('social_twitter', '#') }}" class="bg-[#1da1f2] hover:opacity-90 transition flex flex-col items-center justify-center p-4 rounded text-white group">
                    <i class="fab fa-twitter text-2xl mb-1"></i>
                    <span class="text-xs font-bold">Followers</span>
                </a>
                <a href="{{ \App\Models\Setting::get('social_instagram', '#') }}" class="bg-[#e1306c] hover:opacity-90 transition flex flex-col items-center justify-center p-4 rounded text-white group">
                    <i class="fab fa-instagram text-2xl mb-1"></i>
                    <span class="text-xs font-bold">Followers</span>
                </a>
                <a href="{{ \App\Models\Setting::get('social_youtube', '#') }}" class="bg-[#ff0000] hover:opacity-90 transition flex flex-col items-center justify-center p-4 rounded text-white group">
                    <i class="fab fa-youtube text-2xl mb-1"></i>
                    <span class="text-xs font-bold">Subscribers</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Ad Block -->
        @if($block['type'] === 'ad_block')
        <div class="bg-gray-50 p-4 border border-gray-200 text-center text-gray-400 font-bold uppercase text-xs tracking-widest min-h-[250px] flex items-center justify-center rounded overflow-hidden">
            @php
                $sidebarAd = !empty($block['ad_code']) ? $block['ad_code'] : \App\Models\Setting::get('ad_sidebar', '');
            @endphp
            @if($sidebarAd)
                {!! $sidebarAd !!}
            @else
                {{ $block['title'] ?? 'Sidebar Ad Slot' }}<br>(300x250)
            @endif
        </div>
        @endif

        <!-- Popular Posts -->
        @if($block['type'] === 'popular_posts')
        <div class="bg-white p-6 shadow-sm rounded">
            <div class="flex items-center mb-6 border-b-2 border-gray-100 pb-2">
                <h3 class="text-lg font-bold uppercase tracking-wide text-dark border-b-2 border-primary -mb-[10px] pb-2">{{ $block['title'] ?? 'Most Popular' }}</h3>
            </div>
            <div class="space-y-4">
                @php
                    $popularPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take($block['limit'] ?? 5)->get();
                    $counter = 1;
                @endphp
                @foreach($popularPosts as $post)
                <div class="flex gap-4 group items-center relative">
                    <div class="absolute -left-2 -top-2 bg-primary text-white w-6 h-6 flex items-center justify-center font-bold text-xs rounded-full z-20">
                        {{ $counter++ }}
                    </div>
                    @if($post->featured_image)
                    <div class="w-20 h-20 rounded overflow-hidden relative flex-shrink-0 z-10 border-2 border-white shadow-sm">
                        <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-10"></a>
                        <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    @endif
                    <div class="flex-1">
                        <h4 class="text-sm font-bold leading-snug mb-1 group-hover:text-primary transition line-clamp-2">
                            <a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a>
                        </h4>
                        <span class="text-gray-400 text-[10px] uppercase font-medium">{{ $post->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Categories List -->
        @if($block['type'] === 'categories_list')
        <div class="bg-white p-6 shadow-sm rounded">
            <div class="flex items-center mb-6 border-b-2 border-gray-100 pb-2">
                <h3 class="text-lg font-bold uppercase tracking-wide text-dark border-b-2 border-primary -mb-[10px] pb-2">{{ $block['title'] ?? 'Categories' }}</h3>
            </div>
            <div class="space-y-2">
                @php
                    $sidebarCategories = \App\Models\Category::withCount(['posts' => function($query) {
                        $query->where('status', 'published');
                    }])->orderBy('posts_count', 'desc')->take($block['limit'] ?? 6)->get();
                @endphp
                @foreach($sidebarCategories as $cat)
                <a href="{{ route('frontend.category', $cat->slug) }}" class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0 hover:text-primary transition font-medium text-sm group">
                    <span class="flex items-center"><i class="fas fa-chevron-right text-[10px] text-gray-300 mr-2 group-hover:text-primary transition"></i> {{ $cat->name }}</span>
                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded group-hover:bg-primary group-hover:text-white transition">{{ $cat->posts_count }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Newsletter -->
        @if($block['type'] === 'newsletter')
        <div class="bg-dark text-white p-6 shadow-sm rounded text-center">
            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="far fa-envelope text-xl"></i>
            </div>
            <h3 class="text-xl font-bold uppercase tracking-wide mb-2">{{ $block['title'] ?? 'Subscribe Now' }}</h3>
            <p class="text-xs text-gray-400 mb-6">Get the latest news and updates delivered straight to your inbox.</p>
            <form action="#" class="space-y-3">
                <input type="email" placeholder="Your Email Address" class="w-full bg-white/10 border border-white/20 rounded text-sm px-4 py-2 text-white focus:outline-none focus:border-primary">
                <button type="button" class="w-full bg-primary hover:bg-white hover:text-dark transition font-bold uppercase text-sm py-2 rounded">Subscribe</button>
            </form>
        </div>
        @endif

    @endforeach

</aside>
