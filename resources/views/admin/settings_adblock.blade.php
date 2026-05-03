<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AdBlock Detection & Prevention') }}
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
                
                <div class="bg-white shadow-sm sm:rounded-lg mb-6 overflow-hidden">
                    <div class="p-6">
                        <div class="mb-8 border-b pb-4">
                            <h3 class="text-lg font-bold text-gray-900">AdBlock Shield Configuration</h3>
                            <p class="text-sm text-gray-500">Protect your revenue by detecting and blocking users who use ad-blocking software.</p>
                        </div>

                        <div class="space-y-8">
                            <!-- Toggle -->
                            <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                                <div>
                                    <h4 class="font-bold text-indigo-900">Enable AdBlock Detection</h4>
                                    <p class="text-xs text-indigo-700">When enabled, users with AdBlock will see a lock screen over post content.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="adblock_detection_enabled" value="0">
                                    <input type="checkbox" name="adblock_detection_enabled" value="1" {{ ($settings['adblock_detection_enabled'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Modal Content -->
                                <div class="space-y-4">
                                    <h4 class="font-bold text-gray-700 border-b pb-2">Lock Screen Message</h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Modal Title</label>
                                        <input type="text" name="adblock_message_title" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm" value="{{ $settings['adblock_message_title'] ?? 'AdBlock Detected!' }}">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Modal Message</label>
                                        <textarea name="adblock_message_body" rows="4" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm">{{ $settings['adblock_message_body'] ?? "We've detected that you are using an ad blocker. Please disable it or whitelist our site to continue reading this post. We rely on ads to keep our content free!" }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Refresh Button Text</label>
                                        <input type="text" name="adblock_refresh_text" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm" value="{{ $settings['adblock_refresh_text'] ?? "I've disabled it, Refresh" }}">
                                    </div>
                                </div>

                                <!-- Advanced Settings -->
                                <div class="space-y-4">
                                    <h4 class="font-bold text-gray-700 border-b pb-2">Behavior & Style</h4>
                                    
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div>
                                            <h5 class="text-sm font-bold text-gray-700">Content Blur Effect</h5>
                                            <p class="text-xs text-gray-500">Blur the background content when blocked.</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="hidden" name="adblock_blur_enabled" value="0">
                                            <input type="checkbox" name="adblock_blur_enabled" value="1" {{ ($settings['adblock_blur_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Detection Delay (ms)</label>
                                        <input type="number" name="adblock_delay" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm" value="{{ $settings['adblock_delay'] ?? '1000' }}" placeholder="e.g. 1000">
                                        <p class="text-xs text-gray-500 mt-1">Wait before showing the block modal. 1000ms = 1 second.</p>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <h5 class="text-xs font-bold text-gray-500 uppercase mb-3">Preview Preview Tip</h5>
                                        <p class="text-xs text-gray-600 leading-relaxed">
                                            The AdBlock shield uses a modern <strong>Glassmorphism</strong> design with a frosted glass effect and smooth animations to provide a premium user experience while enforcing your rules.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-bold shadow-lg hover:bg-indigo-700 transition">
                        Save AdBlock Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
