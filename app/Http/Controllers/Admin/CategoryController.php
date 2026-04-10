<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return back()->with('status', 'Category created successfully.');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'string'
        ]);

        $count = 0;
        foreach ($request->input('categories') as $catName) {
            $slug = Str::slug($catName);
            // Check if it already exists to prevent duplicate slugs
            if (!Category::where('slug', $slug)->exists()) {
                Category::create([
                    'name' => $catName,
                    'slug' => $slug,
                    'description' => 'Discussions and insights revolving around ' . strtolower($catName) . ' trends and best practices.',
                ]);
                $count++;
            }
        }

        return back()->with('status', "Successfully imported $count new categories.");
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('status', 'Category deleted successfully.');
    }
}
