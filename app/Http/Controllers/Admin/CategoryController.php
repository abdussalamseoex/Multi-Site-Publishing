<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with(['parent'])->withCount('posts');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = $request->input('sort', 'latest');
        if ($sort == 'latest') {
            $query->latest();
        } elseif ($sort == 'oldest') {
            $query->oldest();
        } elseif ($sort == 'posts_count') {
            $query->orderBy('posts_count', 'desc');
        }

        $categories = $query->get();
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.index', compact('categories', 'parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->name),
            'description' => $request->description,
            'meta_title' => $request->meta_title ?? $request->name,
            'meta_keywords' => $request->meta_keywords,
            'meta_description' => $request->meta_description,
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
        $postCount = 0;
        foreach ($request->input('categories') as $catName) {
            $slug = Str::slug($catName);
            // Check if it already exists to prevent duplicate slugs
            if (!Category::where('slug', $slug)->exists()) {
                $category = Category::create([
                    'name' => $catName,
                    'slug' => $slug,
                    'description' => 'Discussions and insights revolving around ' . strtolower($catName) . ' trends and best practices.',
                ]);
                $count++;

                // Generate 3 dummy posts for this new category
                for ($i = 1; $i <= 3; $i++) {
                    $postTitle = "Sample Premium Article about " . $catName . " " . $i;
                    \App\Models\Post::create([
                        'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                        'category_id' => $category->id,
                        'title' => $postTitle,
                        'slug' => Str::slug($postTitle) . '-' . rand(1000, 9999),
                        'summary' => "This is a premium sample article exploring the depths of $catName. Discover the latest trends, expert opinions, and comprehensive analysis in this exclusive piece.",
                        'content' => "<p>Welcome to this sample article about <strong>$catName</strong>. The industry is rapidly evolving, and staying ahead of the curve is more important than ever.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><h2>Key Takeaways</h2><ul><li>Insight 1 regarding $catName</li><li>Insight 2 regarding $catName</li><li>Insight 3 regarding $catName</li></ul><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.</p>",
                        'featured_image' => 'https://picsum.photos/seed/' . rand(1, 99999) . '/800/600',
                        'status' => 'published',
                        'views' => rand(10, 500),
                    ]);
                    $postCount++;
                }
            }
        }

        return back()->with('status', "Successfully imported $count new categories and generated $postCount demo posts.");
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('status', 'Category deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        Category::whereIn('id', $request->categories)->delete();

        return back()->with('status', count($request->categories) . ' categories deleted successfully.');
    }
}
