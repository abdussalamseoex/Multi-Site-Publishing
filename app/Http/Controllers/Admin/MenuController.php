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
            Menu::create(['name' => 'Footer Categories', 'location' => 'footer_categories']);
            return redirect()->route('admin.menus.index');
        }

        if (!Menu::where('location', 'footer_categories')->exists()) {
            Menu::create(['name' => 'Footer Categories', 'location' => 'footer_categories']);
            return redirect()->route('admin.menus.index');
        }
        
        $activeMenu = $menus->first();
        if(request('id')) {
            $activeMenu = Menu::findOrFail(request('id'));
        }

        $mainCategories = \App\Models\Category::whereNull('parent_id')->get();

        return view('admin.menus.index', compact('menus', 'activeMenu', 'mainCategories'));
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

    public function bulkDeleteItems(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:menu_items,id'
        ]);

        MenuItem::whereIn('id', $request->item_ids)->delete();
        return back()->with('status', count($request->item_ids) . ' Menu Items Deleted!');
    }

    public function reorder(Request $request)
    {
        $items = $request->items;
        foreach ($items as $index => $id) {
            MenuItem::where('id', $id)->update(['order' => $index]);
        }
        
        return response()->json(['success' => true]);
    }

    public function importCategories(Request $request, $menuId)
    {
        $request->validate([
            'categories' => 'required|array',
            'import_mode' => 'required|in:top_level,dropdown',
            'dropdown_name' => 'nullable|string'
        ]);

        $menu = Menu::findOrFail($menuId);
        $order = $menu->items()->max('order') + 1;
        $count = 0;
        
        $selectedCategoryIds = $request->input('categories');
        $mainCategories = \App\Models\Category::whereIn('id', $selectedCategoryIds)->with('children')->get();
        
        $dropdownParentId = null;
        
        if ($request->import_mode === 'dropdown') {
            $dropdownName = $request->input('dropdown_name', 'Categories');
            
            $parentItem = $menu->items()->where('title', $dropdownName)->whereNull('parent_id')->first();
            if (!$parentItem) {
                $parentItem = $menu->items()->create([
                    'title' => $dropdownName,
                    'url' => '#',
                    'order' => $order++
                ]);
            }
            $dropdownParentId = $parentItem->id;
        }
        
        foreach ($mainCategories as $category) {
            $url = '/category/' . $category->slug;
            
            // Check if already exists in this specific level
            $query = $menu->items()->where(function($q) use ($url) {
                $q->where('url', $url)->orWhere('url', url($url));
            });
            
            if ($dropdownParentId) {
                $query->where('parent_id', $dropdownParentId);
            } else {
                $query->whereNull('parent_id');
            }
            
            $catItem = $query->first();
            
            if (!$catItem) {
                $catItem = $menu->items()->create([
                    'title' => $category->name,
                    'url' => $url,
                    'parent_id' => $dropdownParentId,
                    'order' => $order++
                ]);
                $count++;
            }

            // Import subcategories under this main category
            foreach ($category->children as $child) {
                $childUrl = '/category/' . $child->slug;
                $childExists = $menu->items()->where('parent_id', $catItem->id)
                                            ->where(function($q) use ($childUrl) {
                                                $q->where('url', $childUrl)->orWhere('url', url($childUrl));
                                            })->exists();
                
                if (!$childExists) {
                    $menu->items()->create([
                        'title' => $child->name,
                        'url' => $childUrl,
                        'parent_id' => $catItem->id,
                        'order' => $order++
                    ]);
                    $count++;
                }
            }
        }
        
        return back()->with('status', $count . ' Categories imported successfully!');
    }
}
