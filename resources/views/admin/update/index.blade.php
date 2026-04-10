<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Update (GitHub)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4 rounded-r">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
                <div class="p-6 md:p-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-2 space-y-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    Pull Latest Updates
                                </h3>
                                <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                                    This action will attempt to fetch and pull the latest code repository from your GitHub `main` branch. 
                                    It will also automatically flush the internal system cache and run any pending database migrations.
                                </p>

                                @if(isset($pendingCommits) && count($pendingCommits) > 0)
                                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                                        <h4 class="text-sm font-bold text-blue-800 mb-2">Updates Available:</h4>
                                        <ul class="text-sm text-blue-700 space-y-1 font-mono">
                                            @foreach($pendingCommits as $commit)
                                                <li>- {{ $commit }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-md">
                                        <p class="text-sm text-gray-600 font-medium flex items-center gap-2">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Your system is currently up to date.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r">
                                <h4 class="text-sm font-bold text-yellow-800">Deployment Requirements</h4>
                                <ul class="mt-1 text-sm text-yellow-700 list-disc list-inside space-y-1">
                                    <li>Your live server must have <code class="bg-white px-1 py-0.5 rounded text-xs">git</code> installed and available to the PHP process.</li>
                                    <li>Your server must be authenticated with GitHub (via SSH keys) or configured for password-less pulls.</li>
                                    <li>Running this will overwrite any uncommitted local file modifications.</li>
                                </ul>
                            </div>

                            <form action="{{ route('admin.update.process') }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
                                @csrf
                                <button type="submit" :disabled="loading" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                    <svg x-show="!loading" class="w-5 h-5 mr-3 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <svg x-show="loading" style="display: none;" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-show="!loading">Execute Git Pull & Update System</span>
                                    <span x-show="loading" style="display:none;">Updating Framework...</span>
                                </button>
                            </form>
                        </div>
                        
                        <div class="md:col-span-1 hidden md:flex items-center justify-center bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <div class="text-center p-6 text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" /></svg>
                                <p class="text-xs tracking-widest uppercase font-semibold">GitHub Linked</p>
                            </div>
                        </div>
                    </div>

                </div>

                @if(session('update_log'))
                <div class="border-t border-gray-200">
                    <div class="bg-gray-900 p-4 flex items-center justify-between border-b border-gray-800">
                        <h4 class="text-gray-300 text-sm font-mono flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Terminal Output Log
                        </h4>
                    </div>
                    <div class="bg-black p-6 overflow-x-auto">
                        <pre class="text-green-400 font-mono text-sm whitespace-pre-wrap">{{ session('update_log') }}</pre>
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
</x-app-layout>
