<footer class="bg-dark text-gray-400 mt-12 py-12 border-t-[10px] border-primary">
    <div class="max-w-[1200px] mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- About -->
        <div>
            <a href="{{ url('/') }}" class="inline-block mb-4">
                @if(\App\Models\Setting::get('site_logo'))
                    <img src="{{ url(\App\Models\Setting::get('site_logo')) }}" alt="Logo" class="h-10">
                @else
                    <span class="text-3xl font-black tracking-tight text-white uppercase">{{ \App\Models\Setting::get('site_title', 'GOOD') }}</span>
                @endif
            </a>
            <p class="text-sm mb-4 leading-relaxed">{{ \App\Models\Setting::get('site_tagline', 'The Ultimate News Experience') }} - bringing you the latest updates around the clock.</p>
            <p class="text-xs text-gray-500">Contact us: info@example.com</p>
        </div>

        <!-- Latest -->
        <div>
            <h4 class="text-white font-bold uppercase mb-4 tracking-wide text-sm">Most Popular</h4>
            @php
                $footerPosts = \App\Models\Post::where('status', 'published')->orderBy('views', 'desc')->take(3)->get();
            @endphp
            <div class="space-y-4">
                @foreach($footerPosts as $fp)
                <div class="flex gap-3">
                    @if($fp->featured_image)
                    <img src="{{ Str::startsWith($fp->featured_image, 'http') ? $fp->featured_image : url($fp->featured_image) }}" class="w-16 h-16 object-cover rounded">
                    @endif
                    <div>
                        <a href="{{ route('frontend.post', $fp->slug) }}" class="text-gray-300 font-semibold text-sm hover:text-primary transition line-clamp-2">{{ $fp->title }}</a>
                        <span class="text-[10px] text-gray-500 uppercase">{{ $fp->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Social -->
        <div>
            <h4 class="text-white font-bold uppercase mb-4 tracking-wide text-sm">Follow Us</h4>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ \App\Models\Setting::get('social_facebook', '#') }}" class="bg-gray-800 hover:bg-[#3b5998] transition flex items-center p-2 rounded text-xs text-white group"><i class="fab fa-facebook-f w-6 text-center group-hover:text-white"></i> Facebook</a>
                <a href="{{ \App\Models\Setting::get('social_twitter', '#') }}" class="bg-gray-800 hover:bg-[#1da1f2] transition flex items-center p-2 rounded text-xs text-white group"><i class="fab fa-twitter w-6 text-center group-hover:text-white"></i> Twitter</a>
                <a href="{{ \App\Models\Setting::get('social_instagram', '#') }}" class="bg-gray-800 hover:bg-[#e1306c] transition flex items-center p-2 rounded text-xs text-white group"><i class="fab fa-instagram w-6 text-center group-hover:text-white"></i> Instagram</a>
                <a href="{{ \App\Models\Setting::get('social_youtube', '#') }}" class="bg-gray-800 hover:bg-[#ff0000] transition flex items-center p-2 rounded text-xs text-white group"><i class="fab fa-youtube w-6 text-center group-hover:text-white"></i> YouTube</a>
            </div>
        </div>

    </div>

    <div class="max-w-[1200px] mx-auto px-4 mt-8 pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
        <div>
            {!! \App\Models\Setting::get('footer_copyright_text', '&copy; ' . date('Y') . ' GOOD. All rights reserved.') !!}
        </div>
        <div class="mt-4 md:mt-0 flex space-x-4">
            <a href="#" class="hover:text-white transition">Privacy Policy</a>
            <a href="#" class="hover:text-white transition">Terms of Service</a>
            <a href="#" class="hover:text-white transition">Contact</a>
        </div>
    </div>
</footer>
