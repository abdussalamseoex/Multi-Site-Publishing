<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        {!! \App\Models\Setting::get('custom_header_scripts', '') !!}
</head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50 overflow-hidden">
        <div class="flex h-screen" x-data="{ sidebarOpen: false }">
            
            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen" class="fixed inset-0 z-20 transition-opacity bg-black bg-opacity-50 lg:hidden" @click="sidebarOpen = false" x-transition.opacity></div>

            <!-- Left Sidebar -->
            @include('layouts.sidebar')

            <!-- Right Side: Navbar + Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden w-full relative">
                
                <!-- Top Navbar -->
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow border-b border-gray-100 z-10 sticky top-0">
                        <div class="py-4 px-6 md:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Main Page Content -->
                <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-gray-50 relative pb-20">
                    {{ $slot }}
                </main>
                
            </div>
        </div>
        {!! \App\Models\Setting::get('custom_footer_scripts', '') !!}
        @stack('scripts')
        <script>
            // Pseudo-cron to trigger scheduled tasks (like Auto News) in the background without needing cPanel Cron
            setTimeout(function() {
                fetch('{{ url('/system/pseudo-cron') }}').catch(function() {});
            }, 3000);
        </script>
    </body>
</html>


