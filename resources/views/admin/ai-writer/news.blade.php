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
                                        <option value="cointelegraph">CoinTelegraph (Crypto)</option>
                                    </select>
                                </div>
                                <div>
                                    <select id="preset_category" class="block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" disabled>
                                        <option value="">-- First Select a Site --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <script>
                            (function() {
                                var bindEvents = function() {
                                    var presetSource = document.getElementById('preset_source');
                                    var presetCategory = document.getElementById('preset_category');
                                    var sourceName = document.getElementById('source_name');
                                    var sourceUrl = document.getElementById('source_url');

                                    if (!presetSource || !presetCategory) return;

                                    var sourcesData = {
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
                                                'Top Stories': 'https://www.aljazeera.com/xml/rss/all.xml',
                                                'Middle East': 'https://www.aljazeera.com/xml/rss/middle-east.xml',
                                                'World News': 'https://www.aljazeera.com/xml/rss/world.xml',
                                                'Sports': 'https://www.aljazeera.com/xml/rss/sport.xml',
                                                'Economy / Business': 'https://www.aljazeera.com/xml/rss/economy.xml'
                                            }
                                        },
                                        'cointelegraph': {
                                            name: 'CoinTelegraph',
                                            categories: {
                                                'Cryptocurrency & Web3': 'https://cointelegraph.com/rss'
                                            }
                                        }
                                    };

                                    presetSource.addEventListener('change', function() {
                                        var siteId = this.value;
                                        presetCategory.innerHTML = '<option value="">-- Select a Category --</option>';
                                        
                                        if (siteId && sourcesData[siteId]) {
                                            presetCategory.disabled = false;
                                            var cats = sourcesData[siteId].categories;
                                            for (var catName in cats) {
                                                if (cats.hasOwnProperty(catName)) {
                                                    var option = document.createElement('option');
                                                    option.value = cats[catName];
                                                    option.textContent = catName;
                                                    presetCategory.appendChild(option);
                                                }
                                            }
                                        } else {
                                            presetCategory.disabled = true;
                                            presetCategory.innerHTML = '<option value="">-- First Select a Site --</option>';
                                        }
                                    });

                                    presetCategory.addEventListener('change', function() {
                                        var feedUrl = this.value;
                                        if (feedUrl && presetSource.value) {
                                            var siteName = sourcesData[presetSource.value].name;
                                            var catName = this.options[this.selectedIndex].text;
                                            
                                            sourceUrl.value = feedUrl;
                                            sourceName.value = siteName + ' - ' + catName;
                                        }
                                    });
                                };
                                
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', bindEvents);
                                } else {
                                    bindEvents();
                                }
                            })();
                        </script>

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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-amber-50 border border-amber-100 p-4 rounded-lg">
                            <div>
                                <label class="block font-medium text-sm text-amber-800">⏱ Run Duration (Days)</label>
                                <p class="text-xs text-amber-600 mb-2">Set 1–30 days. After this period, the source will auto-stop. Leave blank for unlimited.</p>
                                <div class="flex items-center gap-4">
                                    <input type="range" name="duration_days" id="duration_days" min="1" max="30" value="7"
                                           class="flex-1 accent-amber-500"
                                           oninput="document.getElementById('duration_days_val').innerText = this.value + ' days'">
                                    <span id="duration_days_val" class="text-amber-800 font-bold text-sm w-20 text-center bg-amber-100 rounded px-2 py-1">7 days</span>
                                </div>
                                <div class="flex items-center mt-2 gap-2">
                                    <input type="checkbox" id="unlimited_duration" onchange="
                                        var inp = document.getElementById('duration_days');
                                        var val = document.getElementById('duration_days_val');
                                        if(this.checked){ inp.disabled=true; inp.name=''; val.innerText='Unlimited'; }
                                        else{ inp.disabled=false; inp.name='duration_days'; val.innerText=inp.value+' days'; }
                                    " class="rounded border-amber-300 text-amber-600">
                                    <label for="unlimited_duration" class="text-xs text-amber-700">Run indefinitely (no expiry)</label>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="bg-amber-100 rounded-lg p-3 text-xs text-amber-700 leading-relaxed">
                                    <strong>Example:</strong><br>
                                    • 7 days = fetch daily for 1 week<br>
                                    • 30 days = fetch daily for 1 month<br>
                                    • Unlimited = fetch forever (until manually deleted)
                                </div>
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
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                        <h3 class="font-bold text-lg">Active Sources</h3>

                        {{-- Global Cron Countdown Banner --}}
                        <div class="flex items-center gap-3 bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3">
                            <div class="flex-shrink-0 bg-indigo-100 p-2 rounded-full">
                                <svg class="w-5 h-5 text-indigo-600 animate-spin" style="animation-duration:4s" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-400 font-bold uppercase tracking-wider">Next Auto-Fetch (Cron)</p>
                                <p id="global-cron-countdown" class="text-base font-mono font-bold text-indigo-800">Calculating...</p>
                            </div>
                        </div>
                    </div>

                    @if($sources->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Run / Next Fetch</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($sources as $source)
                                    <tr id="source-row-{{ $source->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $source->name }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($source->source_url, 40) }}</div>
                                            @if($source->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                            @endif

                                            @if($source->expires_at)
                                                <div class="mt-1 text-[10px] font-bold uppercase tracking-tighter {{ $source->expires_at->isPast() ? 'text-red-500' : 'text-amber-600' }}">
                                                    @if($source->expires_at->isPast())
                                                        Expired
                                                    @else
                                                        Ends in: {{ now()->diffInDays($source->expires_at) }} days
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $source->category ? $source->category->name : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $source->posts_per_run }} posts / {{ $source->fetch_interval_hours }}h
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @php
                                                $lastRun   = $source->last_run_at;
                                                $intervalH = $source->fetch_interval_hours;
                                                if ($lastRun) {
                                                    $nextRun     = $lastRun->copy()->addHours($intervalH);
                                                    $isDue       = $nextRun->isPast();
                                                    $totalSecs   = $intervalH * 3600;
                                                    $elapsedSecs = min($lastRun->diffInSeconds(now()), $totalSecs);
                                                    $pct         = round(($elapsedSecs / $totalSecs) * 100);
                                                    $remainSecs  = max(0, $nextRun->diffInSeconds(now(), false) * -1);
                                                } else {
                                                    $isDue      = true;
                                                    $pct        = 100;
                                                    $nextRun    = null;
                                                    $remainSecs = 0;
                                                }
                                            @endphp

                                            {{-- Last run label --}}
                                            <div class="text-xs text-gray-500 mb-1">
                                                @if($lastRun)
                                                    Last: {{ $lastRun->diffForHumans() }}
                                                @else
                                                    <span class="text-orange-500 font-semibold">Never run — ready to fetch!</span>
                                                @endif
                                            </div>

                                            {{-- Progress bar --}}
                                            <div class="w-full bg-gray-200 rounded-full h-2 mb-1 overflow-hidden">
                                                <div class="h-2 rounded-full transition-all {{ $isDue ? 'bg-green-500' : 'bg-indigo-500' }}"
                                                     style="width: {{ $pct }}%"></div>
                                            </div>

                                            {{-- Countdown / status --}}
                                            @if($isDue)
                                                <div class="text-xs font-semibold text-green-600 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Ready — next fetch in: <span class="cron-sync-countdown font-mono ml-1">...</span>
                                                </div>
                                            @else
                                                <div class="text-xs text-indigo-600 font-mono" id="countdown-{{ $source->id }}"
                                                     data-seconds="{{ $remainSecs }}">
                                                    Next in: calculating...
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button
                                                    type="button"
                                                    onclick="triggerFetch({{ $source->id }}, '{{ addslashes($source->name) }}', '{{ route('admin.ai-writer.news.fetch', $source->id) }}')"
                                                    id="fetch-btn-{{ $source->id }}"
                                                    class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded font-semibold text-xs transition-all">
                                                    Fetch Now
                                                </button>
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

                        {{-- Live Fetch Log Panel --}}
                        <div id="fetch-log-panel" class="hidden mt-6 border border-indigo-200 rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-indigo-600 px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <svg id="fetch-spinner" class="animate-spin w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span id="fetch-log-title" class="text-white font-semibold text-sm">Fetching...</span>
                                </div>
                                <span id="fetch-timer" class="font-mono font-bold text-indigo-100 text-sm">00:00</span>
                            </div>
                            <ul id="fetch-log-body" class="bg-gray-900 text-green-400 font-mono text-sm p-4 h-48 overflow-y-auto space-y-1 leading-relaxed">
                                <li>Initializing fetch...</li>
                            </ul>
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

    @push('scripts')
    <script>
    var fetchCsrfToken = '{{ csrf_token() }}';

    function triggerFetch(sourceId, sourceName, fetchUrl) {
        var btn = document.getElementById('fetch-btn-' + sourceId);
        var logPanel = document.getElementById('fetch-log-panel');
        var logTitle = document.getElementById('fetch-log-title');
        var logBody = document.getElementById('fetch-log-body');
        var timerEl = document.getElementById('fetch-timer');
        var spinner = document.getElementById('fetch-spinner');

        if (!confirm('Trigger manual fetch for "' + sourceName + '" now? This might take a few minutes.')) return;

        // Disable all fetch buttons during processing
        document.querySelectorAll('[id^="fetch-btn-"]').forEach(function(b) {
            b.disabled = true;
            b.classList.add('opacity-50', 'cursor-not-allowed');
        });
        btn.innerHTML = '<span class="animate-pulse">Fetching...</span>';

        // Show log panel
        logPanel.classList.remove('hidden');
        logPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        logTitle.innerText = 'Fetching: ' + sourceName;
        logBody.innerHTML = '<li class="text-yellow-300">[' + new Date().toLocaleTimeString() + '] Connecting to server...</li>';
        spinner.classList.remove('hidden');
        spinner.classList.add('animate-spin');

        // Start timer
        var seconds = 0;
        var timer = setInterval(function() {
            seconds++;
            var m = Math.floor(seconds / 60).toString().padStart(2, '0');
            var s = (seconds % 60).toString().padStart(2, '0');
            timerEl.innerText = m + ':' + s;
        }, 1000);

        // AJAX fetch
        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': fetchCsrfToken
            },
            body: JSON.stringify({})
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            clearInterval(timer);
            spinner.classList.remove('animate-spin');

            if (data.success) {
                logTitle.innerText = '✅ Completed: ' + sourceName;
                logBody.innerHTML += '<li class="text-green-300">[' + new Date().toLocaleTimeString() + '] ' + data.message + '</li>';
                if (data.output) {
                    data.output.split('\n').forEach(function(line) {
                        if (line.trim()) logBody.innerHTML += '<li class="text-gray-300">' + line + '</li>';
                    });
                }
                logBody.innerHTML += '<li class="text-green-400 font-bold">Done in ' + timerEl.innerText + '. Refreshing page...</li>';
                setTimeout(function() { window.location.reload(); }, 2500);
            } else {
                logTitle.innerText = '❌ Error: ' + sourceName;
                logBody.innerHTML += '<li class="text-red-400">[' + new Date().toLocaleTimeString() + '] ' + data.message + '</li>';
                // Re-enable buttons
                document.querySelectorAll('[id^="fetch-btn-"]').forEach(function(b) {
                    b.disabled = false;
                    b.classList.remove('opacity-50', 'cursor-not-allowed');
                    b.innerHTML = 'Fetch Now';
                });
            }
        })
        .catch(function(err) {
            clearInterval(timer);
            spinner.classList.remove('animate-spin');
            logTitle.innerText = '❌ Failed: ' + sourceName;
            logBody.innerHTML += '<li class="text-red-400">[' + new Date().toLocaleTimeString() + '] Network error: ' + err.message + '</li>';
            document.querySelectorAll('[id^="fetch-btn-"]').forEach(function(b) {
                b.disabled = false;
                b.classList.remove('opacity-50', 'cursor-not-allowed');
                b.innerHTML = 'Fetch Now';
            });
        });
    }
    // Live countdown timers for each source
    (function() {
        var countdowns = document.querySelectorAll('[id^="countdown-"]');
        if (!countdowns.length) return;

        function formatCountdown(totalSecs) {
            if (totalSecs <= 0) return '✅ Ready — awaiting Cron';
            var h = Math.floor(totalSecs / 3600);
            var m = Math.floor((totalSecs % 3600) / 60);
            var s = totalSecs % 60;
            var parts = [];
            if (h > 0) parts.push(h + 'h');
            if (m > 0 || h > 0) parts.push(m + 'm');
            parts.push(s + 's');
            return 'Next fetch in: ' + parts.join(' ');
        }

        countdowns.forEach(function(el) {
            var remaining = parseInt(el.dataset.seconds, 10);
            el.innerText = formatCountdown(remaining);
            var interval = setInterval(function() {
                remaining--;
                if (remaining <= 0) {
                    el.innerText = '✅ Next Cron run imminent';
                    el.classList.remove('text-indigo-600');
                    el.classList.add('text-green-600', 'font-semibold');
                    clearInterval(interval);
                } else {
                    el.innerText = formatCountdown(remaining);
                }
            }, 1000);
        });
    })();

    // Global Cron countdown — Laravel runs schedule:run every minute, but news:fetch-auto is hourly.
    // We show time until next top-of-hour.
    (function() {
        var globalEl  = document.getElementById('global-cron-countdown');
        var syncSpans = document.querySelectorAll('.cron-sync-countdown');
        if (!globalEl) return;

        function secsUntilNextHour() {
            var now = new Date();
            var nextHour = new Date(now);
            nextHour.setHours(now.getHours() + 1, 0, 0, 0);
            return Math.max(0, Math.floor((nextHour - now) / 1000));
        }

        function fmtHMS(s) {
            if (s <= 0) return '⚡ Running now!';
            var h = Math.floor(s / 3600);
            var m = Math.floor((s % 3600) / 60);
            var sec = s % 60;
            var parts = [];
            if (h > 0) parts.push(h + 'h');
            parts.push(String(m).padStart(2,'0') + 'm');
            parts.push(String(sec).padStart(2,'0') + 's');
            return parts.join(' ');
        }

        function tick() {
            var secs = secsUntilNextHour();
            var formatted = fmtHMS(secs);
            globalEl.innerText = formatted;
            syncSpans.forEach(function(sp) { sp.innerText = formatted; });
        }

        tick();
        setInterval(tick, 1000);
    })();
    </script>
    @endpush
</x-app-layout>


