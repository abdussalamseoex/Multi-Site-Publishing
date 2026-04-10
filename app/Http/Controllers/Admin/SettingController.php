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

    public function store(Request $request)
    {
        $data = $request->except(['_token', 'site_logo', 'site_favicon']);
        
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        if ($request->hasFile('site_logo')) {
            $path = '/storage/' . $request->file('site_logo')->store('logos', 'public');
            Setting::set('site_logo', $path);
        }

        if ($request->hasFile('site_favicon')) {
            $path = '/storage/' . $request->file('site_favicon')->store('logos', 'public');
            Setting::set('site_favicon', $path);
        }

        return back()->with('status', 'Settings updated successfully.');
    }
}
