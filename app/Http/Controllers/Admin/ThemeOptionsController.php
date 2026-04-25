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

        // Provide a default layout if it's completely empty
        if (empty($homepageLayout)) {
            $homepageLayout = self::getDefaultBlocks($activeTheme);
        }

        if (empty($sidebarLayout)) {
            $sidebarLayout = self::getDefaultSidebar($activeTheme);
        }

        return view('admin.theme-options.index', compact('categories', 'homepageLayout', 'sidebarLayout', 'activeTheme'));
    }

    public static function getDefaultBlocks($theme)
    {
        switch ($theme) {
            case 'vanguard':
            case 'estate':
            case 'vitality':
                return [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Featured', 'category_id' => null, 'limit' => 4],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Articles', 'category_id' => null, 'limit' => 6]
                ];
            case 'voyage':
            case 'news':
                return [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Top Stories', 'category_id' => null, 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'category_spotlight', 'title' => "Don't Miss", 'category_id' => null, 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'category_grid', 'title' => 'Lifestyle News', 'category_id' => null, 'limit' => 4],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Articles', 'category_id' => null, 'limit' => 6]
                ];
            case 'blog':
                return [
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Articles', 'category_id' => null, 'limit' => 10]
                ];
            case 'minimal':
                return [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Featured', 'category_id' => null, 'limit' => 2],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Articles', 'category_id' => null, 'limit' => 6]
                ];
            default:
                return [
                    ['id' => uniqid(), 'type' => 'hero_grid', 'title' => 'Top Stories', 'category_id' => null, 'limit' => 4],
                    ['id' => uniqid(), 'type' => 'latest_news', 'title' => 'Latest Articles', 'category_id' => null, 'limit' => 6],
                    ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Middle Ad', 'category_id' => null, 'limit' => null],
                    ['id' => uniqid(), 'type' => 'category_spotlight', 'title' => 'Editorial Choice', 'category_id' => null, 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'category_grid', 'title' => 'More News', 'category_id' => null, 'limit' => 6]
                ];
        }
    }

    public static function getDefaultSidebar($theme)
    {
        switch ($theme) {
            case 'vanguard':
            case 'minimal':
                return [
                    ['id' => uniqid(), 'type' => 'social_counter', 'title' => 'Stay Connected'],
                    ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Most Popular', 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad']
                ];
            case 'blog':
                return [
                    ['id' => uniqid(), 'type' => 'about_agency', 'title' => 'About Us'],
                    ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Featured', 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad']
                ];
            case 'vitality':
                return [
                    ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Trending Topics', 'limit' => 5],
                    ['id' => uniqid(), 'type' => 'newsletter', 'title' => 'Stay Healthy']
                ];
            case 'voyage':
                return [
                    ['id' => uniqid(), 'type' => 'popular_tags', 'title' => 'Popular Tags'],
                    ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad']
                ];
            case 'estate':
                return [
                    ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Popular Local', 'limit' => 4],
                    ['id' => uniqid(), 'type' => 'about_agency', 'title' => 'List With Us']
                ];
            default:
                return [
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

    public function reset()
    {
        $activeTheme = Setting::get('active_theme', 'minimal');
        
        \Illuminate\Support\Facades\DB::table('settings')->where('key', "theme_blocks_{$activeTheme}")->delete();
        \Illuminate\Support\Facades\DB::table('settings')->where('key', "theme_sidebar_{$activeTheme}")->delete();

        return back()->with('status', 'Layout has been completely reset to original ' . strtoupper($activeTheme) . ' factory defaults!');
    }
}
