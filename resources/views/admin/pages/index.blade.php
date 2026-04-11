<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Static Pages') }}
            </h2>
            <a href="{{ route('admin.pages.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm font-bold shadow hover:bg-indigo-700">
                + Create New Page
            </a>
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
                <form method="GET" action="{{ route('admin.pages.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Search Pages</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="w-full md:w-1/3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sort By</label>
                        <select name="sort" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        </select>
                    </div>
                    <div class="flex space-x-2 w-full md:w-auto">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white shadow-sm rounded-md text-sm font-medium hover:bg-indigo-700 transition">Filter</button>
                        <a href="{{ route('admin.pages.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-200 transition">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Page Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL Slug</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pages as $page)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $page->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">/{{ $page->slug }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $page->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('admin.pages.edit', $page->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">Edit</a>
                                    <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 ml-4 font-bold" onclick="return confirm('Delete this page permanently?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($pages->isEmpty())
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No pages created yet.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $pages->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

