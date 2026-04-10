<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ \App\Models\Setting::get('site_favicon') ? url(\App\Models\Setting::get('site_favicon')) : asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard - Database Configuration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">System Installation</h1>
            <p class="text-gray-500 mt-2">Step 1: Database Setup</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 text-red-500 p-4 rounded-lg mb-6 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('install.processDatabase') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Database Connection</label>
                    <select name="db_connection" class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" onchange="toggleFields()">
                        <option value="mysql">MySQL</option>
                        <option value="sqlite">SQLite (Local testing)</option>
                    </select>
                </div>

                <div id="mysql_fields" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Host</label>
                        <input type="text" name="db_host" value="127.0.0.1" class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Port</label>
                        <input type="text" name="db_port" value="3306" class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Name</label>
                        <input type="text" name="db_name" class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="laravel">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Username</label>
                        <input type="text" name="db_user" class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="root">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Password</label>
                        <input type="password" name="db_password" class="mt-1 block w-full rounded-md border-gray-300 border p-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Leave blank if none">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-6">
                Next Step
            </button>
        </form>
    </div>

    <script>
        function toggleFields() {
            var conn = document.querySelector('select[name="db_connection"]').value;
            var mysqlFields = document.getElementById('mysql_fields');
            if (conn === 'sqlite') {
                mysqlFields.style.display = 'none';
            } else {
                mysqlFields.style.display = 'block';
            }
        }
    </script>
</body>
</html>

