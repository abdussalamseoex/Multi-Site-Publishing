<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Advanced Analytics & Traffic') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.analytics.export', ['date_filter' => $filter]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow-sm text-sm font-bold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export CSV
                </a>
                <form method="GET" action="{{ route('admin.analytics.index') }}" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-md shadow-sm border border-gray-200">
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

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Key Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Live Traffic -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-6 rounded-xl shadow-lg border border-indigo-400 text-white flex items-center justify-between transform transition hover:scale-105">
                    <div>
                        <div class="text-indigo-100 text-xs font-bold uppercase tracking-wider mb-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                            Live Now
                        </div>
                        <div class="text-4xl font-black">{{ number_format($liveUsers) }}</div>
                    </div>
                    <div class="p-3 bg-white/20 rounded-lg backdrop-blur-sm">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                </div>

                <!-- Total Traffic Filtered -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">
                            Page Views (@if($filter === 'all_time') All Time @else Filtered @endif)
                        </div>
                        <div class="text-3xl font-black text-gray-800">{{ number_format($filteredTotal) }}</div>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg text-blue-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                </div>

                <!-- Bounce Rate -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Bounce Rate</div>
                        <div class="text-3xl font-black text-gray-800">{{ $bounceRate }}%</div>
                    </div>
                    <div class="p-3 bg-red-50 rounded-lg text-red-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                </div>

                <!-- Bot Traffic -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Bot Traffic</div>
                        <div class="text-3xl font-black text-gray-800">{{ number_format($botCount) }}</div>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-lg text-gray-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Live Map -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Global Visitor Map
                    </h3>
                    <div id="vmap" style="width: 100%; height: 350px;"></div>
                </div>

                <!-- Devices & Browsers -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col gap-6">
                    <div>
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider border-b pb-2 mb-3">Devices</h3>
                        <div class="space-y-3">
                            @foreach($devices as $device => $count)
                            @if($count > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-gray-700 flex items-center gap-2">
                                    @if($device === 'Desktop') <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @endif
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

            <!-- More Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Top Pages -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
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

                <!-- Bot List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-red-600 border-b pb-3 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Detected Bots
                    </h3>
                    @if(empty($bots))
                        <p class="text-gray-500 text-sm">No bots detected recently.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($bots as $bot => $count)
                            <li class="flex justify-between items-center bg-red-50 p-3 rounded-lg border border-red-100">
                                <span class="font-bold text-red-700 text-sm">{{ $bot }}</span>
                                <span class="bg-white text-red-600 px-3 py-1 rounded-full text-xs font-bold shadow-sm">{{ number_format($count) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Traffic Sources & Referrers Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Countries -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
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

            <!-- Recent Visits Logs -->
            <div class="bg-white rounded-xl shadow border border-gray-100 p-6 overflow-hidden">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Live Real-time Log (Last 50 Visitors)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Visitor</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Page Visited</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Referrer</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($recentVisits as $v)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $v->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 mb-1">
                                        @if($v->country_code)
                                        <img src="https://flagcdn.com/w20/{{ strtolower($v->country_code) }}.png" alt="{{ $v->country }}" class="w-5 h-auto rounded shadow-sm inline-block">
                                        @endif
                                        <span class="text-sm font-bold text-gray-900">{{ $v->country }}</span>
                                    </div>
                                    <div class="text-xs text-indigo-500 font-mono bg-indigo-50 px-2 py-0.5 rounded inline-block">{{ $v->ip_address }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-sm">
                                    <a href="{{ $v->url }}" target="_blank" class="hover:text-indigo-600 transition-colors block truncate">{{ str_replace(url('/'), '', $v->url) ?: '/' }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    @if($v->referrer)
                                        <a href="{{ $v->referrer }}" target="_blank" class="text-gray-500 hover:text-indigo-600 transition-colors">{{ Str::limit($v->referrer, 30) }}</a>
                                    @else
                                        <span class="text-gray-400 italic">Direct / Unknown</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<!-- Load jsVectorMap CSS and Scripts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css" />
<script src="https://cdn.jsdelivr.net/npm/jsvectormap"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Data mapping to country codes
        const countryData = {};
        @foreach($topCountries as $country)
            @if($country->country_code)
                countryData['{{ strtoupper($country->country_code) }}'] = {{ $country->count }};
            @endif
        @endforeach

        if (document.getElementById('vmap')) {
            const map = new jsVectorMap({
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
                        fill: '#f3f4f6', // gray-100 equivalent
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
                        true // html rendering
                    );
                }
            });
        }
    });
</script>
