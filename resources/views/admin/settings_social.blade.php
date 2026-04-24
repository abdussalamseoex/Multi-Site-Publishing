<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Social & Contact Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.settings.store') }}" method="POST">
                @csrf
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Floating Contact Buttons</h3>
                            <p class="text-xs text-gray-500 mt-1">Add your contact details to show floating chat buttons at the bottom right of the website.</p>
                        </div>
                    </div>
                    
                    <div class="p-6 text-gray-900 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">WhatsApp Number</label>
                                <input type="text" name="social_whatsapp" value="{{ $settings['social_whatsapp'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. 8801700000000">
                                <p class="text-xs text-gray-500 mt-1">Enter your number with the country code but without the plus (+) sign.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">Telegram Link or Username</label>
                                <input type="text" name="social_telegram" value="{{ $settings['social_telegram'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. https://t.me/username">
                                <p class="text-xs text-gray-500 mt-1">Full Telegram URL or username.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">Facebook Messenger Link</label>
                                <input type="text" name="social_messenger" value="{{ $settings['social_messenger'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. https://m.me/pagename">
                                <p class="text-xs text-gray-500 mt-1">Full Messenger URL.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">WhatsApp Group Link</label>
                                <input type="text" name="social_whatsapp_group" value="{{ $settings['social_whatsapp_group'] ?? '' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. https://chat.whatsapp.com/...">
                                <p class="text-xs text-gray-500 mt-1">Provide this to enable the "Join Free Group" popup option on the WhatsApp button.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mb-12">
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-md shadow uppercase font-bold tracking-wider hover:bg-indigo-700 transition transform hover:-translate-y-0.5">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
