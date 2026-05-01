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
                                                'Top Stories': 'http://feeds.bbci.co.uk/news/rss.xml',
                                                'World': 'http://feeds.bbci.co.uk/news/world/rss.xml',
                                                'UK': 'http://feeds.bbci.co.uk/news/uk/rss.xml',
                                                'Politics': 'http://feeds.bbci.co.uk/news/politics/rss.xml',
                                                'Technology': 'http://feeds.bbci.co.uk/news/technology/rss.xml',
                                                'Business': 'http://feeds.bbci.co.uk/news/business/rss.xml',
                                                'Health': 'http://feeds.bbci.co.uk/news/health/rss.xml',
                                                'Science': 'http://feeds.bbci.co.uk/news/science_and_environment/rss.xml',
                                                'Education': 'http://feeds.bbci.co.uk/news/education/rss.xml',
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Target Category</label>
                                <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Author</label>
                                <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">-- Default Admin --</option>
                                    @foreach($users ?? [] as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Smart Scheduling Section -->
                        <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-100 shadow-sm mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-indigo-900 font-bold flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    স্মার্ট শিডিউল (Smart Schedule)
                                </h4>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="use_smart_schedule" id="use_smart_schedule" value="1" checked class="sr-only peer" onchange="toggleSmartSchedule()">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    <span class="ml-3 text-sm font-medium text-indigo-900">Active</span>
                                </label>
                            </div>

                            <div id="smart_schedule_section">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                                    <div>
                                        <label class="block font-medium text-sm text-indigo-800 mb-1">ডেইলি কয়টি পোস্ট চান? (Daily Goal)</label>
                                        <div class="relative">
                                            <input type="number" name="daily_post_limit" id="daily_post_limit" value="10" min="1" max="100" 
                                                   class="block w-full rounded-lg border-indigo-300 pl-4 pr-12 py-3 text-lg font-bold text-indigo-900 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                                   oninput="calculateSmartSchedule()">
                                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <span class="text-indigo-400 font-medium">Post/Day</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 border border-indigo-100 flex items-center gap-4 shadow-inner">
                                        <div class="bg-indigo-100 p-2 rounded-full">
                                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-indigo-400 font-bold uppercase tracking-wider">শিডিউল প্রিভিউ (Preview)</p>
                                            <p id="schedule_preview" class="text-sm font-semibold text-indigo-800">সিস্টেম প্রতি ২.৪ ঘণ্টা অন্তর ১টি করে পোস্ট করবে।</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="button" onclick="document.getElementById('advanced_scheduling').classList.toggle('hidden')" 
                                        class="text-xs font-bold text-indigo-500 hover:text-indigo-700 uppercase tracking-widest flex items-center gap-1 focus:outline-none">
                                    Advanced Scheduling
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="advanced_scheduling" class="hidden mt-4 pt-4 border-t border-indigo-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block font-medium text-sm text-gray-700">Articles to fetch each time</label>
                                            <input type="number" name="posts_per_run" id="posts_per_run" value="5" min="1" max="20" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-sm text-gray-700">Fetch Frequency (Hours)</label>
                                            <input type="number" name="fetch_interval_hours" id="fetch_interval_hours" value="24" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        </div>
                                    </div>
                                </div>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category & Author</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-bold">{{ $source->category ? $source->category->name : 'N/A' }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="font-medium text-gray-400">Author:</span> {{ $source->user ? $source->user->name : 'Admin (Default)' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($source->use_smart_schedule)
                                                <div class="font-bold text-indigo-600">{{ $source->daily_post_limit }} posts/day</div>
                                                <div class="text-[10px] text-indigo-400 uppercase font-medium">
                                                    @php
                                                        $mins = ($source->daily_post_limit > 0) ? (24 / $source->daily_post_limit) * 60 : 0;
                                                        if ($mins >= 60) {
                                                            echo "Every " . round($mins/60, 1) . "h";
                                                        } else {
                                                            echo "Every " . round($mins, 1) . "m";
                                                        }
                                                    @endphp
                                                </div>
                                            @else
                                                <div class="font-medium">{{ $source->posts_per_run }} posts each time</div>
                                                <div class="text-[10px] text-gray-400 uppercase">Every {{ $source->fetch_interval_hours }}h</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @php
                                                $lastRun   = $source->last_run_at;
                                                $intervalH = ($source->use_smart_schedule && $source->daily_post_limit > 0) ? (24 / $source->daily_post_limit) : $source->fetch_interval_hours;
                                                
                                                if ($lastRun) {
                                                    $nextRun     = $lastRun->copy()->addMinutes($intervalH * 60);
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
                                                    onclick="openEditModal({{ json_encode($source) }})"
                                                    class="text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded font-semibold text-xs transition-all">
                                                    Edit
                                                </button>
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
            
            <div class="bg-gray-800 text-white p-4 mt-2 rounded-xl font-mono text-xs overflow-x-auto border-l-4 border-indigo-500">
                * * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1
            </div>
        </div>
    </div>

    <!-- Edit Source Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-6 py-6 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900" id="modal-title">Edit News Source</h3>
                        <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="bg-white px-6 py-6 max-h-[70vh] overflow-y-auto">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Source Name</label>
                                    <input type="text" name="name" id="edit_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">RSS/Target URL</label>
                                    <input type="url" name="source_url" id="edit_source_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Target Category</label>
                                    <select name="category_id" id="edit_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Author</label>
                                    <select name="user_id" id="edit_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">-- Default Admin --</option>
                                        @foreach($users ?? [] as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Smart Schedule Card (Edit) -->
                            <div class="bg-indigo-50 p-5 rounded-xl border border-indigo-100">
                                <div class="flex items-center justify-between mb-4">
                                    <label class="font-bold text-indigo-900">Smart Schedule</label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="use_smart_schedule" id="edit_use_smart_schedule" value="1" class="sr-only peer" onchange="toggleEditSmartSchedule()">
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                                <div id="edit_smart_section" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                                    <div>
                                        <label class="block text-xs font-bold text-indigo-400 uppercase tracking-tighter mb-1">Daily Post Goal</label>
                                        <input type="number" name="daily_post_limit" id="edit_daily_post_limit" min="1" max="100" class="block w-full rounded-lg border-indigo-200" oninput="calculateEditSmart()">
                                    </div>
                                    <div class="text-sm text-indigo-700 italic pt-4" id="edit_schedule_preview"></div>
                                </div>
                                <div id="edit_advanced_section" class="mt-4 pt-4 border-t border-indigo-100 hidden">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Per Run</label>
                                            <input type="number" name="posts_per_run" id="edit_posts_per_run" min="1" max="20" class="block w-full rounded-lg border-gray-200">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Interval (Hours)</label>
                                            <input type="number" name="fetch_interval_hours" id="edit_fetch_interval_hours" min="1" max="168" class="block w-full rounded-lg border-gray-200">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Featured Image</label>
                                    <select name="featured_image_source" id="edit_featured_image_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="none">Original/None</option>
                                        <option value="pexels">Free (Pexels)</option>
                                        <option value="unsplash">Free (Unsplash)</option>
                                        <option value="dalle">AI Generated (DALL-E)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">In-Content Image Source</label>
                                    <select name="in_content_image_source" id="edit_in_content_image_source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="none">None</option>
                                        <option value="pexels">Free (Pexels)</option>
                                        <option value="unsplash">Free (Unsplash)</option>
                                        <option value="dalle">AI Generated (DALL-E)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="edit_is_active" class="ml-2 block text-sm text-gray-900 font-bold">Active Status</label>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse space-x-2 space-x-reverse">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold shadow-sm hover:bg-indigo-700 transition-all">Update Source</button>
                        <button type="button" onclick="closeEditModal()" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-bold hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
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

    // Global Cron countdown — Now tracks 5-minute intervals
    (function() {
        var globalEl  = document.getElementById('global-cron-countdown');
        var syncSpans = document.querySelectorAll('.cron-sync-countdown');
        if (!globalEl) return;

        function secsUntilNextCronRun() {
            var now = new Date();
            var mins = now.getMinutes();
            // Next 5-minute mark (5, 10, 15, ..., 60)
            var nextMins = Math.ceil((mins + 0.1) / 5) * 5;
            var nextRun = new Date(now);
            nextRun.setMinutes(nextMins, 0, 0);
            return Math.max(0, Math.floor((nextRun - now) / 1000));
        }

        function fmtHMS(s) {
            if (s <= 0) return '⚡ Running now!';
            var m = Math.floor(s / 60);
            var sec = s % 60;
            return String(m).padStart(2,'0') + 'm ' + String(sec).padStart(2,'0') + 's';
        }

        function tick() {
            var secs = secsUntilNextCronRun();
            var formatted = fmtHMS(secs);
            globalEl.innerText = formatted;
            syncSpans.forEach(function(sp) { sp.innerText = formatted; });
        }

        tick();
        setInterval(tick, 1000);
    })();

    // Modal Control Logic
    function openEditModal(source) {
        var form = document.getElementById('editForm');
        form.action = '/admin/ai-writer/news/' + source.id;
        
        document.getElementById('edit_name').value = source.name;
        document.getElementById('edit_source_url').value = source.source_url;
        document.getElementById('edit_category_id').value = source.category_id;
        document.getElementById('edit_user_id').value = source.user_id || '';
        document.getElementById('edit_daily_post_limit').value = source.daily_post_limit || 10;
        document.getElementById('edit_posts_per_run').value = source.posts_per_run;
        document.getElementById('edit_fetch_interval_hours').value = source.fetch_interval_hours;
        document.getElementById('edit_featured_image_source').value = source.featured_image_source;
        document.getElementById('edit_in_content_image_source').value = source.in_content_image_source;
        document.getElementById('edit_use_smart_schedule').checked = source.use_smart_schedule;
        document.getElementById('edit_is_active').checked = source.is_active;

        toggleEditSmartSchedule();
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function toggleEditSmartSchedule() {
        var isSmart = document.getElementById('edit_use_smart_schedule').checked;
        var smartSec = document.getElementById('edit_smart_section');
        var advSec = document.getElementById('edit_advanced_section');
        
        if (isSmart) {
            smartSec.classList.remove('opacity-50');
            smartSec.querySelectorAll('input').forEach(i => i.disabled = false);
            advSec.classList.add('hidden');
            calculateEditSmart();
        } else {
            smartSec.classList.add('opacity-50');
            smartSec.querySelectorAll('input').forEach(i => i.disabled = true);
            advSec.classList.remove('hidden');
        }
    }

    function calculateEditSmart() {
        var goal = document.getElementById('edit_daily_post_limit').value;
        var preview = document.getElementById('edit_schedule_preview');
        if (goal > 0) {
            var mins = ((24 / goal) * 60).toFixed(1);
            preview.innerHTML = 'Every ' + mins + ' minutes';
            document.getElementById('edit_posts_per_run').value = 1;
            document.getElementById('edit_fetch_interval_hours').value = Math.max(1, Math.round(24/goal));
        }
    }

    function toggleSmartSchedule() {
        var isSmart = document.getElementById('use_smart_schedule').checked;
        var section = document.getElementById('smart_schedule_section');
        if (isSmart) {
            section.classList.remove('opacity-50');
            section.querySelectorAll('input').forEach(i => i.disabled = false);
            calculateSmartSchedule();
        } else {
            section.classList.add('opacity-50');
            section.querySelectorAll('input').forEach(i => i.disabled = true);
        }
    }

    function calculateSmartSchedule() {
        var goal = document.getElementById('daily_post_limit').value;
        var preview = document.getElementById('schedule_preview');
        var postsPerRun = document.getElementById('posts_per_run');
        var interval = document.getElementById('fetch_interval_hours');

        if (goal > 0) {
            var hours = (24 / goal).toFixed(1);
            preview.innerHTML = '✅ সিস্টেম প্রতি <strong>' + hours + ' ঘণ্টা</strong> অন্তর ১টি করে পোস্ট অটোমেটিক পাবলিশ করবে।';
            
            // Background sync for the existing interval logic
            postsPerRun.value = 1;
            interval.value = Math.max(1, Math.round(hours));
        }
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        calculateSmartSchedule();
    });
    </script>
    @endpush
</x-app-layout>


