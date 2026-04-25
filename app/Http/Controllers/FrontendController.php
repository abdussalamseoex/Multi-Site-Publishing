<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Post;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        // Fallback to 'minimal' if not set
        $activeTheme = Setting::get('active_theme', 'minimal');
        
        $featuredPosts = Post::where('status', 'published')
                             ->where('is_featured', true)
                             ->latest()
                             ->orderBy('id', 'desc')
                             ->take(5)
                             ->get();
                             
        $latestPosts = Post::where('status', 'published')
                           ->latest()
                           ->orderBy('id', 'desc')
                           ->paginate(12);

        $viewName = "themes.{$activeTheme}.home";

        // Check if theme view exists, fallback to minimal if standard wasn't found
        if (!view()->exists($viewName)) {
            $viewName = "themes.minimal.home";
        }

        return view($viewName, compact('featuredPosts', 'latestPosts', 'activeTheme'));
    }

    public function showPost($slug)
    {
        $post = Post::where('slug', $slug)->first();
        
        if (!$post) {
            // Check legacy imported original_slug
            $post = Post::where('original_slug', $slug)->firstOrFail();
            return redirect()->route('frontend.post', $post->slug, 301);
        }
        
        if ($post->status !== 'published') {
            // Allow admin or the author to preview
            if (!auth()->check() || (auth()->user()->role !== 'admin' && auth()->id() !== $post->user_id)) {
                abort(404);
            }
        } else {
            // Only increment views for actual public visits
            $post->increment('views');
        }

        $activeTheme = Setting::get('active_theme', 'minimal');
        $viewName = "themes.{$activeTheme}.post";

        if (!view()->exists($viewName)) {
            $viewName = "themes.minimal.post";
        }

        return view($viewName, compact('post', 'activeTheme'));
    }

        public function category($slug)
    {
        $category = \App\Models\Category::where('slug', $slug)->firstOrFail();
        $posts = Post::where('category_id', $category->id)
                     ->where('status', 'published')
                     ->latest()
                     ->paginate(12);

        $activeTheme = Setting::get('active_theme', 'minimal');
        $viewName = "themes.{$activeTheme}.category";

        if (view()->exists($viewName)) {
            return view($viewName, compact('category', 'posts', 'activeTheme'));
        }

        // Re-use current active theme's home layout as the category page
        $homeView = "themes.{$activeTheme}.home";
        if (view()->exists($homeView)) {
            $featuredPosts = collect(); 
            $latestPosts = $posts;
            $isCategory = true;
            return view($homeView, compact('category', 'latestPosts', 'featuredPosts', 'isCategory', 'activeTheme'));
        }

        return view('themes.category', compact('category', 'posts', 'activeTheme'));
    }

    public function page($slug)
    {
        $page = \App\Models\Page::where('slug', $slug)->firstOrFail();
        $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');
        return view('themes.page', compact('page', 'activeTheme'));
    }
}
