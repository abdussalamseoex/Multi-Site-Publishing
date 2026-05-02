<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AI Automation Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.ai-writer.settings.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 gap-6">
                    
                    <!-- AI API Keys Settings -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-teal-400">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                            <h3 class="text-lg font-medium text-gray-900">API Keys</h3>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">Configure the API keys required for AI Content Generation and Auto Image Fetching.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">OpenAI API Key</label>
                                <input type="password" name="openai_api_key" value="{{ $settings['openai_api_key'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="sk-...">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Pexels API Key</label>
                                <input type="password" name="pexels_api_key" value="{{ $settings['pexels_api_key'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Unsplash Access Key</label>
                                <input type="password" name="unsplash_api_key" value="{{ $settings['unsplash_api_key'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="p-4 bg-blue-50 border border-blue-100 rounded-lg space-y-4">
                                <h4 class="text-blue-900 font-bold text-xs uppercase">Google Search API (For Images & Real-time Links)</h4>
                                <div>
                                    <label class="block font-medium text-xs text-gray-500 uppercase">Google API Key</label>
                                    <input type="password" name="google_search_api_key" value="{{ $settings['google_search_api_key'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label class="block font-medium text-xs text-gray-500 uppercase">Search Engine ID (CX)</label>
                                    <input type="text" name="google_search_engine_id" value="{{ $settings['google_search_engine_id'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Prompt Settings -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-400">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <h3 class="text-lg font-medium text-gray-900">Custom Prompts</h3>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">Modify the default instructions sent to OpenAI. Make sure to keep the required JSON formatting and variables intact.</p>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">AI Bulk Writer Prompt</label>
                                <p class="text-xs text-gray-500 mb-2">Available Variables: <code>{keyword}</code>, <code>{language}</code>, <code>{image_instruction}</code></p>
                                <textarea name="ai_writer_prompt" rows="8" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ai_writer_prompt'] ?? $defaultWriterPrompt }}</textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Auto News Fetcher Prompt</label>
                                <p class="text-xs text-gray-500 mb-2">Available Variables: <code>{title}</code>, <code>{context}</code>, <code>{link}</code>, <code>{image_instruction}</code></p>
                                <textarea name="ai_news_prompt" rows="8" class="w-full border-gray-300 rounded-md shadow-sm sm:text-sm font-mono text-xs">{{ $settings['ai_news_prompt'] ?? $defaultNewsPrompt }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-6">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded font-medium shadow hover:bg-indigo-700">
                        Save AI Settings
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
