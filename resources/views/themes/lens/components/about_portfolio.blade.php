<div class="text-center pb-12 border-b border-gray-200 mb-12">
    <img src="{{ \App\Models\Setting::get('site_logo') ? url(\App\Models\Setting::get('site_logo')) : 'https://ui-avatars.com/api/?name=Lens&background=f3f4f6&color=111' }}" class="w-24 h-24 rounded-full mx-auto mb-6 object-cover bg-gray-50 p-1 border border-gray-200">
    <h3 class="text-xl font-medium text-gray-900 mb-2 tracking-wide">{{ \App\Models\Setting::get('site_title', 'Lens Studio') }}</h3>
    <p class="text-gray-500 text-sm font-light leading-relaxed mb-6">{{ \App\Models\Setting::get('site_tagline', 'Capturing light, shadows, and moments. A fine-art photography gallery.') }}</p>
    <a href="#" class="inline-block border-2 border-gray-900 text-gray-900 px-6 py-2 text-xs font-bold uppercase tracking-[0.2em] hover:bg-gray-900 hover:text-white transition">Get In Touch</a>
</div>
