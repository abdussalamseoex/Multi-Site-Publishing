<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Http\Request;

class ThemeOptionsController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $activeTheme = Setting::get('active_theme', 'minimal');
        
        // Dynamic keys based on active theme
        $blocksKey = "theme_blocks_{$activeTheme}";
        $sidebarKey = "theme_sidebar_{$activeTheme}";

        $homepageLayoutRaw = Setting::get($blocksKey);
        $sidebarLayoutRaw = Setting::get($sidebarKey);
        
        $homepageLayout = $homepageLayoutRaw ? json_decode($homepageLayoutRaw, true) : [];
        $sidebarLayout = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];

        // Provide a default layout for the 'good' theme if it's completely empty
        if (empty($homepageLayout) && $activeTheme === 'good') {
            $homepageLayout = [
                ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Top Stories', 'category_id' => null, 'limit' => 4],
                ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Articles', 'category_id' => null, 'limit' => 6],
                ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Middle Ad', 'category_id' => null, 'limit' => null],
                ['id' => uniqid(), 'type' => 'category_spotlight', 'title' => 'Editorial Choice', 'category_id' => null, 'limit' => 5],
                ['id' => uniqid(), 'type' => 'category_grid', 'title' => 'More News', 'category_id' => null, 'limit' => 6]
            ];
        }

        if (empty($sidebarLayout) && $activeTheme === 'good') {
            $sidebarLayout = [
                ['id' => uniqid(), 'type' => 'social_counter', 'title' => 'Stay Connected'],
                ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad'],
                ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Most Popular', 'limit' => 5],
                ['id' => uniqid(), 'type' => 'categories_list', 'title' => 'Categories', 'limit' => 6],
                ['id' => uniqid(), 'type' => 'newsletter', 'title' => 'Subscribe']
            ];
        }

        return view('admin.theme-options.index', compact('categories', 'homepageLayout', 'sidebarLayout', 'activeTheme'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'layout_data' => 'required|json',
            'sidebar_data' => 'required|json'
        ]);

        $activeTheme = Setting::get('active_theme', 'minimal');
        $blocksKey = "theme_blocks_{$activeTheme}";
        $sidebarKey = "theme_sidebar_{$activeTheme}";

        // Save layout configuration to settings dynamically for the active theme
        Setting::set($blocksKey, $request->layout_data);
        Setting::set($sidebarKey, $request->sidebar_data);

        return back()->with('status', 'Theme layout successfully saved for the active theme (' . strtoupper($activeTheme) . ')!');
    }
}
