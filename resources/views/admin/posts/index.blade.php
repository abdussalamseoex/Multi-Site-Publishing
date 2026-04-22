<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Posts') }}
            </h2>
            <a href="{{ route('admin.posts.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition">Write New Post</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                <form method="GET" action="{{ route('admin.posts.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Search Posts</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-1/6">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/6">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sort By</label>
                        <select name="sort" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="views_desc" {{ request('sort') == 'views_desc' ? 'selected' : '' }}>Most Viewed</option>
                        </select>
                    </div>
                    <div class="flex space-x-2 w-full md:w-auto">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white shadow-sm rounded-md text-sm font-medium hover:bg-indigo-700 transition">Filter</button>
                        <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-200 transition">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    
                    <form id="bulk-action-form" method="POST" action="{{ route('admin.posts.bulk-action') }}">
                        @csrf
                        <div class="mb-4 flex items-center bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-sm w-max gap-3">
                            <span class="text-xs font-bold text-gray-500 uppercase flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                With Selected:
                            </span>
                            <select name="action" required class="border-gray-300 rounded shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3 uppercase font-bold text-gray-600 bg-white">
                                <option value="">-- Choose --</option>
                                <option value="published">Publish</option>
                                <option value="pending">Mark Pending</option>
                                <option value="draft">Mark Draft</option>
                                <option value="rejected">Reject</option>
                                <option value="delete">Delete Forever</option>
                            </select>
                            <button type="submit" onclick="return confirm('Apply this bulk action to all selected posts?')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded shadow-sm text-sm font-bold transition-all">Apply Action</button>
                        </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left w-10">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($posts as $post)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="ids[]" value="{{ $post->id }}" class="post-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900 border-b border-transparent hover:border-black cursor-pointer inline-block">{{ Str::limit($post->title, 40) }}</div>
                                    <div class="text-xs text-gray-500">{{ $post->category->name ?? 'None' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $post->user->name ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($post->status == 'published') bg-green-100 text-green-800
                                        @elseif($post->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($post->status == 'rejected') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-600 @endif
                                    ">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $post->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-1">
                                    
                                    <form action="{{ route('admin.posts.status', $post->id) }}" method="POST" class="inline-block mr-1" onchange="this.submit()">
                                        @csrf
                                        <select name="status" class="text-xs border-gray-300 rounded shadow-sm py-1 pl-2 pr-6 focus:ring-indigo-500 focus:border-indigo-500 font-bold bg-gray-50 cursor-pointer 
                                            @if($post->status == 'published') text-green-700 
                                            @elseif($post->status == 'pending') text-yellow-600 
                                            @elseif($post->status == 'rejected') text-red-600 
                                            @else text-gray-600 @endif
                                        ">
                                            <option value="published" class="text-green-700 font-bold" {{ $post->status == 'published' ? 'selected' : '' }}>Published</option>
                                            <option value="pending" class="text-yellow-600 font-bold" {{ $post->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="draft" class="text-gray-600 font-bold" {{ $post->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="rejected" class="text-red-600 font-bold" {{ $post->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </form>

                                    <a href="{{ route('frontend.post', $post->slug) }}" target="_blank" class="inline-flex items-center px-2 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-100 focus:outline-none transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition">
                                        Edit
                                    </a>
                                    
                                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-700 focus:outline-none transition" onclick="if(confirm('Delete this post permanently?')) { this.closest('form').submit(); }">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </form>
                    
                    <div class="mt-4">{{ $posts->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.post-checkbox');

        if(selectAll && checkboxes.length > 0) {
            // Select All toggler
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
            });

            // Re-sync "Select All" checkbox when an individual checkbox changes
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if(!this.checked) {
                        selectAll.checked = false;
                    } else {
                        // Check if all are checked
                        const allChecked = Array.from(checkboxes).every(c => c.checked);
                        selectAll.checked = allChecked;
                    }
                });
            });
        }
    });
</script>
