@php
    $footerMenu = \App\Models\Menu::where('location', 'footer')->with('items')->first();
    $footerCategoriesMenu = \App\Models\Menu::where('location', 'footer_categories')->with('items')->first();
    $aboutText = \App\Models\Setting::get('footer_description', \App\Models\Setting::get('site_tagline', 'The Ultimate News Experience bringing you the latest updates around the clock.'));
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

    <!-- Pseudo-Cron Trigger (WP-Cron Alternative) -->
    <script>
        setTimeout(function() {
            fetch('{{ url("/system/pseudo-cron") }}').catch(function() {});
        }, 3000); // Trigger after 3 seconds to not delay page load
    </script>

    <!-- ============================================================ -->
    <!-- ADVANCED ADBLOCK SHIELD v3 - Multi-Layer Scoring System      -->
    <!-- ============================================================ -->
    @if(\App\Models\Setting::get('adblock_detection_enabled') == '1')

    {{-- Layer 1: Multiple DOM bait elements with different ad-network class names --}}
    <div id="adblock-bait-dom" 
         class="ad-placement adsense ads-banner ads-box ad-unit ads-wrapper adsbygoogle ad-slot ad-container"
         style="position:absolute;left:-9999px;top:-9999px;height:1px;width:1px;opacity:0.001;"
         aria-hidden="true">
    </div>

    {{-- The Shield Overlay --}}
    <div id="adblock-shield" 
         style="display:none;" 
         class="fixed inset-0 z-[9999999] flex items-center justify-center p-4"
         aria-modal="true" role="alertdialog">
        
        {{-- Animated background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/95 via-indigo-950/95 to-slate-900/95 backdrop-blur-2xl"></div>
        
        {{-- Animated orbs --}}
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-red-600/20 rounded-full blur-3xl animate-pulse" style="animation-delay:1s;"></div>
        
        {{-- Modal card --}}
        <div id="adblock-modal-card"
             class="relative z-10 bg-white/10 border border-white/20 shadow-2xl rounded-3xl max-w-md w-full p-8 text-center transform scale-90 transition-all duration-500 opacity-0"
             style="backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">

            {{-- Pulse ring icon --}}
            <div class="relative inline-flex mx-auto mb-6">
                <span class="absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-40 animate-ping"></span>
                <div class="relative w-20 h-20 bg-gradient-to-br from-red-500 to-red-700 rounded-2xl flex items-center justify-center shadow-lg shadow-red-500/30 rotate-3">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>

            <div class="mb-1 inline-flex items-center gap-1.5 bg-red-500/20 border border-red-500/30 text-red-300 text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse inline-block"></span>
                AD BLOCKER ACTIVE
            </div>

            <h2 class="text-2xl font-black text-white mt-4 mb-3 tracking-tight">
                {{ \App\Models\Setting::get('adblock_message_title', 'Please Disable Your AdBlocker') }}
            </h2>
            <p class="text-slate-300 mb-6 leading-relaxed text-sm">
                {{ \App\Models\Setting::get('adblock_message_body', "We've detected an active ad blocker. Our content is free because of ads — please whitelist our site to continue reading.") }}
            </p>

            {{-- How to steps --}}
            <div class="text-left bg-white/5 border border-white/10 rounded-2xl p-4 mb-6 space-y-2.5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">How to whitelist:</p>
                <div class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                    <p class="text-slate-300 text-sm">Click the AdBlock icon in your browser toolbar.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                    <p class="text-slate-300 text-sm">Select <strong class="text-white">"Don't run on this site"</strong> or <strong class="text-white">"Whitelist"</strong>.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                    <p class="text-slate-300 text-sm">Click the button below to reload.</p>
                </div>
            </div>

            <button id="adblock-refresh-btn"
                    onclick="window.location.reload()" 
                    class="w-full py-3.5 px-8 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white rounded-2xl font-bold shadow-lg shadow-indigo-500/30 transition-all duration-200 active:scale-95 text-base">
                {{ \App\Models\Setting::get('adblock_refresh_text', "I've disabled it — Reload") }}
            </button>
            <p class="text-xs text-slate-500 mt-4 uppercase tracking-widest font-semibold">Thank you for supporting us ❤️</p>
        </div>
    </div>

    <style>
        /* Blur body content behind shield */
        body.adblock-active-lock {
            overflow: hidden !important;
            position: fixed !important;
            width: 100% !important;
        }
        /* Blurred content layer */
        .adblock-content-blurred {
            filter: blur(10px) grayscale(60%) brightness(0.7) !important;
            pointer-events: none !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            transition: filter 0.4s ease !important;
        }
        /* Shield fade-in animation */
        #adblock-shield.shield-visible {
            display: flex !important;
        }
        #adblock-modal-card.modal-in {
            opacity: 1 !important;
            transform: scale(1) !important;
        }
    </style>

    <script>
    /* ================================================================
       ADVANCED ADBLOCK SHIELD v3
       4-Layer Detection | MutationObserver | Scoring System | Anti-Bypass
       ================================================================ */
    (function() {
        'use strict';

        // --- Config from Admin Settings ---
        const DETECTION_DELAY = {{ \App\Models\Setting::get('adblock_delay', 1000) }};
        const BLUR_ENABLED    = {{ \App\Models\Setting::get('adblock_blur_enabled', 1) == '1' ? 'true' : 'false' }};
        const RECHECK_INTERVAL = 4000; // ms between periodic checks
        const SCORE_THRESHOLD  = 1;    // Trigger if at least 1 layer fires

        let isShieldActive = false;
        let mutationObserver = null;

        // ---- LAYER 1: DOM Bait Element Check ----
        function checkLayer1_BaitDOM() {
            return new Promise(resolve => {
                const bait = document.getElementById('adblock-bait-dom');
                if (!bait) { resolve({ layer: 1, blocked: true, reason: 'bait-missing' }); return; }

                const st = window.getComputedStyle(bait);
                const blocked =
                    bait.offsetHeight === 0 ||
                    bait.offsetWidth  === 0 ||
                    st.display        === 'none' ||
                    st.visibility     === 'hidden' ||
                    parseFloat(st.opacity) < 0.01;

                resolve({ layer: 1, blocked, reason: 'dom-style' });
            });
        }

        // ---- LAYER 2: Bait Script Load Check (/ads.js) ----
        function checkLayer2_BaitScript() {
            return new Promise(resolve => {
                // uBlock Origin, Adblock Plus, Brave Shields all block files named "ads.js"
                const s = document.createElement('script');
                const done = (blocked) => {
                    resolve({ layer: 2, blocked, reason: 'script-load' });
                    try { document.head.removeChild(s); } catch(e) {}
                };
                s.src = '/ads.js?t=' + Date.now();
                s.onload  = () => done(typeof window.__adblock_bait_loaded === 'undefined');
                s.onerror = () => done(true);
                // Safety timeout
                const timer = setTimeout(() => done(true), 2500);
                s.onload = () => { clearTimeout(timer); done(typeof window.__adblock_bait_loaded === 'undefined'); };
                document.head.appendChild(s);
            });
        }

        // ---- LAYER 3: Bait CSS Link Load Check (/css/adsense.css) ----
        function checkLayer3_BaitCSS() {
            return new Promise(resolve => {
                // Most blockers also block CSS files matching ad network names
                const link = document.createElement('link');
                link.rel  = 'stylesheet';
                link.href = '/css/adsense.css?t=' + Date.now();
                const done = (blocked) => {
                    resolve({ layer: 3, blocked, reason: 'css-load' });
                    try { document.head.removeChild(link); } catch(e) {}
                };
                const timer = setTimeout(() => done(true), 2500);
                link.onload  = () => { clearTimeout(timer); done(false); };
                link.onerror = () => { clearTimeout(timer); done(true); };
                document.head.appendChild(link);
            });
        }

        // ---- LAYER 4: Fetch Request to Known Ad Network URL ----
        function checkLayer4_FetchProbe() {
            return new Promise(resolve => {
                // Ad blockers (uBlock, Adblock Plus) block requests to ad network domains
                fetch('https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', {
                    method: 'HEAD',
                    mode:   'no-cors',
                    cache:  'no-store'
                })
                .then(() => resolve({ layer: 4, blocked: false, reason: 'fetch-ok' }))
                .catch(() => resolve({ layer: 4, blocked: true,  reason: 'fetch-failed' }));
            });
        }

        // ---- MutationObserver: Detect DOM Tampering by Blockers ----
        function setupMutationGuard() {
            const body = document.body;
            mutationObserver = new MutationObserver((mutations) => {
                if (isShieldActive) return;
                for (const mutation of mutations) {
                    // Watch for bait element being hidden or removed
                    if (mutation.type === 'attributes') {
                        const t = mutation.target;
                        if (t && t.id === 'adblock-bait-dom') {
                            const st = window.getComputedStyle(t);
                            if (st.display === 'none' || st.visibility === 'hidden') {
                                activateShield('mutation-attr');
                                return;
                            }
                        }
                    }
                    if (mutation.type === 'childList') {
                        mutation.removedNodes.forEach(node => {
                            if (node.nodeType === 1 && node.id === 'adblock-bait-dom') {
                                activateShield('mutation-remove');
                            }
                        });
                    }
                }
            });

            const bait = document.getElementById('adblock-bait-dom');
            if (bait) {
                mutationObserver.observe(bait, { attributes: true, attributeFilter: ['style', 'class'] });
            }
            mutationObserver.observe(body, { childList: true, subtree: true });
        }

        // ---- Run All Layers and Score ----
        async function runDetection() {
            if (isShieldActive) return;

            try {
                // Run all layers concurrently for speed
                const results = await Promise.all([
                    checkLayer1_BaitDOM(),
                    checkLayer2_BaitScript(),
                    checkLayer3_BaitCSS(),
                    checkLayer4_FetchProbe(),
                ]);

                let score = 0;
                const triggers = [];
                results.forEach(r => {
                    if (r.blocked) {
                        score++;
                        triggers.push('L' + r.layer + ':' + r.reason);
                    }
                });

                if (score >= SCORE_THRESHOLD) {
                    activateShield(triggers.join(','));
                }
            } catch(e) {
                // If detection itself errors, run a simple DOM check as fallback
                checkLayer1_BaitDOM().then(r => { if (r.blocked) activateShield('fallback'); });
            }
        }

        // ---- Activate the Shield UI ----
        function activateShield(reason) {
            if (isShieldActive) return;
            isShieldActive = true;

            // Hard-lock body scroll
            document.body.classList.add('adblock-active-lock');

            // Blur background content
            if (BLUR_ENABLED) {
                const selectors = ['main', 'article', 'header', '.site-header', '.site-nav', 
                                   '.max-w-7xl', '#content', 'section', 'aside', '.hero'];
                selectors.forEach(sel => {
                    document.querySelectorAll(sel).forEach(el => {
                        // Don't blur the shield itself
                        if (!el.closest('#adblock-shield')) {
                            el.classList.add('adblock-content-blurred');
                        }
                    });
                });
            }

            // Show shield with animation
            const shield = document.getElementById('adblock-shield');
            const card   = document.getElementById('adblock-modal-card');
            if (shield) {
                shield.style.display = 'flex';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        if (card) card.classList.add('modal-in');
                    });
                });
            }
        }

        // ---- Init ----
        function init() {
            setupMutationGuard();

            // Initial check after delay
            setTimeout(runDetection, DETECTION_DELAY);

            // Periodic recheck (in case user installs blocker mid-session)
            setInterval(() => {
                if (!isShieldActive) runDetection();
            }, RECHECK_INTERVAL);

            // Recheck on tab return (user may have changed settings)
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && !isShieldActive) {
                    setTimeout(runDetection, 600);
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

    })();
    </script>
    @endif
</footer>

