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

                @if(\App\Models\Setting::get('show_header_auth_buttons', '1') == '1')
                    <span class="text-gray-700 hidden md:inline">|</span>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-primary text-white transition font-bold"><i class="fas fa-tachometer-alt mr-1"></i> DASHBOARD</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-primary text-white transition font-bold"><i class="fas fa-sign-in-alt mr-1"></i> LOGIN</a>
                        <a href="{{ route('register') }}" class="hover:text-primary text-white transition font-bold"><i class="fas fa-user-plus mr-1"></i> SIGN UP</a>
                    @endauth
                @endif
            </div>
        </div>
    </div>
</footer>

{{-- Social / Contact Floating Widgets --}}
<x-social-contact-widgets />

{{-- Floating Ads (Desktop) --}}
<div class="hidden lg:block">
    @if(!empty(\App\Models\Setting::get('ad_placement_floating_left')))
        <div style="position:fixed;left:10px;top:50%;transform:translateY(-50%);z-index:40;max-width:160px;text-align:center;">
            <span class="text-[8px] text-gray-400 block uppercase mb-1 leading-none">Advertisement</span>
            <x-ad-slot placement="floating_left" />
        </div>
    @endif
    @if(!empty(\App\Models\Setting::get('ad_placement_floating_right')))
        <div style="position:fixed;right:10px;top:50%;transform:translateY(-50%);z-index:40;max-width:160px;text-align:center;">
            <span class="text-[8px] text-gray-400 block uppercase mb-1 leading-none">Advertisement</span>
            <x-ad-slot placement="floating_right" />
        </div>
    @endif
</div>

{{-- Global Background Scripts --}}
@if(\App\Models\Setting::get('enable_popunder') == '1')
    {!! \App\Models\Setting::get('ad_code_popunder', '') !!}
@endif
@if(\App\Models\Setting::get('enable_socialbar') == '1')
    {!! \App\Models\Setting::get('ad_code_socialbar', '') !!}
@endif

{{-- Pseudo-Cron --}}
<script>
    setTimeout(function() {
        fetch('{{ url("/system/pseudo-cron") }}').catch(function() {});
    }, 3000);
</script>

{{-- AdBlock Shield --}}
@if(\App\Models\Setting::get('adblock_detection_enabled') == '1')
<div id="adblock-bait-dom"
     class="ad-placement adsense ads-banner ads-box ad-unit ads-wrapper adsbygoogle ad-slot ad-container"
     style="position:absolute;left:-9999px;top:-9999px;height:1px;width:1px;opacity:0.001;"
     aria-hidden="true">
</div>
<div id="adblock-shield" style="display:none;" class="fixed inset-0 z-[9999999] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900/95 via-indigo-950/95 to-slate-900/95 backdrop-blur-2xl"></div>
    <div id="adblock-modal-card" class="relative z-10 bg-white/10 border border-white/20 shadow-2xl rounded-3xl max-w-md w-full p-8 text-center transform scale-90 transition-all duration-500 opacity-0" style="backdrop-filter:blur(24px);">
        <h2 class="text-2xl font-black text-white mt-4 mb-3">{{ \App\Models\Setting::get('adblock_message_title', 'Please Disable Your AdBlocker') }}</h2>
        <p class="text-slate-300 mb-6 text-sm">{{ \App\Models\Setting::get('adblock_message_body', "We've detected an active ad blocker. Our content is free because of ads — please whitelist our site.") }}</p>
        <button onclick="window.location.reload()" class="w-full py-3.5 px-8 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-2xl font-bold shadow-lg transition-all">
            {{ \App\Models\Setting::get('adblock_refresh_text', "I've disabled it — Reload") }}
        </button>
    </div>
</div>
<style>
    body.adblock-active-lock{overflow:hidden!important;position:fixed!important;width:100%!important;}
    .adblock-content-blurred{filter:blur(10px) grayscale(60%) brightness(0.7)!important;pointer-events:none!important;}
    #adblock-modal-card.modal-in{opacity:1!important;transform:scale(1)!important;}
</style>
<script>
(function(){
    'use strict';
    const DELAY={{ \App\Models\Setting::get('adblock_delay',1000) }};
    const BLUR={{ \App\Models\Setting::get('adblock_blur_enabled',1)==='1'?'true':'false' }};
    let active=false;
    function check(){
        const b=document.getElementById('adblock-bait-dom');
        if(!b)return activate();
        const s=window.getComputedStyle(b);
        if(b.offsetHeight===0||b.offsetWidth===0||s.display==='none'||s.visibility==='hidden')activate();
    }
    function activate(){
        if(active)return;active=true;
        document.body.classList.add('adblock-active-lock');
        if(BLUR){document.querySelectorAll('main,article,header,.site-header,section').forEach(el=>{if(!el.closest('#adblock-shield'))el.classList.add('adblock-content-blurred');});}
        const sh=document.getElementById('adblock-shield');
        const mc=document.getElementById('adblock-modal-card');
        if(sh){sh.style.display='flex';requestAnimationFrame(()=>requestAnimationFrame(()=>{if(mc)mc.classList.add('modal-in');}));}
    }
    setTimeout(check,DELAY);
    setInterval(()=>{if(!active)check();},5000);
})();
</script>
@endif

