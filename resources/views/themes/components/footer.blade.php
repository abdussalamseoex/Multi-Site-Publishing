@php
    $footerMenu = \App\Models\Menu::where('location', 'footer')->with('items')->first();
    $aboutText = \App\Models\Setting::get('site_description', 'We are a premier publishing platform dedicated to bringing you the best content from talented authors around the globe.');
    $siteTitle = \App\Models\Setting::get('site_title', 'Publish.');
    $logo = \App\Models\Setting::get('site_logo');
@endphp

<footer class="bg-white border-t border-gray-200 pt-16 pb-8 mt-16">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
        
        <!-- Brand & About -->
        <div class="space-y-4">
            @if($logo)
                <a href="{{ route('home') }}"><img src="{{ Str::startsWith($logo, 'http') ? $logo : url($logo) }}" alt="Logo" class="h-8 mb-6 opacity-80 hover:opacity-100 transition"></a>
            @else
                <a href="{{ route('home') }}" class="font-extrabold text-2xl tracking-tight text-primary block mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    {{ $siteTitle }}
                </a>
            @endif
            <p class="text-gray-500 text-sm leading-relaxed">{{ Str::limit($aboutText, 150) }}</p>
            
            <div class="flex items-center gap-4 pt-2">
                <!-- Social Icons Placeholder -->
                <a href="#" class="text-gray-400 hover:text-primary transition">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                </a>
                <a href="#" class="text-gray-400 hover:text-primary transition">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                </a>
            </div>
        </div>

        <!-- Legal / Footer Menu -->
        <div>
            <h3 class="font-bold text-gray-900 mb-6 uppercase tracking-wider text-sm">Legal & Links</h3>
            <ul class="space-y-3 text-sm text-gray-500 font-medium">
                @if($footerMenu && $footerMenu->items)
                    @foreach($footerMenu->items->sortBy('order') as $item)
                        <li><a href="{{ $item->url }}" class="hover:text-primary transition-colors flex items-center gap-2"><span class="text-primary hover-opacity-80">&#8250;</span> {{ $item->title }}</a></li>
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
                @foreach(\App\Models\Category::take(5)->get() as $cat)
                    <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2"><span class="text-primary">&#8250;</span> {{ $cat->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <!-- AdSense / Newsletter Space -->
        @php $adContent = \App\Models\Setting::get('ad_content_code'); @endphp
        @if($adContent)
            <div class="flex flex-col justify-center items-center text-center overflow-hidden w-full">
                {!! $adContent !!}
            </div>
        @else
            <div class="bg-gray-50 p-6 rounded-xl border border-dashed border-gray-300 flex flex-col justify-center items-center text-center">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Advertisement
                </span>
                <!-- ADD GOOGLE ADSENSE CODE HERE -->
                <div class="w-full h-32 bg-gray-200/60 rounded flex items-center justify-center">
                    <span class="text-sm text-gray-500 font-mono">300x250 Ad Space</span>
                </div>
                <!-- END ADSENSE CODE -->
            </div>
        @endif

    </div>

    <div class="max-w-7xl mx-auto px-6 mt-16 pt-8 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-gray-400 font-medium">
        <p>&copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.</p>
        <p>Built with ❤️ Modern UI</p>
    </div>
</footer>

