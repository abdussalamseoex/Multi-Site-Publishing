@php
    $query = \App\Models\Post::where('status', 'published');
    if (!empty($block['category_id'])) {
        $query->where('category_id', $block['category_id']);
    }
    // Use paginate to enable numbered pagination
    $deepDivePosts = $query->latest()->paginate($block['limit'] ?? 4);
@endphp

@if($deepDivePosts->count() > 0)
<div class="mt-16 bg-[#0f172a] rounded-2xl overflow-hidden shadow-2xl p-8 lg:p-12 text-white border-t-4 border-primary relative mb-12">
    <h3 class="text-3xl font-gaming font-bold border-b-2 border-slate-700 pb-2 mb-8 uppercase text-white"><span class="border-b-4 border-primary pb-2.5">{{ $block['title'] ?? 'Deep Dive Blogs' }}</span></h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach($deepDivePosts as $index => $post)
        <article class="flex flex-col sm:flex-row gap-6 group items-center bg-slate-800/50 p-4 rounded-xl border border-slate-700/50 hover:bg-slate-800 transition relative">
            <a href="{{ route('frontend.post', $post->slug) }}" class="absolute inset-0 z-0"></a>
            <div class="w-full sm:w-40 h-32 shrink-0 rounded-lg overflow-hidden bg-slate-800 relative shadow-inner border border-slate-700">
                <a href="{{ isset($post->category) ? route('frontend.category', $post->category->slug) : '#' }}" class="absolute top-2 left-2 z-20 hover:opacity-80 transition"><span class="bg-blue-600 text-white px-2 py-0.5 text-[9px] font-black uppercase rounded shadow-lg">{{ $post->category->name ?? 'Blog' }}</span></a>
                @if($post->featured_image)
                    <img src="{{ Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image) }}" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-100 transition duration-500 group-hover:scale-105 z-10">
                @endif
            </div>
            <div>
                <h4 class="text-lg font-bold leading-tight mb-2 group-hover:text-blue-400 transition line-clamp-2">{{ $post->title }}</h4>
                <p class="text-slate-400 text-sm mb-3 line-clamp-2 leading-relaxed">{{ strip_tags($post->summary ?? $post->content) }}</p>
                <span class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">{{ $post->user->name ?? 'Writer' }} &bull; {{ $post->created_at->diffForHumans() }}</span>
            </div>
        </article>
        @endforeach
    </div>
    
    @if($deepDivePosts->hasPages())
    <div class="mt-10 pagination-wrapper relative z-20">
        {{ $deepDivePosts->links() }}
    </div>
    @endif
</div>
@endif
