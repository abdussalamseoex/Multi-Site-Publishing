<div class="widget mb-10">
    <h3 class="section-title">{{ $block['title'] ?? 'Stay Connected' }}</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
        <a href="{{ \App\Models\Setting::get('social_facebook', '#') }}" class="bg-[#3b5998] text-white flex flex-col items-center justify-center p-3 hover:opacity-90 transition">
            <span class="font-bold text-sm mb-1"><i class="fab fa-facebook-f"></i></span>
            <span class="text-[9px] uppercase tracking-wider opacity-80">Fans</span>
        </a>
        <a href="{{ \App\Models\Setting::get('social_twitter', '#') }}" class="bg-[#1da1f2] text-white flex flex-col items-center justify-center p-3 hover:opacity-90 transition">
            <span class="font-bold text-sm mb-1"><i class="fab fa-twitter"></i></span>
            <span class="text-[9px] uppercase tracking-wider opacity-80">Followers</span>
        </a>
        <a href="{{ \App\Models\Setting::get('social_youtube', '#') }}" class="bg-[#cd201f] text-white flex flex-col items-center justify-center p-3 hover:opacity-90 transition">
            <span class="font-bold text-sm mb-1"><i class="fab fa-youtube"></i></span>
            <span class="text-[9px] uppercase tracking-wider opacity-80">Subs</span>
        </a>
    </div>
</div>
