<!DOCTYPE html>
<html lang="en">
<head>
    @include('themes.components.meta_tags')
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#6366f1');
        $siteName = \App\Models\Setting::get('site_name', 'Our Site');
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .text-primary { color: var(--primary); }
        .bg-primary { background-color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .hover-primary:hover { color: var(--primary); }

        /* Avatar ring animation */
        .avatar-ring {
            padding: 4px;
            background: linear-gradient(135deg, var(--primary), #a78bfa, #60a5fa);
            border-radius: 50%;
            display: inline-block;
        }
        /* Post card hover */
        .post-card { transition: all 0.3s ease; }
        .post-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    </style>
    <title>{{ $author->name }} — Author Profile | {{ $siteName }}</title>
    <meta name="description" content="Posts by {{ $author->name }} on {{ $siteName }}. {{ $author->bio ? Str::limit($author->bio, 155) : 'Browse all articles by this author.' }}">
    {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
<body class="antialiased min-h-screen">

    @include('themes.components.header')

    {{-- Author Hero Section --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-4 py-16 md:py-24">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-10">

                {{-- Avatar --}}
                <div class="shrink-0">
                    <div class="avatar-ring">
                        @if(!empty($author->avatar) && Storage::disk('public')->exists($author->avatar))
                            <img src="{{ asset('storage/' . $author->avatar) }}" alt="{{ $author->name }}"
                                 class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover block">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($author->name) }}&background={{ ltrim($primary, '#') }}&color=fff&size=200"
                                 alt="{{ $author->name }}"
                                 class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover block">
                        @endif
                    </div>
                </div>

                {{-- Author Info --}}
                <div class="text-center md:text-left flex-1">
                    <div class="text-xs font-bold text-primary uppercase tracking-widest mb-2">Author Profile</div>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-3" style="font-family: 'Playfair Display', serif;">
                        {{ $author->name }}
                    </h1>

                    @if($author->bio)
                        <p class="text-gray-500 text-lg leading-relaxed max-w-2xl mb-4">{{ $author->bio }}</p>
                    @else
                        <p class="text-gray-400 text-lg italic mb-4">No bio added yet.</p>
                    @endif

                    {{-- Social Links --}}
                    @if(!empty($author->website) || !empty($author->twitter) || !empty($author->facebook) || !empty($author->linkedin) || !empty($author->instagram))
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5 mb-6">
                            @if(!empty($author->website))
                                <a href="{{ $author->website }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-full transition">
                                    🌐 Website
                                </a>
                            @endif
                            @if(!empty($author->twitter))
                                <a href="{{ $author->twitter }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1 bg-sky-50 hover:bg-sky-100 text-sky-600 text-xs font-semibold rounded-full transition">
                                    𝕏 Twitter
                                </a>
                            @endif
                            @if(!empty($author->facebook))
                                <a href="{{ $author->facebook }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-semibold rounded-full transition">
                                    Facebook
                                </a>
                            @endif
                            @if(!empty($author->linkedin))
                                <a href="{{ $author->linkedin }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-semibold rounded-full transition">
                                    LinkedIn
                                </a>
                            @endif
                            @if(!empty($author->instagram))
                                <a href="{{ $author->instagram }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1 bg-pink-50 hover:bg-pink-100 text-pink-600 text-xs font-semibold rounded-full transition">
                                    Instagram
                                </a>
                            @endif
                        </div>
                    @endif

                    {{-- Stats Row --}}
                    <div class="flex flex-wrap justify-center md:justify-start gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-black text-primary">{{ $posts->total() }}</div>
                            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Published Posts</div>
                        </div>
                        <div class="w-px bg-gray-200 self-stretch hidden md:block"></div>
                        <div class="text-center">
                            <div class="text-3xl font-black text-gray-800">{{ $author->created_at->format('Y') }}</div>
                            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Member Since</div>
                        </div>
                        <div class="w-px bg-gray-200 self-stretch hidden md:block"></div>
                        <div class="text-center">
                            @php $totalViews = $posts->sum('views'); @endphp
                            <div class="text-3xl font-black text-gray-800">{{ number_format(\App\Models\Post::where('user_id', $author->id)->where('status','published')->sum('views')) }}</div>
                            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Total Views</div>
                        </div>
                        <div class="w-px bg-gray-200 self-stretch hidden md:block"></div>
                        <div class="text-center">
                            <div class="text-3xl font-black text-gray-800">{{ ucfirst($author->roles->first()->name ?? 'author') }}</div>
                            <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Role</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Posts Section --}}
    <div class="max-w-5xl mx-auto px-4 py-16">

        @if($posts->count() > 0)
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl font-bold text-gray-800">
                    All Posts by <span class="text-primary">{{ $author->name }}</span>
                </h2>
                <span class="text-sm text-gray-400 font-medium">{{ $posts->total() }} {{ Str::plural('article', $posts->total()) }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <a href="{{ route('frontend.post', $post->slug) }}" class="post-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 block group">
                        {{-- Featured Image --}}
                        @if($post->featured_image)
                            <div class="aspect-video w-full overflow-hidden bg-gray-100">
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}"
                                     alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </div>
                        @else
                            <div class="aspect-video w-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif

                        {{-- Content --}}
                        <div class="p-5">
                            @if($post->category)
                                <span class="text-[10px] font-bold uppercase tracking-widest text-primary mb-2 block">{{ $post->category->name }}</span>
                            @endif
                            <h3 class="font-bold text-lg text-gray-800 group-hover:text-primary transition leading-snug mb-2">
                                {{ $post->title }}
                            </h3>
                            @if($post->summary)
                                <p class="text-gray-500 text-sm line-clamp-2 mb-3">{{ $post->summary }}</p>
                            @endif
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span>{{ $post->created_at->format('M d, Y') }}</span>
                                <span>&bull;</span>
                                <span>{{ number_format($post->views) }} views</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($posts->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @endif

        @else
            <div class="text-center py-24">
                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-400 mb-2">No posts yet</h3>
                <p class="text-gray-300 text-sm">{{ $author->name }} hasn't published any articles yet.</p>
            </div>
        @endif
    </div>

    @include('themes.components.footer')
    {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
</body>
</html>
