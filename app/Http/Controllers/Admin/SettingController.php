<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings', compact('settings'));
    }

    public function seoIndex()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.seo', compact('settings'));
    }

    public function limitsIndex()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings_limits', compact('settings'));
    }

    public function socialIndex()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings_social', compact('settings'));
    }

    public function store(Request $request)
    {
        $data = $request->except(['_token', 'site_logo', 'site_favicon']);
        
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        // FORCE WRITE the physical robots.txt to overcome shared hosting proxy blockades
        $robotsContent = "User-agent: *\nDisallow: /admin/\nDisallow: /checkout/\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
        if (isset($data['custom_robots_txt'])) {
            $robotsContent = $data['custom_robots_txt'];
        }
        
        $targets = [
            public_path('robots.txt'),
            base_path('robots.txt'),
            isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/robots.txt' : null,
        ];
        
        $written = false;
        foreach ($targets as $target) {
            if ($target) {
                try {
                    \Illuminate\Support\Facades\File::put($target, $robotsContent);
                    $written = true;
                } catch (\Exception $e) {}
            }
        }

        if ($request->hasFile('site_logo')) {
            $filename = 'logo_' . time() . '.' . $request->file('site_logo')->extension();
            $request->file('site_logo')->move(public_path('uploads/logos'), $filename);
            Setting::set('site_logo', '/uploads/logos/' . $filename);
        }

        if ($request->hasFile('site_favicon')) {
            $filename = 'favicon_' . time() . '.' . $request->file('site_favicon')->extension();
            $request->file('site_favicon')->move(public_path('uploads/logos'), $filename);
            Setting::set('site_favicon', '/uploads/logos/' . $filename);
        }

        return back()->with('status', 'Settings updated successfully.');
    }

    public function importDemo()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\DemoContentSeeder',
                '--force' => true
            ]);
            return back()->with('status', 'Demo content (categories and 30 posts) imported successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Demo import failed: ' . $e->getMessage());
        }
    }

    public function runMigration()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', [
                '--force' => true
            ]);
            return back()->with('status', 'Database Migrations completed successfully! System is up to date.');
        } catch (\Exception $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }
}
