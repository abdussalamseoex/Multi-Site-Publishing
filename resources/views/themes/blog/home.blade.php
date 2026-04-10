<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- BlogPost Standard Theme -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($category) ? $category->name . ' - ' : '' }}{{ \App\Models\Setting::get('site_title', 'My Standard Blog') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

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


    <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
        
        <main class="w-full md:w-2/3">
            <h2 class="text-2xl font-bold border-b border-gray-300 pb-2 mb-6">Latest Articles</h2>
            
            <div class="space-y-8">
                @foreach($latestPosts as $post)
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="hover:opacity-80 transition"><div class="text-xs font-bold text-indigo-500 uppercase mb-2">{{ $post->category->name ?? 'Uncategorized' }}</div></a>
                        <h3 class="text-2xl font-bold mb-2">
                            <a href="{{ route('frontend.post', $post->slug) }}" class="hover:text-indigo-600">{{ $post->title }}</a>
                        </h3>
                        <p class="text-gray-600 mb-4">{{ Str::limit(strip_tags($post->content), 150) }}</p>
                        <div class="text-sm text-gray-400">By {{ $post->user->name ?? 'Author' }} &bull; {{ $post->created_at->format('M d, Y') }}</div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $latestPosts->links() }}
            </div>
        </main>

        <aside class="w-full md:w-1/3">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 sticky top-8">
                <h3 class="font-bold text-lg border-b pb-2 mb-4">About Us</h3>
                <p class="text-sm text-gray-600 mb-6">Welcome to {{ \App\Models\Setting::get('site_title') }}. Here you will find the best articles and guest posts.</p>
                
                @if($featuredPosts->count())
                <h3 class="font-bold text-lg border-b pb-2 mb-4">Featured</h3>
                <ul class="space-y-3">
                    @foreach($featuredPosts as $post)
                        <li>
                            <a href="{{ route('frontend.post', $post->slug) }}" class="text-indigo-600 hover:underline text-sm font-medium">{{ $post->title }}</a>
                        </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </aside>

    </div>
    @include('themes.components.footer')
</body>
</html>
