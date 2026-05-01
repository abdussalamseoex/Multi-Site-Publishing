<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Auto News Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold">Generation History</h3>
                        <div class="flex items-center gap-4">
                            <form action="{{ route('admin.ai-writer.news.logs') }}" method="GET" class="flex items-center gap-2">
                                <select name="source_id" class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                                    <option value="">All Sources</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}" {{ request('source_id') == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('source_id'))
                                    <a href="{{ route('admin.ai-writer.news.logs') }}" class="text-xs text-red-500 hover:underline">Clear Filter</a>
                                @endif
                            </form>
                        </div>
                    </div>

                    @if($posts->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($posts as $post)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="font-medium text-gray-900">{{ $post->created_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $post->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $post->title }}</div>
                                            <div class="text-xs text-gray-500">{{ $post->category->name ?? 'Uncategorized' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $src = $sources->firstWhere('id', $post->auto_news_source_id);
                                            @endphp
                                            <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-xs font-bold border border-indigo-100">
                                                {{ $src->name ?? 'Unknown Source' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 uppercase">
                                                {{ $post->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('frontend.post', $post->slug) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded">View</a>
                                            <a href="{{ route('admin.posts.edit', $post->id) }}" class="text-amber-600 hover:text-amber-900 ml-2">Edit</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="text-center py-20 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No logs found</h3>
                            <p class="mt-1 text-sm text-gray-500">Wait for the next auto-fetch or trigger a manual fetch.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
