<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    public function index()
    {
        return view('install.step1');
    }

    public function processDatabase(Request $request)
    {
        $request->validate([
            'db_connection' => 'required|in:mysql,sqlite',
        ]);

        if ($request->db_connection === 'mysql') {
            $request->validate([
                'db_host' => 'required',
                'db_port' => 'required',
                'db_name' => 'required',
                'db_user' => 'required',
            ]);

            $this->updateEnv([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_name,
                'DB_USERNAME' => $request->db_user,
                'DB_PASSWORD' => $request->db_password ?? '',
            ]);
        } else {
            // Create sqlite database file if not exists
            if (!file_exists(database_path('database.sqlite'))) {
                touch(database_path('database.sqlite'));
            }
            
            $this->updateEnv([
                'DB_CONNECTION' => 'sqlite',
            ]);
        }

        return redirect()->route('install.step2')->with('success', 'Database details saved.');
    }

    public function step2()
    {
        return view('install.step2');
    }

    public function processInstallation(Request $request)
    {
        $request->validate([
            'admin_name' => 'required',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:6',
            'site_name' => 'required',
        ]);

        try {
            // Apply new DB config instantly so migrate works without needing a full reload
            $connection = env('DB_CONNECTION', 'sqlite');
            config(["database.default" => $connection]);
            
            if ($connection === 'mysql') {
                config(['database.connections.mysql.host' => env('DB_HOST', config('database.connections.mysql.host'))]);
                config(['database.connections.mysql.port' => env('DB_PORT', config('database.connections.mysql.port'))]);
                config(['database.connections.mysql.database' => env('DB_DATABASE', config('database.connections.mysql.database'))]);
                config(['database.connections.mysql.username' => env('DB_USERNAME', config('database.connections.mysql.username'))]);
                config(['database.connections.mysql.password' => env('DB_PASSWORD', config('database.connections.mysql.password'))]);
                DB::purge('mysql');
            } else {
                // SQLite
                config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
                DB::purge('sqlite');
            }

            Artisan::call('migrate:fresh', ['--force' => true]);

            // Install roles
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'editor']);
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'author']);

            $user = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
            ]);
            
            $user->assignRole('admin');

            $this->updateEnv([
                'APP_NAME' => '"' . $request->site_name . '"',
                'APP_INSTALLED' => 'true',
            ]);

            Artisan::call('optimize:clear');

            return redirect('/login')->with('success', 'Installation successful! Please log in.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function updateEnv($data)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        foreach ($data as $key => $val) {
            $keyPosition = strpos($str, "{$key}=");
            if ($keyPosition !== false) {
                // Determine the end of the line
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                if ($endOfLinePosition === false) {
                    $endOfLinePosition = strlen($str);
                }
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                $str = str_replace($oldLine, "{$key}={$val}", $str);
            } else {
                // If it doesn't exist, append
                $str .= "\n{$key}={$val}";
            }
        }

        // Clean up any double newlines at the end
        $str = trim($str) . "\n";
        file_put_contents($envFile, $str);
    }
}
