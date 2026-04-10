<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'My Minimal Blog') }} | {{ \App\Models\Setting::get('site_tagline', 'Less is more.') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#111827');
        $font = \App\Models\Setting::get('typography', 'Inter');
        $logo = \App\Models\Setting::get('site_logo');
        $menu = \App\Models\Menu::with('items')->first();
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: '{{ $font }}', sans-serif; }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .hover\:text-primary:hover { color: var(--primary); }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased selection:bg-gray-200">

    @include('themes.components.header')

    @if(isset($isCategory) && $isCategory)
    <div class="bg-slate-50/80 border-b border-slate-200" style="background-color: var(--bg-category, #f8fafc); border-bottom: 1px solid var(--border-category, #e2e8f0)">
        <div class="max-w-7xl mx-auto px-4 py-12 md:py-16 text-center">
            <span class="text-[10px] font-black uppercase tracking-widest text-primary mb-3 block opacity-80">Category</span>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4" style="color: var(--text-main, #0f172a)">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-lg opacity-70 max-w-2xl mx-auto" style="color: var(--text-muted, #475569)">{{ $category->description }}</p>
            @endif
        </div>
    </div>
    @endif


    <header class="max-w-4xl mx-auto py-12 px-6">
        <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 border-b-4 border-primary inline-block mb-4">
            Hi, welcome.
        </h1>
        <p class="text-lg text-gray-500">{{ \App\Models\Setting::get('site_tagline', 'A beautiful, typography-first reading experience.') }}</p>
    </header>

    <main class="max-w-4xl mx-auto px-6 pb-20">
        
        @if(\App\Models\Setting::get('show_featured_section', '1') == '1' && $featuredPosts->count())
        <section class="mb-16">
            <h2 class="text-sm font-bold uppercase tracking-widest text-primary mb-8">Featured</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                @foreach($featuredPosts as $post)
                    <article class="group cursor-pointer">
                        <a href="{{ route('frontend.post', $post->slug) }}" class="block">
                            @if($post->featured_image)
                                <div class="aspect-video w-full overflow-hidden rounded-lg mb-4">
                                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-500">
                                </div>
                            @endif
                            <h3 class="text-2xl font-bold mb-2 group-hover:text-primary transition line-clamp-2">{{ $post->title }}</h3>
                            <p class="text-gray-600 mb-4 leading-relaxed">{{ Str::limit($post->summary ?? strip_tags($post->content), 120) }}</p>
                            <span class="text-xs font-mono text-gray-400 uppercase">{{ $post->created_at->format('M d, Y') }}</span>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
        @endif

        @if(\App\Models\Setting::get('show_latest_section', '1') == '1')
        <section>
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-8">Latest Articles</h2>
            <div class="space-y-12">
                @forelse($latestPosts as $post)
                    <article class="group border-b pb-12 flex flex-col md:flex-row gap-8">
                        <div class="flex-1">
                            <article class="block relative">
    <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
                                <h3 class="text-3xl font-bold mb-3 group-hover:underline decoration-4 underline-offset-4 text-primary line-clamp-2">{{ $post->title }}</h3>
                                <p class="text-gray-600 text-lg leading-relaxed mb-4">{{ Str::limit(strip_tags($post->content), 200) }}</p>
                                <div class="flex items-center text-sm font-mono text-gray-400 uppercase gap-4">
                                    <span>{{ $post->created_at->format('F d, Y') }}</span>
                                    <span>&bull;</span>
                                    <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><span>{{ $post->category->name ?? 'Uncategorized' }}</span></a>
                                </div>
                            </article>
                        </div>
                        @if($post->featured_image)
                            <div class="w-full md:w-1/3 aspect-video md:aspect-square overflow-hidden rounded-lg">
                                <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="object-cover w-full h-full group-hover:opacity-80 transition">
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="text-xl text-gray-400">No posts written yet.</p>
                @endforelse
            </div>
            <div class="mt-12">{{ $latestPosts->links() }}</div>
        </section>
        @endif
    </main>

    @include('themes.components.footer')
</body>
</html>
