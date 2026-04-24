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
            'parent_id' => 'nullable|exists:menu_items,id'
        ]);

        $menu = Menu::findOrFail($menuId);
        $order = $menu->items()->max('order') + 1;

        $menu->items()->create([
            'title' => $request->title,
            'url' => $request->url,
            'parent_id' => $request->parent_id,
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
        $order = $menu->items()->max('order') + 1;
        $count = 0;
        
        // 1. Get all main categories
        $mainCategories = \App\Models\Category::whereNull('parent_id')->with('children')->get();
        
        foreach ($mainCategories as $category) {
            $url = '/category/' . $category->slug;
            
            $parentItem = $menu->items()->where('url', $url)->orWhere('url', url($url))->first();
            
            if (!$parentItem) {
                $parentItem = $menu->items()->create([
                    'title' => $category->name,
                    'url' => $url,
                    'order' => $order++
                ]);
                $count++;
            }

            // 2. Import children
            foreach ($category->children as $child) {
                $childUrl = '/category/' . $child->slug;
                $childExists = $menu->items()->where('url', $childUrl)->orWhere('url', url($childUrl))->exists();
                
                if (!$childExists) {
                    $menu->items()->create([
                        'title' => $child->name,
                        'url' => $childUrl,
                        'parent_id' => $parentItem->id,
                        'order' => $order++
                    ]);
                    $count++;
                }
            }
        }
        
        return back()->with('status', $count . ' Categories (with subcategories) imported successfully to ' . $menu->name . '!');
    }
}
