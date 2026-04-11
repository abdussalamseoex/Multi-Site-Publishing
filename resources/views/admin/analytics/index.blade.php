<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Live Traffic & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Stats -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-sm border-b pb-2 mb-2 text-gray-500 font-bold uppercase tracking-wider">Total Traffic (All Time)</div>
                        <div class="text-3xl font-black text-gray-900">{{ number_format($totalVisits) }}</div>
                    </div>
                    <div class="p-4 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-sm border-b pb-2 mb-2 text-gray-500 font-bold uppercase tracking-wider">Traffic Today</div>
                        <div class="text-3xl font-black text-green-600">{{ number_format($visitsToday) }}</div>
                    </div>
                    <div class="p-4 bg-green-50 text-green-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Countries -->
                <div class="bg-white sm:rounded-lg shadow p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Top Countries</h3>
                    @if($topCountries->isEmpty())
                        <p class="text-gray-500">No data available.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($topCountries as $country)
                            <li class="flex justify-between items-center bg-gray-50 p-3 rounded border border-gray-100">
                                <span class="font-bold text-gray-700">{{ $country->country }}</span>
                                <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold">{{ number_format($country->count) }} Views</span>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Top Referrers -->
                <div class="bg-white sm:rounded-lg shadow p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Top Referrers</h3>
                    @if($topReferrers->isEmpty())
                        <p class="text-gray-500">No data available.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($topReferrers as $ref)
                            @php
                                $domain = parse_url($ref->referrer, PHP_URL_HOST) ?? $ref->referrer;
                            @endphp
                            <li class="flex justify-between items-center bg-gray-50 p-3 rounded border border-gray-100 truncate flex-wrap gap-2">
                                <a href="{{ $ref->referrer }}" target="_blank" class="font-bold text-gray-700 hover:text-indigo-600 truncate max-w-xs">{{ $domain }}</a>
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">{{ number_format($ref->count) }} Clicks</span>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Recent Visits Logs -->
            <div class="bg-white rounded-lg shadow border border-gray-100 p-6 mt-6 overflow-x-auto">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4">Live Real-time Log (Last 50 Visitors)</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP / Country</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page Visited</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referrer</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentVisits as $v)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $v->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $v->country }}</div>
                                <div class="text-xs text-gray-500">{{ $v->ip_address }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-sm truncate">
                                <a href="{{ $v->url }}" target="_blank" class="text-indigo-600 hover:underline">{{ Str::limit($v->url, 50) }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                @if($v->referrer)
                                    <a href="{{ $v->referrer }}" target="_blank" class="text-gray-500 hover:underline">{{ Str::limit($v->referrer, 30) }}</a>
                                @else
                                    <span class="text-gray-400">Direct / Unknown</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
