<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 text-white flex flex-col shadow-xl transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0 lg:flex">
    <div class="h-16 flex items-center px-6 border-b border-gray-800 bg-black">
        <a href="{{ route('dashboard') }}" class="font-black text-xl tracking-widest text-white uppercase">{{ \App\Models\Setting::get('site_title', 'CMS') }}</a>
    </div>

    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <!-- User Points Display -->
        @if(auth()->user()->role !== 'admin')
        <div class="mb-6 p-3 bg-gray-800 rounded-lg border border-gray-700 shadow-inner">
            <p class="text-xs text-gray-400 font-semibold mb-1 uppercase tracking-wider">Account Balance</p>
            <div class="flex items-end justify-between">
                <span class="text-2xl font-black text-indigo-400">{{ auth()->user()->points }} <span class="text-sm font-normal text-gray-400">Pts</span></span>
            </div>
            <a href="{{ route('user.topup') }}" class="mt-3 block w-full py-1.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold text-center rounded transition">Top-up Points</a>
        </div>
        @endif

        <p class="px-3 text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 mt-2">Overview</p>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.analytics.index') }}" class="{{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            Live Traffic
        </a>
        @endif
        <a href="{{ url('/') }}" target="_blank" class="text-gray-300 hover:bg-gray-800 hover:text-white flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            View Live Site
        </a>

        <p class="px-3 text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 mt-6">Content</p>
        @php
            $postIndexRoute = auth()->user()->role === 'admin' ? route('admin.posts.index') : route('posts.index');
            $postCreateRoute = auth()->user()->role === 'admin' ? route('admin.posts.create') : route('posts.create');
        @endphp

        <a href="{{ $postIndexRoute }}" class="{{ request()->routeIs('*.posts.index') && request('status') !== 'pending' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L16.5 5.5M9 11l3 3L22 4"></path></svg>
            All Posts
        </a>

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.posts.index', ['status' => 'pending']) }}" class="{{ request()->routeIs('admin.posts.index') && request('status') === 'pending' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Pending Edits
        </a>
        @endif

        <a href="{{ $postCreateRoute }}" class="{{ request()->routeIs('*.posts.create') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Write Post
        </a>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            Categories
        </a>
        @endif

        @if(auth()->user()->role === 'admin')
        <p class="px-3 text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 mt-6">AI Automation</p>
        <a href="{{ route('admin.ai-writer.index') }}" class="{{ request()->routeIs('admin.ai-writer.index') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            AI Bulk Writer
        </a>
        <a href="{{ route('admin.ai-writer.news') }}" class="{{ request()->routeIs('admin.ai-writer.news') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L16.5 5.5M9 11l3 3L22 4"></path></svg>
            Auto News Fetcher
        </a>
        <a href="{{ route('admin.ai-writer.news.logs') }}" class="{{ request()->routeIs('admin.ai-writer.news.logs') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            News Logs
        </a>
        <a href="{{ route('admin.ai-writer.settings') }}" class="{{ request()->routeIs('admin.ai-writer.settings') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            AI Settings
        </a>

        <p class="px-3 text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 mt-6">Design & Pages</p>
        <a href="{{ route('admin.theme.options') }}" class="{{ request()->routeIs('admin.theme.options*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
            Theme Builder
        </a>
        <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Static Pages
        </a>
        <a href="{{ route('admin.menus.index') }}" class="{{ request()->routeIs('admin.menus.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" border="0" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            Menu Builder
        </a>

        <p class="px-3 text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 mt-6">System</p>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Users & Roles
        </a>
        <a href="{{ route('admin.topup.requests') }}" class="{{ request()->routeIs('admin.topup.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Point Requests
        </a>
        <a href="{{ route('admin.settings.limits') }}" class="{{ request()->routeIs('admin.settings.limits') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            Limits & Pricing
        </a>
        <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.index') || request()->routeIs('admin.settings.store') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Settings
        </a>
        <a href="{{ route('admin.seo.index') }}" class="{{ request()->routeIs('admin.seo.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
            SEO
        </a>
        <a href="{{ route('admin.settings.social') }}" class="{{ request()->routeIs('admin.settings.social') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            Social & Contact
        </a>
        <a href="{{ route('admin.settings.ads') }}" class="{{ request()->routeIs('admin.settings.ads') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Ads & Monetization
        </a>
        <a href="{{ route('admin.settings.adblock') }}" class="{{ request()->routeIs('admin.settings.adblock') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            AdBlock Settings
        </a>
        <a href="{{ route('admin.update.index') }}" class="{{ request()->routeIs('admin.update.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-1 border border-dashed border-gray-700">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            System Update
        </a>
        <a href="{{ route('admin.import.wordpress.index') }}" class="{{ request()->routeIs('admin.import.wordpress.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} flex items-center px-3 py-2 text-sm font-medium rounded-md transition transition-colors mt-2 bg-blue-900/40">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            WP Importer
        </a>
        @endif
    </div>

    <!-- Bottom Profile Area -->
    <div class="p-4 border-t border-gray-800 bg-gray-900 flex items-center justify-between">
        <div class="flex items-center">
            <div class="h-8 w-8 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-sm">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-400 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </div>
</aside>

