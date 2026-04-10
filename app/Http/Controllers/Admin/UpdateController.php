<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UpdateController extends Controller
{
    public function index()
    {
        return view('admin.update.index');
    }

    public function process(Request $request)
    {
        $log = [];

        // Determine branch, fallback to main
        $branch = 'main';

        // 1. Git Fetch & Pull
        try {
            $gitPullProcess = Process::fromShellCommandline("git fetch origin && git pull origin {$branch} 2>&1");
            $gitPullProcess->setWorkingDirectory(base_path());
            $gitPullProcess->setTimeout(120);
            $gitPullProcess->run();
            $log[] = "==== GIT PULL LOG ====";
            $log[] = $gitPullProcess->getOutput();
        } catch (\Exception $e) {
            $log[] = "==== GIT ERROR ====";
            $log[] = $e->getMessage();
        }

        // 2. Clear Caches
        try {
            Artisan::call('optimize:clear');
            $log[] = "==== SYSTEM CACHE ====";
            $log[] = Artisan::output();
        } catch (\Exception $e) {
            $log[] = "==== CACHE ERROR ====";
            $log[] = $e->getMessage();
        }

        // 3. Database Migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            $log[] = "==== DATABASE MIGRATION ====";
            $log[] = Artisan::output();
        } catch (\Exception $e) {
            $log[] = "==== MIGRATION ERROR ====";
            $log[] = $e->getMessage();
        }

        // Output formatting
        $finalLog = implode("\n\n", array_filter($log));

        return back()->with('update_log', $finalLog)->with('status', 'Update process completed! Please check the logs below.');
    }
}
