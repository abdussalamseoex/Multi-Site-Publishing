<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Categories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            
            <div class="w-full md:w-1/3">
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4">Add New Category</h3>
                    <form action="{{ route('admin.categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white rounded py-2 text-sm font-bold shadow hover:bg-indigo-700">Create Category</button>
                    </form>
                </div>

                <!-- Bulk Import Segment -->
                <div class="bg-white shadow sm:rounded-lg p-6 mt-6" x-data="{ open: false }">
                    <h3 class="text-lg font-bold mb-2 flex items-center justify-between">
                        Quick Import Niches
                        <button @click="open = !open" type="button" class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded border border-indigo-100 hover:bg-indigo-100 transition">Toggle Form</button>
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">Instantly generate high-demand guest posting categories without typing.</p>
                    
                    <form action="{{ route('admin.categories.bulk_import') }}" method="POST" x-show="open" style="display: none;" class="mt-2">
                        @csrf
                        <div class="h-56 overflow-y-auto border border-gray-200 rounded p-3 mb-4 space-y-2 text-sm bg-gray-50">
                            @php
                            $niches = ['Technology & IT', 'Software Development', 'Health & Fitness', 'Business & Finance', 'Digital Marketing', 'Real Estate', 'Home Improvement', 'Travel & Tourism', 'Lifestyle & Culture', 'Fashion & Beauty', 'Education & Learning', 'Gaming & Esports', 'Cryptocurrency & Web3', 'Law & Legal', 'Automotive', 'Sports', 'Entertainment & Pop Culture', 'Pets & Animals', 'Food & Recipes'];
                            @endphp
                            @foreach($niches as $niche)
                            <label class="flex items-center gap-2 hover:bg-white p-1 rounded transition cursor-pointer">
                                <input type="checkbox" name="categories[]" value="{{ $niche }}" class="rounded text-indigo-600 border-gray-300 shadow-sm focus:ring-indigo-500">
                                <span class="text-gray-700">{{ $niche }}</span>
                            </label>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full bg-green-600 text-white rounded py-2 text-sm font-bold shadow hover:bg-green-700 transition" onclick="return confirm('Import the selected categories?')">Import Selected Categories</button>
                    </form>
                </div>
            </div>

            <div class="w-full md:w-2/3">
                @if (session('status'))
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                        <p class="text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                    <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-1/2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Search Categories</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Sort By</label>
                            <select name="sort" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="posts_count" {{ request('sort') == 'posts_count' ? 'selected' : '' }}>Most Posts</option>
                            </select>
                        </div>
                        <div class="flex space-x-2 w-full md:w-auto">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white shadow-sm rounded-md text-sm font-medium hover:bg-indigo-700 transition">Filter</button>
                            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-200 transition">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posts</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($categories as $cat)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $cat->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($cat->description, 50) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cat->posts_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('frontend.category', $cat->slug) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-bold">View</a>
                                        <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 ml-4 font-bold" onclick="return confirm('Delete this category?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

