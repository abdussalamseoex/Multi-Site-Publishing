<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            $posts = Post::latest()->paginate(15);
        } else {
            $posts = Post::where('user_id', $user->id)->latest()->paginate(15);
        }

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'featured_image' => 'nullable|image|max:10240',
        ]);

        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = '/storage/' . $request->file('featured_image')->store('featured_images', 'public');
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $request->input('title'),
            'slug' => \Illuminate\Support\Str::slug($request->input('title')) . '-' . uniqid(),
            'content' => $request->input('content'),
            'category_id' => $request->input('category_id'),
            'featured_image' => $featuredImagePath,
            'status' => \App\Models\Setting::get('default_post_status', 'pending'), 
            'meta_title' => $request->input('meta_title') ?? $request->input('title'),
            'meta_description' => $request->input('meta_description') ?? substr(strip_tags($request->input('content')), 0, 150),
            'canonical_url' => $request->input('canonical_url'),
        ]);

        return redirect()->route('orders.checkout', $post->id);
    }
}
