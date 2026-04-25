<div class="bg-gray-900 rounded-xl shadow-lg p-8 mb-8 text-center text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+CjxjaXJjbGUgY3g9IjIiIGN5PSIyIiByPSIyIiBmaWxsPSIjZmZmIi8+Cjwvc3ZnPg==')]"></div>
    <div class="relative z-10">
        <svg class="w-10 h-10 mx-auto mb-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        <h3 class="text-xl font-bold mb-2">{{ $block['title'] ?? 'Subscribe Now' }}</h3>
        <p class="text-gray-400 text-sm mb-6">Get the latest articles delivered right to your inbox.</p>
        <form action="#" class="space-y-3">
            <input type="email" placeholder="Your email address" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
            <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-lg transition text-sm">Subscribe</button>
        </form>
    </div>
</div>
