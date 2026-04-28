<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Auto News Fetcher') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Add New Source -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="font-bold text-lg mb-4">Add New News Source (RSS/URL)</h3>
                    
                    <form action="{{ route('admin.ai-writer.news.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <!-- Preset Options -->
                        <div class="mb-4 bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <label class="block font-medium text-sm text-blue-800 mb-2">Or Choose from Top Global Sources (Auto-fill):</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <select id="preset_source" class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <option value="">-- Select a News Site --</option>
                                        <option value="bbc">BBC News</option>
                                        <option value="cnn">CNN</option>
                                        <option value="nyt">New York Times</option>
                                        <option value="guardian">The Guardian</option>
                                        <option value="aljazeera">Al Jazeera</option>
                                    </select>
                                </div>
                                <div>
                                    <select id="preset_category" class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" disabled>
                                        <option value="">-- First Select a Site --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Source Name</label>
                                <input type="text" name="name" id="source_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required placeholder="e.g. BBC Technology">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">RSS Feed URL or Target URL</label>
                                <input type="url" name="source_url" id="source_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required placeholder="https://feeds.bbci.co.uk/news/technology/rss.xml">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Target Category</label>
                                <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Posts Per Run</label>
                                <input type="number" name="posts_per_run" value="5" min="1" max="20" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Fetch Interval (Hours)</label>
                                <input type="number" name="fetch_interval_hours" value="24" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded border">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Featured Image</label>
                                <select name="featured_image_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="none">Original/None</option>
                                    <option value="pexels">Free (Pexels)</option>
                                    <option value="unsplash">Free (Unsplash)</option>
                                    <option value="dalle">AI Generated (DALL-E)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">In-Content Images Count</label>
                                <input type="number" name="in_content_images_count" value="1" min="0" max="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">In-Content Image Source</label>
                                <select name="in_content_image_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="none">None</option>
                                    <option value="pexels">Free (Pexels)</option>
                                    <option value="unsplash">Free (Unsplash)</option>
                                    <option value="dalle">AI Generated (DALL-E)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active (Cron Job will process this source)
                            </label>
                        </div>

                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded shadow hover:bg-indigo-700">Add Source</button>
                    </form>
                </div>
            </div>

            <!-- Existing Sources -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="font-bold text-lg mb-4">Active Sources</h3>
                    
                    @if($sources->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Run</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($sources as $source)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $source->name }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($source->source_url, 40) }}</div>
                                            @if($source->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $source->category ? $source->category->name : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $source->posts_per_run }} posts / {{ $source->fetch_interval_hours }}h
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $source->last_run_at ? $source->last_run_at->diffForHumans() : 'Never' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <form action="{{ route('admin.ai-writer.news.fetch', $source->id) }}" method="POST" onsubmit="return confirm('Trigger manual fetch for this source now? This might take a few minutes.');">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-2 py-1 rounded">Fetch Now</button>
                                                </form>
                                                <form action="{{ route('admin.ai-writer.news.destroy', $source->id) }}" method="POST" onsubmit="return confirm('Delete this source?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 px-2 py-1">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No auto news sources added yet.</p>
                    @endif
                </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded text-sm text-gray-600">
                <p><strong>Cron Job Setup:</strong> To make auto news fetching work, you need to set up a Cron Job on your server (cPanel/Plesk) to run the Laravel schedule every hour:</p>
                <code class="block bg-gray-800 text-white p-2 mt-2 rounded">
                    * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
                </code>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const presetSource = document.getElementById('preset_source');
        const presetCategory = document.getElementById('preset_category');
        const sourceName = document.getElementById('source_name');
        const sourceUrl = document.getElementById('source_url');

        const sourcesData = {
            'bbc': {
                name: 'BBC News',
                categories: {
                    'World': 'http://feeds.bbci.co.uk/news/world/rss.xml',
                    'Technology': 'http://feeds.bbci.co.uk/news/technology/rss.xml',
                    'Business': 'http://feeds.bbci.co.uk/news/business/rss.xml',
                    'Health': 'http://feeds.bbci.co.uk/news/health/rss.xml',
                    'Entertainment': 'http://feeds.bbci.co.uk/news/entertainment_and_arts/rss.xml',
                    'Sports': 'http://feeds.bbci.co.uk/sport/rss.xml'
                }
            },
            'cnn': {
                name: 'CNN',
                categories: {
                    'Top Stories': 'http://rss.cnn.com/rss/edition.rss',
                    'World': 'http://rss.cnn.com/rss/edition_world.rss',
                    'Technology': 'http://rss.cnn.com/rss/edition_technology.rss',
                    'Business': 'http://rss.cnn.com/rss/money_news_international.rss',
                    'Entertainment': 'http://rss.cnn.com/rss/edition_entertainment.rss'
                }
            },
            'nyt': {
                name: 'New York Times',
                categories: {
                    'World': 'https://rss.nytimes.com/services/xml/rss/nyt/World.xml',
                    'Technology': 'https://rss.nytimes.com/services/xml/rss/nyt/Technology.xml',
                    'Business': 'https://rss.nytimes.com/services/xml/rss/nyt/Business.xml',
                    'Sports': 'https://rss.nytimes.com/services/xml/rss/nyt/Sports.xml',
                    'Health': 'https://rss.nytimes.com/services/xml/rss/nyt/Health.xml'
                }
            },
            'guardian': {
                name: 'The Guardian',
                categories: {
                    'World': 'https://www.theguardian.com/world/rss',
                    'Technology': 'https://www.theguardian.com/technology/rss',
                    'Business': 'https://www.theguardian.com/business/rss',
                    'Sports': 'https://www.theguardian.com/sport/rss',
                    'Culture': 'https://www.theguardian.com/culture/rss'
                }
            },
            'aljazeera': {
                name: 'Al Jazeera',
                categories: {
                    'Top Stories': 'https://www.aljazeera.com/xml/rss/all.xml'
                }
            }
        };

        if (presetSource && presetCategory) {
            presetSource.addEventListener('change', function() {
                const siteId = this.value;
                presetCategory.innerHTML = '<option value="">-- Select a Category --</option>';
                
                if (siteId && sourcesData[siteId]) {
                    presetCategory.disabled = false;
                    const cats = sourcesData[siteId].categories;
                    for (const [catName, feedUrl] of Object.entries(cats)) {
                        const option = document.createElement('option');
                        option.value = feedUrl;
                        option.textContent = catName;
                        presetCategory.appendChild(option);
                    }
                } else {
                    presetCategory.disabled = true;
                    presetCategory.innerHTML = '<option value="">-- First Select a Site --</option>';
                }
            });

            presetCategory.addEventListener('change', function() {
                const feedUrl = this.value;
                if (feedUrl && presetSource.value) {
                    const siteName = sourcesData[presetSource.value].name;
                    const catName = this.options[this.selectedIndex].text;
                    
                    sourceUrl.value = feedUrl;
                    sourceName.value = siteName + ' - ' + catName;
                }
            });
        }
    });
</script>
@endpush
