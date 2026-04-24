@php
    $whatsapp = \App\Models\Setting::get('social_whatsapp');
    $whatsappGroup = \App\Models\Setting::get('social_whatsapp_group');
    $telegram = \App\Models\Setting::get('social_telegram');
    $messenger = \App\Models\Setting::get('social_messenger');
    $siteName = \App\Models\Setting::get('site_title', config('app.name'));
    $waText = urlencode("Hello, I am contacting you from {$siteName}");
@endphp

@if($whatsapp || $whatsappGroup || $telegram || $messenger)
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-center gap-3">
        
        @if($messenger)
            <a href="{{ $messenger }}" target="_blank" rel="noopener noreferrer" 
               class="w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center text-white shadow-lg hover:shadow-2xl hover:scale-110 transition-transform duration-300 relative group">
                <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.477 2 2 6.145 2 11.258c0 2.923 1.498 5.496 3.824 7.114V22l3.494-1.916c.854.238 1.75.366 2.682.366 5.523 0 10-4.145 10-9.258S17.523 2 12 2zm1.188 12.35l-3.003-3.21-5.856 3.21 6.425-6.836 3.067 3.21 5.79-3.21-6.423 6.836z"/></svg>
                <span class="absolute right-full mr-3 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity pointer-events-none">Messenger</span>
            </a>
        @endif

        @if($telegram)
            <a href="{{ $telegram }}" target="_blank" rel="noopener noreferrer" 
               class="w-14 h-14 bg-blue-400 rounded-full flex items-center justify-center text-white shadow-lg hover:shadow-2xl hover:scale-110 transition-transform duration-300 relative group">
                <svg class="w-7 h-7 fill-current ml-[-2px] mt-[2px]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.223-.548.223l.188-2.623 4.823-4.35c.132-.132-.045-.213-.245-.078l-5.962 3.754-2.553-.8c-.553-.173-.564-.555.115-.82l9.966-3.84c.46-.17.87.114.716.852z"/></svg>
                <span class="absolute right-full mr-3 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity pointer-events-none">Telegram</span>
            </a>
        @endif

        @if($whatsapp && $whatsappGroup)
            <div class="relative group" x-data="{ open: false }">
                <!-- Popup Card -->
                <div class="absolute bottom-full right-0 mb-4 bg-white rounded-xl shadow-2xl border border-gray-100 p-2 w-64 transform transition-all duration-300 origin-bottom-right scale-0 opacity-0 group-hover:scale-100 group-hover:opacity-100"
                     :class="open ? 'scale-100 opacity-100' : 'scale-0 opacity-0'">
                    
                    <div class="px-3 py-2 bg-green-50 rounded-lg mb-2">
                        <h4 class="font-bold text-green-800 text-sm">How can we help?</h4>
                        <p class="text-xs text-green-600">Choose an option below</p>
                    </div>

                    <div class="space-y-1">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text={{ $waText }}" target="_blank" rel="noopener noreferrer" 
                           class="flex items-center gap-3 w-full p-2 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Chat with Us</p>
                                <p class="text-xs text-gray-500">Buy guest posts</p>
                            </div>
                        </a>

                        <a href="{{ $whatsappGroup }}" target="_blank" rel="noopener noreferrer" 
                           class="flex items-center gap-3 w-full p-2 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Join Free Group</p>
                                <p class="text-xs text-gray-500">Daily free websites</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Main Button -->
                <button @click="open = !open" 
                   class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg hover:shadow-2xl hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                </button>
            </div>
        @elseif($whatsapp)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text={{ $waText }}" target="_blank" rel="noopener noreferrer" 
               class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg hover:shadow-2xl hover:scale-110 transition-transform duration-300 relative group">
                <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                <span class="absolute right-full mr-3 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity pointer-events-none">WhatsApp</span>
            </a>
        @elseif($whatsappGroup)
            <a href="{{ $whatsappGroup }}" target="_blank" rel="noopener noreferrer" 
               class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg hover:shadow-2xl hover:scale-110 transition-transform duration-300 relative group">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="absolute right-full mr-3 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity pointer-events-none">Join Group</span>
            </a>
        @endif

    </div>
@endif
