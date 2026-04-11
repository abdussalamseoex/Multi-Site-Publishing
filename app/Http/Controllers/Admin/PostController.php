<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('user', 'category');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sort = $request->input('sort', 'latest');
        if ($sort == 'latest') {
            $query->latest();
        } elseif ($sort == 'oldest') {
            $query->oldest();
        } elseif ($sort == 'views_desc') {
            $query->orderBy('views', 'desc');
        }

        $posts = $query->paginate(20)->withQueryString();
        $categories = \App\Models\Category::all();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,pending,published,rejected',
            'featured_image' => 'nullable|image|max:10240',
        ]);

        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $filename = time() . '_' . uniqid() . '.' . $request->file('featured_image')->getClientOriginalExtension();
            $request->file('featured_image')->move(public_path('uploads/posts'), $filename);
            $featuredImagePath = '/uploads/posts/' . $filename;
        }

        Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . uniqid(),
            'content' => $request->content,
            'category_id' => $request->category_id,
            'featured_image' => $featuredImagePath,
            'status' => $request->status,
            'meta_title' => $request->meta_title ?? $request->title,
            'meta_description' => $request->meta_description ?? substr(strip_tags($request->content), 0, 150),
            'canonical_url' => $request->canonical_url,
        ]);

        return redirect()->route('admin.posts.index')->with('status', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        $categories = \App\Models\Category::all();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,pending,published,rejected',
            'featured_image' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('featured_image')) {
            $filename = time() . '_' . uniqid() . '.' . $request->file('featured_image')->getClientOriginalExtension();
            $request->file('featured_image')->move(public_path('uploads/posts'), $filename);
            $post->featured_image = '/uploads/posts/' . $filename;
        }

        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'status' => $request->status,
            'meta_title' => $request->meta_title ?? $request->title,
            'meta_description' => $request->meta_description ?? substr(strip_tags($request->content), 0, 150),
            'canonical_url' => $request->canonical_url,
        ]);

        return redirect()->route('admin.posts.index')->with('status', 'Post updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:draft,pending,published,rejected'
        ]);

        $post->status = $request->status;
        $post->save();

        return back()->with('status', 'Post status updated to ' . $request->status);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return back()->with('status', 'Post deleted successfully.');
    }
}
