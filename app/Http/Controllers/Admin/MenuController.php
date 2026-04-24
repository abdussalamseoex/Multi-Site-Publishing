<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('items')->get();
        if ($menus->isEmpty()) {
            Menu::create(['name' => 'Header Navigation', 'location' => 'header']);
            Menu::create(['name' => 'Footer Navigation', 'location' => 'footer']);
            return redirect()->route('admin.menus.index');
        }
        
        $activeMenu = $menus->first();
        if(request('id')) {
            $activeMenu = Menu::findOrFail(request('id'));
        }

        return view('admin.menus.index', compact('menus', 'activeMenu'));
    }

    public function storeItem(Request $request, $menuId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string|max:255',
        ]);

        $menu = Menu::findOrFail($menuId);
        $order = $menu->items()->max('order') + 1;

        $menu->items()->create([
            'title' => $request->title,
            'url' => $request->url,
            'order' => $order
        ]);

        return back()->with('status', 'Menu Item Added!');
    }

    public function deleteItem($id)
    {
        MenuItem::findOrFail($id)->delete();
        return back()->with('status', 'Menu Item Deleted!');
    }

    public function reorder(Request $request)
    {
        $items = $request->items;
        foreach ($items as $index => $id) {
            MenuItem::where('id', $id)->update(['order' => $index]);
        }
        
        return response()->json(['success' => true]);
    }

    public function importCategories($menuId)
    {
        $menu = Menu::findOrFail($menuId);
        $categories = \App\Models\Category::all();
        
        $order = $menu->items()->max('order') + 1;
        $count = 0;
        
        foreach ($categories as $category) {
            $url = '/category/' . $category->slug;
            
            // Only add if not already exists in this menu
            $exists = $menu->items()->where('url', $url)->orWhere('url', url($url))->exists();
            if (!$exists) {
                $menu->items()->create([
                    'title' => $category->name,
                    'url' => $url,
                    'order' => $order++
                ]);
                $count++;
            }
        }
        
        return back()->with('status', $count . ' Categories imported successfully to ' . $menu->name . '!');
    }
}
