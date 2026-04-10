<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard - Admin Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">System Installation</h1>
            <p class="text-gray-500 mt-2">Step 2: Site & Admin Account</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 text-red-500 p-4 rounded-lg mb-6 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('install.processInstallation') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Site Name</label>
                    <input type="text" name="site_name" required class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="My Publishing Site">
                </div>

                <hr class="my-4 border-gray-200">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Admin Name</label>
                    <input type="text" name="admin_name" required class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="John Doe">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Admin Email</label>
                    <input type="email" name="admin_email" required class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="admin@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Admin Password</label>
                    <input type="password" name="admin_password" required minlength="6" class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Min 6 characters">
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mt-6 transition-colors">
                Install System
            </button>
        </form>
    </div>

</body>
</html>

