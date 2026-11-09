<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500 animate-ping"></span>
                    {{ __('Advanced Analytics & Live Traffic') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Real-time user journeys, bot protection & AI traffic intelligence</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.analytics.export', ['date_filter' => $filter]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm font-bold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export CSV
                </a>
                <form method="GET" action="{{ route('admin.analytics.index') }}" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg shadow-sm border border-gray-200">
                    <span class="text-xs font-bold text-gray-500 uppercase">Filter:</span>
                    <select name="date_filter" onchange="this.form.submit()" class="text-sm border-none focus:ring-0 text-indigo-700 font-bold bg-transparent cursor-pointer py-1 pr-8">
                        <option value="today" {{ $filter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $filter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="last_7_days" {{ $filter === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="last_30_days" {{ $filter === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_month" {{ $filter === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="all_time" {{ $filter === 'all_time' ? 'selected' : '' }}>All Time</option>
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-700 bg-green-50 border border-green-200 p-4 rounded-xl flex items-center justify-between">
                    <span>{{ session('status') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-800 font-bold text-lg">&times;</button>
                </div>
            @endif

            <!-- Tabs Menu -->
            <div class="border-b border-gray-200 mb-6 bg-white rounded-xl shadow-sm overflow-x-auto">
                <nav class="-mb-px flex space-x-8 px-6 min-w-max" aria-label="Tabs">
                    <button onclick="openTab('tab-overview', this)" class="tab-btn active-tab border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Overview & Live
                    </button>
                    <button onclick="openTab('tab-sources', this)" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Traffic Sources
                    </button>
                    <button onclick="openTab('tab-pages', this)" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Top Content
                    </button>
                    <button onclick="openTab('tab-live', this)" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-400"></span> Live Log
                    </button>
                    <button onclick="openTab('tab-bots', this)" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Bot Traffic & Security
                    </button>
                </nav>
            </div>

            <!-- ========================================== -->
            <!-- TAB: OVERVIEW & LIVE JOURNEYS -->
            <!-- ========================================== -->
            <div id="tab-overview" class="tab-pane block space-y-6">

                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <!-- Live Traffic (Upgraded Live Pulse) -->
                    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-6 rounded-2xl shadow-xl text-white relative overflow-hidden transform transition hover:scale-[1.02]">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                        <div class="relative z-10">
                            <div class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-2 flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                Live Now
                            </div>
                            <div class="text-4xl font-black tracking-tight" id="live-user-count">{{ number_format($liveUsers) }}</div>
                            <div class="mt-2 text-[11px] text-indigo-100/90 font-medium flex items-center gap-2 flex-wrap">
                                <span class="bg-white/20 px-2 py-0.5 rounded-full">+<span id="live-joined-count">{{ $joined5m }}</span> entered</span>
                                <span class="bg-white/20 px-2 py-0.5 rounded-full">-<span id="live-left-count">{{ $left5m }}</span> exited</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Traffic Filtered -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">
                                Page Views (@if($filter === 'all_time') All Time @else Filtered @endif)
                            </div>
                            <div class="text-3xl font-black text-gray-800">{{ number_format($filteredTotal) }}</div>
                            <div class="text-xs text-gray-400 mt-1 font-medium">Today: {{ number_format($visitsToday) }}</div>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Unique Users -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Unique Visitors</div>
                            <div class="text-3xl font-black text-gray-800">{{ number_format($uniqueUsers) }}</div>
                            <div class="text-xs text-indigo-500 mt-1 font-medium">Human sessions</div>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Bounce Rate -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Bounce Rate</div>
                            <div class="text-3xl font-black text-gray-800">{{ $bounceRate }}%</div>
                            <div class="text-xs text-gray-400 mt-1 font-medium">Single-page sessions</div>
                        </div>
                        <div class="p-3 bg-red-50 rounded-xl text-red-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                    </div>

                    <!-- Bot Shield -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Bot Traffic</div>
                            <div class="text-3xl font-black text-gray-800">{{ number_format($totalGoodBots + $totalBadBots) }}</div>
                            <div class="text-xs text-emerald-600 mt-1 font-medium">{{ $totalGoodBots }} SEO / {{ $totalBadBots }} Bad</div>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-xl text-gray-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Live Stream Control & AI Intelligence Bar -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            Live Zero-Reload Stream
                        </span>
                        <span class="text-xs text-gray-500 font-mono" id="live-stream-clock">Connected</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="stream-toggle-btn" onclick="toggleStream()" class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 text-gray-700 transition-colors flex items-center gap-1.5">
                            <span id="stream-toggle-icon">⏸️</span>
                            <span id="stream-toggle-text">Pause Stream</span>
                        </button>
                        <button type="button" onclick="fetchLiveFeed()" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Refresh Now
                        </button>
                    </div>
                </div>

                <!-- AI Intelligence & Threat Shield Card -->
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-indigo-500/20">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-indigo-500/20 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-indigo-500/20 rounded-xl border border-indigo-400/30 text-indigo-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    AI Live Traffic Intelligence
                                    <span class="text-[10px] bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 px-2 py-0.5 rounded-full uppercase tracking-wider font-extrabold">Autonomous</span>
                                </h3>
                                <p class="text-xs text-indigo-200/70">Real-time behavior analysis, content engagement & anomaly detection</p>
                            </div>
                        </div>
                        <div id="threat-indicator">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                System Safe & Protected
                            </span>
                        </div>
                    </div>

                    <div id="ai-summary-text" class="space-y-2 text-sm text-indigo-100 leading-relaxed font-sans">
                        <p class="font-medium bg-white/5 p-3 rounded-xl border border-white/5">
                            {{ $aiSummary['bn'] ?? 'লাইভ ট্র্যাফিক ডেটা বিশ্লেষণ করা হচ্ছে...' }}
                        </p>
                    </div>

                    <!-- Threat Alert Box (Hidden by default, shown dynamically when threat detected) -->
                    <div id="threat-alert-box" class="hidden mt-4 bg-red-500/20 border border-red-500/50 rounded-xl p-4 flex flex-col md:flex-row justify-between items-center gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⚠️</span>
                            <div>
                                <div class="text-sm font-bold text-red-200">Suspicious Scraper Anomaly Detected</div>
                                <div class="text-xs text-red-300" id="threat-alert-message">High frequency hits from IP</div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.analytics.blockIp') }}" class="inline">
                            @csrf
                            <input type="hidden" name="ip_address" id="threat-ip-input" value="">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-4 py-2 rounded-lg transition-colors">
                                Block This IP Now
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- CORE FEATURE: LIVE ACTIVE VISITORS & USER JOURNEYS (OVERVIEW) -->
                <!-- ============================================================== -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                                Active Real Visitors & Live User Journeys
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Real-time step-by-step path: see what page each visitor landed on, where they navigated next, and their dwell time without refreshing.
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-semibold text-gray-500">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> Active Now (&lt;45s)</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Idle</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400"></span> Exited</span>
                        </div>
                    </div>

                    <!-- Live User Journeys Container -->
                    <div id="live-user-journeys-container" class="space-y-3">
                        @if($activeSessions->isEmpty())
                        <div class="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <div class="w-12 h-12 mx-auto mb-3 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-between p-3 animate-pulse">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <h4 class="text-sm font-bold text-gray-700">Waiting for Live Visitors...</h4>
                            <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                                When a real user browses your website, their live card with country, device, and step-by-step journey will automatically appear right here with zero reload!
                            </p>
                        </div>
                        @else
                        @foreach($activeSessions as $s)
                        <div class="bg-gray-50 hover:bg-indigo-50/40 transition-colors border border-gray-200 rounded-xl p-4 space-y-3">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
                                <div class="flex items-center gap-3 flex-wrap">
                                    @if($s->country_code)
                                    <img src="https://flagcdn.com/w24/{{ strtolower($s->country_code) }}.png" alt="{{ $s->country }}" class="w-5 h-auto rounded shadow-sm inline-block">
                                    @endif
                                    <span class="font-bold text-sm text-gray-900">{{ $s->city ? $s->city . ', ' : '' }}{{ $s->country }}</span>
                                    
                                    <span class="bg-gray-200/80 text-gray-700 text-xs px-2 py-0.5 rounded font-medium flex items-center gap-1">
                                        @if($s->device_type === 'Mobile') 📱 Mobile
                                        @elseif($s->device_type === 'Tablet') 📟 Tablet
                                        @else 💻 Desktop @endif
                                        &bull; {{ $s->os }} &bull; {{ $s->browser }}
                                    </span>

                                    <span class="font-mono text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                        {{ $s->ip_address }}
                                    </span>

                                    <span class="text-xs text-gray-400">
                                        Via: {{ $s->referrer_domain ?: 'Direct' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-500 font-medium">⏱️ On Site: {{ $s->dwell_seconds ? gmdate('i:s', $s->dwell_seconds) : 'Just joined' }}</span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold @if($s->computed_status === 'active') bg-green-100 text-green-700 @elseif($s->computed_status === 'idle') bg-amber-100 text-amber-700 @else bg-red-100 text-red-700 @endif">
                                        <span class="w-1.5 h-1.5 rounded-full @if($s->computed_status === 'active') bg-green-500 @elseif($s->computed_status === 'idle') bg-amber-500 @else bg-red-500 @endif"></span>
                                        {{ ucfirst($s->computed_status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Visual User Journey Flow -->
                            <div class="bg-white p-3 rounded-lg border border-gray-200 text-xs">
                                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                                    <span>🧭 Visitor Journey Path ({{ $s->page_views }} page views):</span>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="flex items-center gap-1.5 bg-gray-100 px-2.5 py-1 rounded-md text-gray-700 font-medium truncate max-w-xs">
                                        <span>Landing:</span>
                                        <span class="font-bold text-indigo-700">{{ parse_url($s->landing_page, PHP_URL_PATH) ?: '/' }}</span>
                                    </div>
                                    <span class="text-gray-400 font-bold">&rarr;</span>
                                    <div class="flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-md text-indigo-800 font-medium truncate max-w-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        <span>Current:</span>
                                        <a href="{{ $s->current_page }}" target="_blank" class="font-bold hover:underline text-indigo-600 truncate">{{ $s->current_page_title ?: (parse_url($s->current_page, PHP_URL_PATH) ?: '/') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>

                <!-- Live Stream Event Ticker -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-3">
                    <h3 class="text-base font-bold text-gray-800 flex items-center justify-between border-b pb-3">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Real-Time Live Event Stream (Last Actions)
                        </span>
                        <span class="text-xs text-gray-400 font-mono" id="live-log-updated">Auto-updating</span>
                    </h3>
                    <div id="live-stream-ticker" class="space-y-2 max-h-60 overflow-y-auto font-mono text-xs text-gray-600">
                        <!-- Populated by JS live feed -->
                        <div class="text-gray-400 italic py-2 text-center">Listening for real-time hits...</div>
                    </div>
                </div>

                <!-- Global Map + Devices/Browsers Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Live Map -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Global Visitor Distribution
                        </h3>
                        <div id="vmap" style="width: 100%; height: 350px;"></div>
                    </div>

                    <!-- Devices & Browsers -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col gap-6">
                        <div>
                            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider border-b pb-2 mb-3">Devices</h3>
                            <div class="space-y-3">
                                @foreach($devices as $device => $count)
                                @if($count > 0)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-700 flex items-center gap-2">
                                        @if($device === 'Desktop') 💻
                                        @elseif($device === 'Tablet') 📟
                                        @else 📱 @endif
                                        {{ $device }}
                                    </span>
                                    <span class="bg-gray-100 px-2 py-1 rounded text-xs font-bold">{{ number_format($count) }}</span>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider border-b pb-2 mb-3">Top Browsers</h3>
                            <div class="space-y-3">
                                @foreach($browsers as $browser => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-700">{{ $browser }}</span>
                                    <span class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded text-xs font-bold">{{ number_format($count) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- End tab-overview -->

            <!-- ========================================== -->
            <!-- TAB: TOP CONTENT -->
            <!-- ========================================== -->
            <div id="tab-pages" class="tab-pane hidden space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Top Pages Visited</h3>
                    @if($topPages->isEmpty())
                        <p class="text-gray-500">No data available.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead>
                                    <tr class="text-gray-500 uppercase tracking-wider text-xs border-b">
                                        <th class="pb-2 font-semibold">Page URL</th>
                                        <th class="pb-2 font-semibold text-right">Views</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($topPages as $page)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-2">
                                            <a href="{{ $page->url }}" target="_blank" class="text-indigo-600 hover:underline inline-block w-full max-w-sm truncate" title="{{ $page->url }}">{{ str_replace(url('/'), '', $page->url) ?: '/' }}</a>
                                        </td>
                                        <td class="py-3 px-2 text-right">
                                            <span class="bg-green-100 text-green-700 font-bold px-2 py-1 rounded text-xs">{{ number_format($page->count) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB: TRAFFIC SOURCES -->
            <!-- ========================================== -->
            <div id="tab-sources" class="tab-pane hidden space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Countries -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Top Countries</h3>
                        @if($topCountries->isEmpty())
                            <p class="text-gray-500">No data available.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach($topCountries as $country)
                                <li class="flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors p-3 rounded-lg border border-gray-100">
                                    <div class="flex items-center gap-3">
                                        @if($country->country_code)
                                        <img src="https://flagcdn.com/w40/{{ strtolower($country->country_code) }}.png" alt="{{ $country->country }}" class="w-6 h-auto rounded shadow-sm object-cover">
                                        @endif
                                        <span class="font-bold text-gray-700">{{ $country->country }}</span>
                                    </div>
                                    <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">{{ number_format($country->count) }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <!-- Top Referrers -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Top Referrers</h3>
                        @if($topReferrers->isEmpty())
                            <p class="text-gray-500">No data available.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach($topReferrers as $ref)
                                @php
                                    $domain = parse_url($ref->referrer, PHP_URL_HOST) ?? $ref->referrer;
                                @endphp
                                <li class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100 truncate flex-wrap gap-2 hover:bg-gray-100 transition-colors">
                                    <a href="{{ $ref->referrer }}" target="_blank" class="font-bold text-indigo-600 hover:text-indigo-800 truncate max-w-xs transition-colors">{{ $domain }}</a>
                                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold">{{ number_format($ref->count) }} Clicks</span>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB: LIVE LOG -->
            <!-- ========================================== -->
            <div id="tab-live" class="tab-pane hidden space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse inline-block"></span>
                        Live Real-time Log (Last 50 Human Visitors)
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Visitor / Device</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Page Visited</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Referrer</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100" id="live-log-tbody">
                                @foreach($recentVisits as $v)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $v->created_at->diffForHumans() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($v->country_code)
                                            <img src="https://flagcdn.com/w20/{{ strtolower($v->country_code) }}.png" alt="{{ $v->country }}" class="w-5 h-auto rounded shadow-sm inline-block">
                                            @endif
                                            <span class="text-sm font-bold text-gray-900">{{ $v->city ? $v->city . ', ' : '' }}{{ $v->country }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="text-indigo-600 font-mono bg-indigo-50 px-2 py-0.5 rounded">{{ $v->ip_address }}</span>
                                            <span class="text-gray-400">{{ $v->device_type }} &bull; {{ $v->os }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-sm">
                                        <a href="{{ $v->url }}" target="_blank" class="hover:text-indigo-600 transition-colors block truncate font-medium">
                                            {{ $v->page_title ?: (str_replace(url('/'), '', $v->url) ?: '/') }}
                                        </a>
                                        <span class="text-xs text-gray-400 block truncate">{{ str_replace(url('/'), '', $v->url) ?: '/' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        @if($v->referrer)
                                            <a href="{{ $v->referrer }}" target="_blank" class="text-gray-500 hover:text-indigo-600 transition-colors">{{ Str::limit($v->referrer, 30) }}</a>
                                        @else
                                            <span class="text-gray-400 italic">Direct / Search</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if(in_array($v->ip_address, $blockedIpList))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Banned
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.analytics.blockIp') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="ip_address" value="{{ $v->ip_address }}">
                                                <button type="submit" onclick="return confirm('Block this IP ({{ $v->ip_address }}) permanently?')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1 rounded text-xs font-bold transition-colors">
                                                    Block IP
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB: BOT TRAFFIC & SECURITY -->
            <!-- ========================================== -->
            <div id="tab-bots" class="tab-pane hidden space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Good Bots -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4">
                        <h3 class="text-sm font-bold text-green-600 uppercase tracking-wider border-b pb-2 mb-1 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Good Bots (Search Engine Crawlers)
                        </h3>
                        @if(empty($goodBots))
                            <p class="text-gray-400 text-xs">No search engine bots recorded in this period.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($goodBots as $bot => $count)
                                <div class="flex items-center justify-between text-sm bg-green-50 px-3 py-2 rounded-lg border border-green-100">
                                    <span class="font-bold text-green-700">{{ $bot }}</span>
                                    <span class="bg-white text-green-600 px-2 py-0.5 rounded text-xs font-bold shadow-sm">{{ number_format($count) }}</span>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Bad Bots -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4">
                        <h3 class="text-sm font-bold text-red-600 uppercase tracking-wider border-b pb-2 mb-1 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Scrapers & Bad Crawlers
                        </h3>
                        @if(empty($badBots))
                            <p class="text-gray-400 text-xs">No malicious bots detected in this period.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($badBots as $bot => $count)
                                <div class="flex items-center justify-between text-sm bg-red-50 px-3 py-2 rounded-lg border border-red-100">
                                    <span class="font-bold text-red-700 truncate max-w-xs">{{ $bot }}</span>
                                    <span class="bg-white text-red-600 px-2 py-0.5 rounded text-xs font-bold shadow-sm">{{ number_format($count) }}</span>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Blocked IP Management -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-bold text-gray-800 border-b pb-3 mb-4">Manually Blocked IP Addresses</h3>
                    @if($blockedIps->isEmpty())
                        <p class="text-xs text-gray-400">No IPs currently blocked.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($blockedIps as $bip)
                            <div class="py-2.5 flex items-center justify-between text-sm">
                                <div>
                                    <span class="font-mono font-bold text-gray-900">{{ $bip->ip_address }}</span>
                                    <span class="text-xs text-gray-400 ml-2">({{ $bip->reason }})</span>
                                </div>
                                <form method="POST" action="{{ route('admin.analytics.unblockIp') }}">
                                    @csrf
                                    <input type="hidden" name="ip_address" value="{{ $bip->ip_address }}">
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-bold">Unblock</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

<script>
    function openTab(tabId, element) {
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('block');
            pane.classList.add('hidden');
        });
        
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-indigo-500', 'text-indigo-600', 'font-bold');
            btn.classList.add('border-transparent', 'text-gray-500', 'font-medium');
        });
        
        document.getElementById(tabId).classList.remove('hidden');
        document.getElementById(tabId).classList.add('block');
        
        element.classList.remove('border-transparent', 'text-gray-500', 'font-medium');
        element.classList.add('border-indigo-500', 'text-indigo-600', 'font-bold');
    }
</script>

<!-- Load jsVectorMap CSS and Scripts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css" />
<script src="https://cdn.jsdelivr.net/npm/jsvectormap"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const countryData = {};
        @foreach($topCountries as $country)
            @if($country->country_code)
                countryData['{{ strtoupper($country->country_code) }}'] = {{ $country->count }};
            @endif
        @endforeach

        if (document.getElementById('vmap')) {
            new jsVectorMap({
                selector: '#vmap',
                map: 'world',
                backgroundColor: 'transparent',
                zoomOnScroll: false,
                visualizeData: {
                    scale: ['#e0e7ff', '#4f46e5'],
                    values: countryData
                },
                regionStyle: {
                    initial: {
                        fill: '#f3f4f6',
                        stroke: '#d1d5db',
                        strokeWidth: 0.5,
                        fillOpacity: 1
                    },
                    hover: {
                        fillOpacity: 0.8,
                        cursor: 'pointer'
                    }
                },
                onRegionTooltipShow(event, tooltip, code) {
                    const count = countryData[code] || 0;
                    tooltip.text(
                        `<div style="font-weight:bold">${tooltip.text()}</div><div style="font-size:12px;margin-top:2px;">${count} Views</div>`,
                        true
                    );
                }
            });
        }
    });
</script>

<!-- ============================================================== -->
<!-- ZERO-RELOAD REAL-TIME LIVE STREAM ENGINE -->
<!-- ============================================================== -->
<script>
    const LIVE_FEED_URL = '{{ route('admin.analytics.liveFeed') }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const BLOCK_IP_URL = '{{ route('admin.analytics.blockIp') }}';
    let isStreamPaused = false;
    let streamTimer = null;

    function toggleStream() {
        isStreamPaused = !isStreamPaused;
        const icon = document.getElementById('stream-toggle-icon');
        const text = document.getElementById('stream-toggle-text');
        const clock = document.getElementById('live-stream-clock');
        if (isStreamPaused) {
            if (icon) icon.textContent = '▶️';
            if (text) text.textContent = 'Resume Stream';
            if (clock) clock.textContent = 'Paused';
        } else {
            if (icon) icon.textContent = '⏸️';
            if (text) text.textContent = 'Pause Stream';
            fetchLiveFeed();
        }
    }

    function renderJourneys(sessions) {
        const container = document.getElementById('live-user-journeys-container');
        if (!container) return;

        if (!sessions || sessions.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div class="w-12 h-12 mx-auto mb-3 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center p-3 animate-pulse">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h4 class="text-sm font-bold text-gray-700">Waiting for Live Visitors...</h4>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        When a real user browses your website, their live card with country, device, and step-by-step journey will automatically appear right here with zero reload!
                    </p>
                </div>
            `;
            return;
        }

        let html = '';
        sessions.forEach(s => {
            const flagImg = s.country_code ? `<img src="https://flagcdn.com/w24/${s.country_code}.png" alt="${s.country}" class="w-5 h-auto rounded shadow-sm inline-block">` : '';
            const locationText = (s.city ? s.city + ', ' : '') + s.country;
            const deviceIcon = s.device_type === 'Mobile' ? '📱 Mobile' : (s.device_type === 'Tablet' ? '📟 Tablet' : '💻 Desktop');

            let statusClass = 'bg-green-100 text-green-700';
            let dotClass = 'bg-green-500 animate-pulse';
            if (s.status === 'idle') {
                statusClass = 'bg-amber-100 text-amber-700';
                dotClass = 'bg-amber-500';
            } else if (s.status === 'left') {
                statusClass = 'bg-red-100 text-red-700';
                dotClass = 'bg-red-500';
            }

            // Build journey steps
            let stepsHtml = '';
            if (s.journey && s.journey.length > 0) {
                s.journey.forEach((step, idx) => {
                    const isLast = (idx === s.journey.length - 1);
                    const dwellBadge = step.dwell ? `<span class="text-[10px] bg-black/10 px-1.5 py-0.2 rounded font-mono">⏱️ ${step.dwell}</span>` : '';
                    if (isLast) {
                        stepsHtml += `
                            <div class="flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-md text-indigo-900 font-medium truncate max-w-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                <span class="text-indigo-500 font-bold">Now:</span>
                                <a href="${step.url}" target="_blank" class="font-bold hover:underline text-indigo-700 truncate" title="${step.title}">${step.title}</a>
                                ${dwellBadge}
                            </div>
                        `;
                    } else {
                        stepsHtml += `
                            <div class="flex items-center gap-1.5 bg-gray-100 px-2.5 py-1 rounded-md text-gray-700 font-medium truncate max-w-xs">
                                <span class="text-gray-400 font-bold">${idx + 1}.</span>
                                <span class="font-bold text-gray-800 truncate" title="${step.title}">${step.title}</span>
                                ${dwellBadge}
                            </div>
                            <span class="text-gray-300 font-bold">&rarr;</span>
                        `;
                    }
                });
            } else {
                stepsHtml = `
                    <div class="flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-md text-indigo-800 font-medium truncate max-w-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        <a href="${s.current_page}" target="_blank" class="font-bold hover:underline text-indigo-600 truncate">${s.current_title}</a>
                    </div>
                `;
            }

            html += `
                <div class="bg-gray-50 hover:bg-indigo-50/40 transition-all border border-gray-200 rounded-xl p-4 space-y-3">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            ${flagImg}
                            <span class="font-bold text-sm text-gray-900">${locationText}</span>
                            <span class="bg-gray-200/80 text-gray-700 text-xs px-2 py-0.5 rounded font-medium">
                                ${deviceIcon} &bull; ${s.os} &bull; ${s.browser}
                            </span>
                            <span class="font-mono text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                ${s.ip_address}
                            </span>
                            <span class="text-xs text-gray-400">Via: ${s.referrer_domain}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-500 font-medium">⏱️ On Site: ${s.duration}</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold ${statusClass}">
                                <span class="w-1.5 h-1.5 rounded-full ${dotClass}"></span>
                                ${s.status.charAt(0).toUpperCase() + s.status.slice(1)}
                            </span>
                            <form method="POST" action="${BLOCK_IP_URL}" class="inline">
                                <input type="hidden" name="_token" value="${CSRF_TOKEN}">
                                <input type="hidden" name="ip_address" value="${s.ip_address}">
                                <button type="submit" onclick="return confirm('Block ${s.ip_address}?')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded text-[11px] font-bold transition-colors">
                                    Block
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200 text-xs">
                        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
                            🧭 Journey Progression (${s.page_views} page views):
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            ${stepsHtml}
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function renderStreamTicker(events) {
        const ticker = document.getElementById('live-stream-ticker');
        if (!ticker || !events || events.length === 0) return;

        let html = '';
        events.forEach(ev => {
            const flag = ev.country_code ? `<img src="https://flagcdn.com/w20/${ev.country_code}.png" class="w-4 h-auto rounded inline-block shadow-sm">` : '🌐';
            html += `
                <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-gray-400 font-mono text-[11px]">${ev.time}</span>
                        ${flag}
                        <span class="font-bold text-gray-800 text-xs">${ev.city ? ev.city + ', ' : ''}${ev.country}</span>
                        <span class="text-gray-400">&bull;</span>
                        <a href="${ev.url}" target="_blank" class="text-indigo-600 hover:underline truncate max-w-md font-medium">${ev.title}</a>
                    </div>
                    <div class="text-right whitespace-nowrap text-gray-400 text-[11px]">
                        ${ev.device} &bull; ${ev.ip}
                    </div>
                </div>
            `;
        });
        ticker.innerHTML = html;
    }

    function fetchLiveFeed() {
        if (isStreamPaused) return;

        fetch(LIVE_FEED_URL)
            .then(res => res.json())
            .then(data => {
                // Update live counters
                const counter = document.getElementById('live-user-count');
                if (counter && data.live_users !== undefined) counter.textContent = data.live_users;

                const joinedEl = document.getElementById('live-joined-count');
                if (joinedEl && data.joined_5m !== undefined) joinedEl.textContent = data.joined_5m;

                const leftEl = document.getElementById('live-left-count');
                if (leftEl && data.left_5m !== undefined) leftEl.textContent = data.left_5m;

                const clock = document.getElementById('live-stream-clock');
                if (clock && data.timestamp) clock.textContent = 'Live: ' + data.timestamp;

                // Update AI summary
                const aiEl = document.getElementById('ai-summary-text');
                if (aiEl && data.ai_summary && data.ai_summary.bn) {
                    aiEl.innerHTML = `<p class="font-medium bg-white/5 p-3 rounded-xl border border-white/5">${data.ai_summary.bn}</p>`;
                }

                // Threat Alert handling
                const threatBox = document.getElementById('threat-alert-box');
                const threatMsg = document.getElementById('threat-alert-message');
                const threatIp = document.getElementById('threat-ip-input');
                if (data.threat_alert) {
                    if (threatBox) threatBox.classList.remove('hidden');
                    if (threatMsg) threatMsg.textContent = data.threat_alert.message;
                    if (threatIp) threatIp.value = data.threat_alert.ip;
                } else {
                    if (threatBox) threatBox.classList.add('hidden');
                }

                // Render Active Sessions & Journeys
                renderJourneys(data.active_sessions);

                // Render Live Event Stream
                renderStreamTicker(data.live_events);

                // Update Live Log timestamp
                const logUpdated = document.getElementById('live-log-updated');
                if (logUpdated) {
                    logUpdated.textContent = 'Updated: ' + (data.timestamp || new Date().toLocaleTimeString());
                }
            })
            .catch(() => {});
    }

    // Polling every 3 seconds for fast, zero-reload real-time feel
    document.addEventListener("DOMContentLoaded", function() {
        fetchLiveFeed();
        streamTimer = setInterval(fetchLiveFeed, 3000);
    });
</script>
</x-app-layout>
