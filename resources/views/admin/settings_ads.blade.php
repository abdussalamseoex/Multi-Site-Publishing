<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ads & Monetization Control Panel') }}
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
                
                <div class="bg-white shadow-sm sm:rounded-lg mb-6 overflow-hidden" x-data="{ activeTab: 'units' }">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex text-sm font-medium" aria-label="Tabs">
                            <button type="button" @click="activeTab = 'units'" 
                                :class="activeTab === 'units' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/2 border-b-2 py-4 px-1 text-center font-bold transition-colors">
                                1. Ad Units Setup (Paste Codes)
                            </button>
                            <button type="button" @click="activeTab = 'placements'" 
                                :class="activeTab === 'placements' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/2 border-b-2 py-4 px-1 text-center font-bold transition-colors">
                                2. Placements & Display Rules
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- AD UNITS TAB -->
                        <div x-show="activeTab === 'units'">
                            <div class="mb-6 border-b pb-4">
                                <h3 class="text-lg font-bold text-gray-900">Configure Ad Units</h3>
                                <p class="text-sm text-gray-500">Paste the HTML/JS codes for each specific ad size from your network (e.g., AdsTerra, AdSense). Only fill out the ones you want to use.</p>
                            </div>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Global / Script Ads -->
                                <div class="space-y-4 bg-gray-50 p-4 rounded border">
                                    <h4 class="font-bold text-gray-700 border-b pb-2">Global & Background Ads</h4>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Popunder Script</label>
                                        <textarea name="ad_code_popunder" rows="2" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_popunder'] ?? '' }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Social Bar Script</label>
                                        <textarea name="ad_code_socialbar" rows="2" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_socialbar'] ?? '' }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Smartlink / Direct Link URL</label>
                                        <input type="text" name="ad_code_smartlink" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs" value="{{ $settings['ad_code_smartlink'] ?? '' }}" placeholder="https://...">
                                    </div>
                                </div>

                                <!-- Native & Special -->
                                <div class="space-y-4 bg-gray-50 p-4 rounded border">
                                    <h4 class="font-bold text-gray-700 border-b pb-2">Native & Content Ads</h4>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Native Banner HTML</label>
                                        <textarea name="ad_code_native" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_native'] ?? '' }}</textarea>
                                    </div>
                                </div>

                                <!-- Banner Sizes Left -->
                                <div class="space-y-4 bg-gray-50 p-4 rounded border">
                                    <h4 class="font-bold text-gray-700 border-b pb-2">Standard Banners (Horizontal)</h4>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Banner 728x90 (Desktop Header)</label>
                                        <textarea name="ad_code_728x90" rows="2" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_728x90'] ?? '' }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Banner 468x60 (In-Article / Header)</label>
                                        <textarea name="ad_code_468x60" rows="2" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_468x60'] ?? '' }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Banner 320x50 (Mobile / Footer)</label>
                                        <textarea name="ad_code_320x50" rows="2" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_320x50'] ?? '' }}</textarea>
                                    </div>
                                </div>

                                <!-- Banner Sizes Right -->
                                <div class="space-y-4 bg-gray-50 p-4 rounded border">
                                    <h4 class="font-bold text-gray-700 border-b pb-2">Standard Banners (Vertical/Square)</h4>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Banner 160x600 (Skyscraper / Floating)</label>
                                        <textarea name="ad_code_160x600" rows="2" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_160x600'] ?? '' }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Banner 160x300 (Half Skyscraper)</label>
                                        <textarea name="ad_code_160x300" rows="2" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_160x300'] ?? '' }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Banner 300x250 (Sidebar / Footer Rectangle)</label>
                                        <textarea name="ad_code_300x250" rows="2" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ad_code_300x250'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PLACEMENTS TAB -->
                        <div x-show="activeTab === 'placements'" style="display: none;">
                            <div class="mb-6 border-b pb-4">
                                <h3 class="text-lg font-bold text-gray-900">Ad Placements & UI Logic</h3>
                                <p class="text-sm text-gray-500">Determine exactly where the ads you configured in step 1 will show up on your website. Setting a placement to "Disabled" will safely hide it without breaking the UI.</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                
                                <!-- Core Layout Slots -->
                                <div>
                                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Core Layout Slots</h4>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Header Ad Slot</label>
                                            <select name="ad_placement_header" class="block w-full border-gray-300 rounded-md sm:text-sm bg-gray-50">
                                                <option value="" {{ empty($settings['ad_placement_header']) ? 'selected' : '' }}>Disabled (No Ad)</option>
                                                <option value="ad_code_728x90" {{ ($settings['ad_placement_header'] ?? '') == 'ad_code_728x90' ? 'selected' : '' }}>Banner 728x90</option>
                                                <option value="ad_code_468x60" {{ ($settings['ad_placement_header'] ?? '') == 'ad_code_468x60' ? 'selected' : '' }}>Banner 468x60</option>
                                                <option value="ad_code_native" {{ ($settings['ad_placement_header'] ?? '') == 'ad_code_native' ? 'selected' : '' }}>Native Banner</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Footer Ad Slot</label>
                                            <select name="ad_placement_footer" class="block w-full border-gray-300 rounded-md sm:text-sm bg-gray-50">
                                                <option value="" {{ empty($settings['ad_placement_footer']) ? 'selected' : '' }}>Disabled (No Ad)</option>
                                                <option value="ad_code_728x90" {{ ($settings['ad_placement_footer'] ?? '') == 'ad_code_728x90' ? 'selected' : '' }}>Banner 728x90</option>
                                                <option value="ad_code_300x250" {{ ($settings['ad_placement_footer'] ?? '') == 'ad_code_300x250' ? 'selected' : '' }}>Banner 300x250</option>
                                                <option value="ad_code_320x50" {{ ($settings['ad_placement_footer'] ?? '') == 'ad_code_320x50' ? 'selected' : '' }}>Banner 320x50</option>
                                                <option value="ad_code_native" {{ ($settings['ad_placement_footer'] ?? '') == 'ad_code_native' ? 'selected' : '' }}>Native Banner</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Theme Right Sidebar Ad</label>
                                            <select name="ad_placement_sidebar" class="block w-full border-gray-300 rounded-md sm:text-sm bg-gray-50">
                                                <option value="" {{ empty($settings['ad_placement_sidebar']) ? 'selected' : '' }}>Disabled (No Ad)</option>
                                                <option value="ad_code_300x250" {{ ($settings['ad_placement_sidebar'] ?? '') == 'ad_code_300x250' ? 'selected' : '' }}>Banner 300x250</option>
                                                <option value="ad_code_160x600" {{ ($settings['ad_placement_sidebar'] ?? '') == 'ad_code_160x600' ? 'selected' : '' }}>Banner 160x600</option>
                                                <option value="ad_code_160x300" {{ ($settings['ad_placement_sidebar'] ?? '') == 'ad_code_160x300' ? 'selected' : '' }}>Banner 160x300</option>
                                                <option value="ad_code_native" {{ ($settings['ad_placement_sidebar'] ?? '') == 'ad_code_native' ? 'selected' : '' }}>Native Banner</option>
                                            </select>
                                            <p class="text-xs text-gray-500 mt-1">Shows inside the default sidebar for themes like News, Magazine, Blog.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Post In-Article Ads -->
                                <div>
                                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Inside Post Content (In-Article)</h4>
                                    
                                    <div class="space-y-4 border p-4 rounded bg-indigo-50 border-indigo-100">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">In-Article Ad Slot</label>
                                            <select name="ad_placement_in_article" class="block w-full border-gray-300 rounded-md sm:text-sm font-bold text-indigo-700">
                                                <option value="" {{ empty($settings['ad_placement_in_article']) ? 'selected' : '' }}>Disabled (No In-Article Ads)</option>
                                                <option value="ad_code_native" {{ ($settings['ad_placement_in_article'] ?? '') == 'ad_code_native' ? 'selected' : '' }}>Native Banner</option>
                                                <option value="ad_code_468x60" {{ ($settings['ad_placement_in_article'] ?? '') == 'ad_code_468x60' ? 'selected' : '' }}>Banner 468x60</option>
                                                <option value="ad_code_300x250" {{ ($settings['ad_placement_in_article'] ?? '') == 'ad_code_300x250' ? 'selected' : '' }}>Banner 300x250</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Ad Frequency (Paragraphs)</label>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-gray-600">Show 1 ad every</span>
                                                <input type="number" name="ad_in_article_frequency" class="w-20 border-gray-300 rounded-md shadow-sm sm:text-sm text-center" value="{{ $settings['ad_in_article_frequency'] ?? '3' }}" min="1" max="10">
                                                <span class="text-sm text-gray-600">paragraphs.</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">The system will dynamically calculate and inject the ad without breaking the post text!</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Floating & Background Ads -->
                                <div>
                                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Floating Ads (Both Sides)</h4>
                                    <p class="text-xs text-gray-500 mb-3">These stick to the left and right sides of the screen on Desktop devices. Ideal for 160x600 banners.</p>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Floating Left Slot</label>
                                            <select name="ad_placement_floating_left" class="block w-full border-gray-300 rounded-md sm:text-sm bg-gray-50">
                                                <option value="" {{ empty($settings['ad_placement_floating_left']) ? 'selected' : '' }}>Disabled (No Ad)</option>
                                                <option value="ad_code_160x600" {{ ($settings['ad_placement_floating_left'] ?? '') == 'ad_code_160x600' ? 'selected' : '' }}>Banner 160x600</option>
                                                <option value="ad_code_160x300" {{ ($settings['ad_placement_floating_left'] ?? '') == 'ad_code_160x300' ? 'selected' : '' }}>Banner 160x300</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Floating Right Slot</label>
                                            <select name="ad_placement_floating_right" class="block w-full border-gray-300 rounded-md sm:text-sm bg-gray-50">
                                                <option value="" {{ empty($settings['ad_placement_floating_right']) ? 'selected' : '' }}>Disabled (No Ad)</option>
                                                <option value="ad_code_160x600" {{ ($settings['ad_placement_floating_right'] ?? '') == 'ad_code_160x600' ? 'selected' : '' }}>Banner 160x600</option>
                                                <option value="ad_code_160x300" {{ ($settings['ad_placement_floating_right'] ?? '') == 'ad_code_160x300' ? 'selected' : '' }}>Banner 160x300</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Background Scripts -->
                                <div>
                                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Background Scripts Toggle</h4>
                                    
                                    <div class="space-y-3">
                                        <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                                            <input type="hidden" name="enable_popunder" value="0">
                                            <input type="checkbox" name="enable_popunder" value="1" {{ ($settings['enable_popunder'] ?? '0') == '1' ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500">
                                            <span class="font-medium text-gray-700 text-sm">Enable Popunder Script (Site-wide)</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                                            <input type="hidden" name="enable_socialbar" value="0">
                                            <input type="checkbox" name="enable_socialbar" value="1" {{ ($settings['enable_socialbar'] ?? '0') == '1' ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500">
                                            <span class="font-medium text-gray-700 text-sm">Enable Social Bar Script (Site-wide)</span>
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-bold shadow-lg hover:bg-indigo-700 transition">
                        Save Monetization Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
