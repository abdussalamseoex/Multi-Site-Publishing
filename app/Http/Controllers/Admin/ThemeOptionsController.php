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
        $homepageLayoutRaw = Setting::get('homepage_layout');
        
        $homepageLayout = [];
        if ($homepageLayoutRaw) {
            $homepageLayout = json_decode($homepageLayoutRaw, true);
        }

        // If completely empty, set a default layout block so the UI isn't totally blank
        if (empty($homepageLayout)) {
            $homepageLayout = [
                [
                    'id' => uniqid(),
                    'type' => 'hero_grid',
                    'title' => 'Top Stories',
                    'category_id' => null,
                    'limit' => 4
                ],
                [
                    'id' => uniqid(),
                    'type' => 'latest_news',
                    'title' => 'Latest Articles',
                    'category_id' => null,
                    'limit' => 6
                ]
            ];
        }

        return view('admin.theme-options.index', compact('categories', 'homepageLayout'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'layout_data' => 'required|json'
        ]);

        // Save layout configuration to settings
        Setting::set('homepage_layout', $request->layout_data);

        return back()->with('status', 'Homepage layout successfully saved! The GOOD theme will now use this structure.');
    }
}
