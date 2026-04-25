<div class="rounded-2xl p-8 bg-gradient-to-br from-indigo-900/50 to-slate-900 border border-indigo-500/30 text-center relative overflow-hidden mb-10">
    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl"></div>
    <svg class="w-8 h-8 text-indigo-400 mx-auto mb-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
    <h3 class="text-xl font-tech font-bold mb-2 relative z-10">{{ $block['title'] ?? 'Join the Network' }}</h3>
    <p class="text-slate-400 text-sm mb-6 relative z-10">Receive encrypted weekly reports on SaaS and AI advancements.</p>
    <div class="flex flex-col gap-3 relative z-10">
        <input type="email" placeholder="Email Node" class="bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-center focus:border-indigo-500 focus:outline-none text-white">
        <button class="bg-indigo-600 hover:bg-indigo-500 text-white font-tech font-bold tracking-widest text-xs uppercase py-3 rounded-lg transition shadow-[0_0_15px_rgba(79,70,229,0.3)]">Subscribe</button>
    </div>
</div>
