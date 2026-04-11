<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Overview') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Message -->
            <div class="bg-white overflow-hidden shadow-sm flex items-center justify-between sm:rounded-lg border-l-4 border-indigo-500 p-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}! 👋</h3>
                    <p class="text-gray-500 mt-1">Here is what's happening globally across your publishing platform.</p>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <a href="{{ Auth::user()->role === 'admin' ? route('admin.posts.index') : route('posts.index') }}" class="px-6 py-2 bg-indigo-600 text-white rounded font-bold hover:bg-indigo-700 shadow transition text-sm">
                        Manage Posts
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Stat Card 1 -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="p-4 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L16.5 5.5M9 11l3 3L22 4"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">{{ Auth::user()->role === 'admin' ? 'Total Posts' : 'My Posts' }}</div>
                        <div class="text-3xl font-black text-gray-900">{{ number_format($stats['posts_count']) }}</div>
                    </div>
                </div>
                <!-- Stat Card 2 -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="p-4 bg-green-50 text-green-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">{{ Auth::user()->role === 'admin' ? 'Total Views' : 'My Views' }}</div>
                        <div class="text-3xl font-black text-gray-900">{{ number_format($stats['posts_views']) }}</div>
                    </div>
                </div>

                @if(Auth::user()->role === 'admin')
                    <!-- Stat Card 3 (Admin) -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="p-4 bg-blue-50 text-blue-600 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">Total Users</div>
                            <div class="text-3xl font-black text-gray-900">{{ number_format($stats['users_count']) }}</div>
                        </div>
                    </div>
                    <!-- Stat Card 4 (Admin) -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="p-4 bg-purple-50 text-purple-600 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">Categories</div>
                            <div class="text-3xl font-black text-gray-900">{{ number_format($stats['categories_count']) }}</div>
                        </div>
                    </div>

                    <!-- Live Analytics Card (Admin) -->
                    <a href="{{ route('admin.analytics.index') }}" class="bg-gradient-to-br from-indigo-500 to-purple-600 p-6 rounded-lg shadow-md flex items-center justify-between hover:shadow-lg transition">
                        <div>
                            <div class="text-sm font-bold uppercase tracking-wider text-indigo-100 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                Live Traffic Today
                            </div>
                            <div class="text-3xl font-black text-white mt-1">{{ number_format($stats['visits_today']) }}</div>
                            <div class="text-xs text-indigo-200 mt-2">Total all-time visits: {{ number_format($stats['total_visits']) }}</div>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-full text-white">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                    </a>
                @else
                    <!-- Stat Card 3 (Author) -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="p-4 bg-blue-50 text-blue-600 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">Approved</div>
                            <div class="text-3xl font-black text-gray-900">{{ number_format($stats['approved_posts']) }}</div>
                        </div>
                    </div>
                    <!-- Stat Card 4 (Author) -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center gap-4">
                        <div class="p-4 bg-orange-50 text-orange-600 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">Pending</div>
                            <div class="text-3xl font-black text-gray-900">{{ number_format($stats['pending_posts']) }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Recent Activity / Posts List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h4 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Recently Added Posts</h4>
                @if($recent_posts->isEmpty())
                    <p class="text-gray-500">No posts found on the platform yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($recent_posts as $p)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded font-bold uppercase">{{ $p->category->name ?? 'Uncategorized' }}</span>
                                <a href="{{ route('frontend.post', $p->slug) }}" target="_blank" class="text-gray-800 font-semibold text-lg hover:text-indigo-600 transition">{{ $p->title }}</a>
                            </div>
                            <div class="text-sm text-gray-400">
                                {{ $p->created_at->diffForHumans() }} &bull; {{ $p->views }} views
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

