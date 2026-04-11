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

    public function store(Request $request)
    {
        $data = $request->except(['_token', 'site_logo', 'site_favicon']);
        
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        // FORCE DELETE any physical robots.txt to prevent Nginx/Apache overriding the dynamic route
        $targets = [
            public_path('robots.txt'),
            base_path('robots.txt'),
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'].'/robots.txt' : null,
        ];
        
        foreach ($targets as $target) {
            if ($target && \Illuminate\Support\Facades\File::exists($target)) {
                try {
                    \Illuminate\Support\Facades\File::delete($target);
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
}
