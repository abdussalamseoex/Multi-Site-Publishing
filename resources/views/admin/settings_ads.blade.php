<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ads & Monetization') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.settings.store') }}" method="POST">
                @csrf
                
                <div class="bg-white shadow-sm sm:rounded-lg mb-6 overflow-hidden" x-data="{ activeTab: 'adsense' }">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex text-sm font-medium" aria-label="Tabs">
                            <button type="button" @click="activeTab = 'adsense'" 
                                :class="activeTab === 'adsense' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/4 border-b-2 py-4 px-1 text-center font-bold transition-colors">
                                Google AdSense
                            </button>
                            <button type="button" @click="activeTab = 'adsterra'" 
                                :class="activeTab === 'adsterra' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/4 border-b-2 py-4 px-1 text-center font-bold transition-colors">
                                AdsTerra
                            </button>
                            <button type="button" @click="activeTab = 'medianet'" 
                                :class="activeTab === 'medianet' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/4 border-b-2 py-4 px-1 text-center font-bold transition-colors">
                                Media.net
                            </button>
                            <button type="button" @click="activeTab = 'custom'" 
                                :class="activeTab === 'custom' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/4 border-b-2 py-4 px-1 text-center font-bold transition-colors">
                                Custom Scripts
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- Google AdSense -->
                        <div x-show="activeTab === 'adsense'">
                            <div class="flex items-center gap-3 mb-6 border-b pb-4">
                                <div class="w-10 h-10 bg-yellow-100 text-yellow-600 flex items-center justify-center rounded-full font-bold">G</div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Google AdSense</h3>
                                    <p class="text-sm text-gray-500">Paste your AdSense auto-ads code or manual ad unit codes below.</p>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Auto Ads Header Script</label>
                                    <textarea name="adsense_header_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<script async src='https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-xxx' crossorigin='anonymous'></script>">{{ $settings['adsense_header_code'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sidebar Ad Unit</label>
                                    <textarea name="adsense_sidebar_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<ins class='adsbygoogle' ...></ins>">{{ $settings['adsense_sidebar_code'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">In-Article Ad Unit</label>
                                    <textarea name="adsense_content_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<ins class='adsbygoogle' ...></ins>">{{ $settings['adsense_content_code'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- AdsTerra -->
                        <div x-show="activeTab === 'adsterra'" style="display: none;">
                            <div class="flex items-center gap-3 mb-6 border-b pb-4">
                                <div class="w-10 h-10 bg-blue-100 text-blue-600 flex items-center justify-center rounded-full font-bold">A</div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">AdsTerra Monetization</h3>
                                    <p class="text-sm text-gray-500">Integrate AdsTerra's high-paying ad formats like Native Banners and Social Bars.</p>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Social Bar / Popunder Script (Header)</label>
                                    <textarea name="adsterra_header_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<script type='text/javascript' src='//your-adsterra-code.js'></script>">{{ $settings['adsterra_header_code'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Native Banner (Sidebar Area)</label>
                                    <textarea name="adsterra_sidebar_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<div id='container-xxx'></div>">{{ $settings['adsterra_sidebar_code'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Native Banner (In-Article Area)</label>
                                    <textarea name="adsterra_content_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<div id='container-xxx'></div>">{{ $settings['adsterra_content_code'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Media.net -->
                        <div x-show="activeTab === 'medianet'" style="display: none;">
                            <div class="flex items-center gap-3 mb-6 border-b pb-4">
                                <div class="w-10 h-10 bg-red-100 text-red-600 flex items-center justify-center rounded-full font-bold">M</div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Media.net Settings</h3>
                                    <p class="text-sm text-gray-500">Add context-driven ads from Media.net for alternative monetization.</p>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Header Script</label>
                                    <textarea name="medianet_header_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<script type='text/javascript'>...</script>">{{ $settings['medianet_header_code'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sidebar Ad Unit</label>
                                    <textarea name="medianet_sidebar_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<div id='...'></div>">{{ $settings['medianet_sidebar_code'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">In-Article Ad Unit</label>
                                    <textarea name="medianet_content_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<div id='...'></div>">{{ $settings['medianet_content_code'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Scripts -->
                        <div x-show="activeTab === 'custom'" style="display: none;">
                            <div class="flex items-center gap-3 mb-6 border-b pb-4">
                                <div class="w-10 h-10 bg-gray-100 text-gray-600 flex items-center justify-center rounded-full font-bold">&lt;&gt;</div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Custom / Fallback Scripts</h3>
                                    <p class="text-sm text-gray-500">Legacy ad codes or custom network placements. Will act as fallback.</p>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Global Sidebar Ad Code (Legacy)</label>
                                    <textarea name="ad_sidebar_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<script async src='...'></script>">{{ $settings['ad_sidebar_code'] ?? '' }}</textarea>
                                    <p class="text-xs text-gray-500 mt-1">If AdsTerra/AdSense sidebar is empty, this will show.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Global Header/Top Banner Code</label>
                                    <textarea name="ad_header_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<script async src='...'></script>">{{ $settings['ad_header_code'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Global In-Article Ad Code (Legacy)</label>
                                    <textarea name="ad_content_code" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" placeholder="<script async src='...'></script>">{{ $settings['ad_content_code'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Ad Network Selection -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Active Ad Network</h3>
                    <p class="text-sm text-gray-600 mb-4">Select which network to primarily display across the site frontend. You must enter the codes above for your selection to work.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <label class="border p-4 rounded-lg cursor-pointer hover:bg-gray-50 transition flex items-center gap-3">
                            <input type="radio" name="active_ad_network" value="adsense" {{ ($settings['active_ad_network'] ?? 'adsense') === 'adsense' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            <span class="font-bold text-gray-800">Google AdSense</span>
                        </label>
                        <label class="border p-4 rounded-lg cursor-pointer hover:bg-gray-50 transition flex items-center gap-3">
                            <input type="radio" name="active_ad_network" value="adsterra" {{ ($settings['active_ad_network'] ?? '') === 'adsterra' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            <span class="font-bold text-gray-800">AdsTerra</span>
                        </label>
                        <label class="border p-4 rounded-lg cursor-pointer hover:bg-gray-50 transition flex items-center gap-3">
                            <input type="radio" name="active_ad_network" value="medianet" {{ ($settings['active_ad_network'] ?? '') === 'medianet' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            <span class="font-bold text-gray-800">Media.net</span>
                        </label>
                        <label class="border p-4 rounded-lg cursor-pointer hover:bg-gray-50 transition flex items-center gap-3">
                            <input type="radio" name="active_ad_network" value="custom" {{ ($settings['active_ad_network'] ?? '') === 'custom' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            <span class="font-bold text-gray-800">Custom / Legacy</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded font-medium shadow hover:bg-indigo-700">
                        Save Ad Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
