<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;

class UpdateController extends Controller
{
    private $githubRepo = "abdussalamseoex/Multi-Site-Publishing";
    private $branch = "main";
    
    // Change this to your GitHub Personal Access Token string, or leave it as env() variable!
    private function getToken() {
        return env('GITHUB_UPDATE_TOKEN', '');
    }

    public function index()
    {
        $pendingCommits = [];
        $token = $this->getToken();
        $githubStatus = 'unknown';
        $githubMessage = '';
        
        try {
            $requestHttp = Http::withHeaders(['User-Agent' => 'Laravel-Updater']);
            if (!empty($token)) {
                $requestHttp = $requestHttp->withToken($token);
            }
            
            $response = $requestHttp->get("https://api.github.com/repos/{$this->githubRepo}/commits", [
                'sha' => $this->branch,
                'per_page' => 5 // Just show the latest 5 commits as a preview
            ]);

            if ($response->successful()) {
                $githubStatus = 'connected';
                $commits = $response->json();
                foreach($commits as $commit) {
                    $pendingCommits[] = substr($commit['sha'], 0, 7) . ' - ' . $commit['commit']['message'];
                }
            } else {
                $githubStatus = 'error';
                $githubMessage = $response->json('message') ?? 'API access denied';
            }
        } catch (\Exception $e) {
            $githubStatus = 'error';
            $githubMessage = $e->getMessage();
        }

        return view('admin.update.index', compact('pendingCommits', 'githubStatus', 'githubMessage'));
    }

    public function process(Request $request)
    {
        if ($request->has('force_clear')) {
            // Skip update and just run cleanup
            $log = ["==== MANUAL SYSTEM REFRESH ===="];
            try {
                Artisan::call('optimize:clear');
                Artisan::call('route:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
                Artisan::call('cache:clear');
                $log[] = "All caches and routes refreshed successfully.";
            } catch (\Exception $e) {
                $log[] = "Error: " . $e->getMessage();
            }
            return back()->with('status', 'System refreshed successfully! All 404 issues should be resolved now.')->with('update_log', implode("\n", $log));
        }

        $request->validate([
            'update_file' => 'nullable|file|mimes:zip|max:51200', // 50MB max
        ]);

        $log = [];
        $token = $this->getToken();

        try {
            $log[] = "==== FETCHING UPDATE ====";
            
            $url = "https://api.github.com/repos/{$this->githubRepo}/zipball/{$this->branch}";
            $requestHttp = Http::withHeaders(['User-Agent' => 'Laravel-Updater'])->withOptions(['stream' => true]);
            if (!empty($token)) {
                $requestHttp = $requestHttp->withToken($token);
            }
            
            $response = $requestHttp->get($url);

            if ($response->failed()) {
                throw new \Exception("Failed to download update from GitHub. Status: " . $response->status() . ". Ensure your GITHUB_UPDATE_TOKEN is valid for private repositories.");
            }

            $zipPath = storage_path('app/system_update.zip');
            File::put($zipPath, $response->body());
            
            $log[] = "Downloaded update archive successfully.";

            $zip = new ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $extractPath = storage_path('app/update-temp');
                
                // Clear any old temp folder
                if (File::exists($extractPath)) {
                    File::deleteDirectory($extractPath);
                }
                
                $zip->extractTo($extractPath);
                $zip->close();
                
                $log[] = "Extracted archive. Applying files...";

                // GitHub zips contain a root folder like 'abdussalamseoex-Multi-Site-Publishing-abc1234'
                $directories = File::directories($extractPath);
                if (!empty($directories)) {
                    $sourceDir = $directories[0];
                    
                    // Copy specific directories to overwrite application files safely
                    // We avoid blindly copying everything to prevent overwriting vendor/ or storage/ local data if they accidentally get pushed
                    $foldersToUpdate = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'tests'];
                    
                    foreach ($foldersToUpdate as $folder) {
                        if (File::exists("{$sourceDir}/{$folder}")) {
                            File::copyDirectory("{$sourceDir}/{$folder}", base_path($folder));
                        }
                    }
                    
                    // Copy standalone files
                    $filesToUpdate = ['composer.json', 'package.json', 'tailwind.config.js', 'vite.config.js'];
                    foreach ($filesToUpdate as $file) {
                        if (File::exists("{$sourceDir}/{$file}")) {
                            File::copy("{$sourceDir}/{$file}", base_path($file));
                        }
                    }
                }

                // Cleanup
                File::deleteDirectory($extractPath);
                File::delete($zipPath);
                
                $log[] = "Files applied successfully.";
            } else {
                throw new \Exception("Failed to extract the ZIP archive.");
            }

        } catch (\Exception $e) {
            $log[] = "==== UPDATE ERROR ====";
            $log[] = $e->getMessage();
            $finalLog = implode("\n\n", array_filter($log));
            return back()->with('update_log', $finalLog)->with('error', 'Update Failed! Check the log below.');
        }

        // Run optimizations
        try {
            $log[] = "==== SYSTEM CLEANUP ====";
            Artisan::call('optimize:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            $log[] = "System cache, routes, and config cleared successfully.";
            
            // Cleanup any static robots.txt so dynamic SeoController route can work
            if (File::exists(public_path('robots.txt'))) {
                File::delete(public_path('robots.txt'));
                $log[] = "Cleaned up static robots.txt wrapper constraint.";
            }
        } catch (\Exception $e) { 
            $log[] = "Cleanup warning: " . $e->getMessage();
        }

        // Run migrations
        try {
            $log[] = "==== DATABASE MIGRATE ====";
            Artisan::call('migrate', ['--force' => true]);
            $log[] = Artisan::output();
        } catch (\Exception $e) { }

        $finalLog = implode("\n\n", array_filter($log));

        return back()->with('update_log', $finalLog)->with('status', 'Update process completed via Zip Extraction!');
    }
}
