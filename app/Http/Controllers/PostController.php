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
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            $isPromoActive = \App\Models\Setting::get('enable_promotional_free_posts') == '1';
            $promoLimit = (int)\App\Models\Setting::get('promotional_free_post_limit', 1);
            $postsToday = Post::where('user_id', $user->id)->whereDate('created_at', \Carbon\Carbon::today())->count();
            $eligibleForPromo = $isPromoActive && ($postsToday < $promoLimit);

            if (!$eligibleForPromo && $user->points <= 0) {
                return back()->with('error', 'You do not have enough points to publish a post. Please top up your account.');
            }

            if (!$user->is_unlimited) {
                $dailySetting = \App\Models\Setting::get('default_daily_post_limit');
                $dailyLimit = (int)($user->daily_post_limit ?? (is_numeric($dailySetting) ? $dailySetting : 1));

                $totalSetting = \App\Models\Setting::get('default_total_post_limit');
                $totalLimit = (int)($user->total_post_limit ?? (is_numeric($totalSetting) ? $totalSetting : 10));

                if ($user->total_posts >= $totalLimit && $totalLimit > 0) {
                    return back()->with('error', "You have reached your total post limit of {$totalLimit}.");
                }

                if ($postsToday >= $dailyLimit && $dailyLimit > 0) {
                    return back()->with('error', "You have reached your daily post limit of {$dailyLimit}. Please try again tomorrow.");
                }
            }
        }

        $categories = Category::all();
        return view('posts.create', compact('categories', 'eligibleForPromo', 'promoLimit', 'postsToday'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'featured_image' => 'nullable|image|max:10240',
        ]);

        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $filename = time() . '_' . uniqid() . '.' . $request->file('featured_image')->getClientOriginalExtension();
            $request->file('featured_image')->move(public_path('uploads/posts'), $filename);
            $featuredImagePath = '/uploads/posts/' . $filename;
        }

        $baseSlug = $request->input('slug') ? \Illuminate\Support\Str::slug($request->input('slug')) : \Illuminate\Support\Str::slug($request->input('title'));
        $finalSlug = \App\Models\Setting::get('seo_post_slug_code') === 'on' ? $baseSlug . '-' . uniqid() : $baseSlug;
        
        // Ensure uniqueness if code is off
        if (\App\Models\Setting::get('seo_post_slug_code') !== 'on') {
            $originalFinal = $finalSlug;
            $counter = 1;
            while (Post::where('slug', $finalSlug)->exists()) {
                $finalSlug = $originalFinal . '-' . $counter;
                $counter++;
            }
        }

        $user = Auth::user();
        $isDofollow = false;
        $usedFreePromo = false;

        if (!$user->hasRole('admin')) {
            $isPromoActive = \App\Models\Setting::get('enable_promotional_free_posts') == '1';
            $promoLimit = (int)\App\Models\Setting::get('promotional_free_post_limit', 1);
            $postsToday = Post::where('user_id', $user->id)->whereDate('created_at', \Carbon\Carbon::today())->count();
            $eligibleForPromo = $isPromoActive && ($postsToday < $promoLimit);

            if (!$eligibleForPromo && $user->points <= 0) {
                return back()->with('error', 'You do not have enough points.');
            }

            if (!$user->is_unlimited) {
                $dailySetting = \App\Models\Setting::get('default_daily_post_limit');
                $dailyLimit = (int)($user->daily_post_limit ?? (is_numeric($dailySetting) ? $dailySetting : 1));

                $totalSetting = \App\Models\Setting::get('default_total_post_limit');
                $totalLimit = (int)($user->total_post_limit ?? (is_numeric($totalSetting) ? $totalSetting : 10));

                if ($user->total_posts >= $totalLimit && $totalLimit > 0) return back()->with('error', "You have reached your total limit.");
                if ($postsToday >= $dailyLimit && $dailyLimit > 0) {
                    return back()->with('error', "You have reached your daily limit.");
                }
            }

            if ($eligibleForPromo) {
                $usedFreePromo = true;
            } else {
                $user->decrement('points');
            }
            
            $user->increment('total_posts');

            $globalDofollow = \App\Models\Setting::get('default_dofollow_status', 0);
            $isDofollow = is_null($user->dofollow_default) ? (bool)$globalDofollow : (bool)$user->dofollow_default;
        } else {
            $isDofollow = true;
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $request->input('title'),
            'slug' => $finalSlug,
            'original_slug' => $baseSlug,
            'content' => $request->input('content'),
            'category_id' => $request->input('category_id'),
            'featured_image' => $featuredImagePath,
            'status' => \App\Models\Setting::get('default_post_status', 'pending'), 
            'meta_title' => $request->input('meta_title') ?? $request->input('title'),
            'meta_description' => $request->input('meta_description') ?? substr(strip_tags($request->input('content')), 0, 150),
            'meta_keywords' => $request->input('meta_keywords'),
            'is_dofollow' => $isDofollow,
        ]);

        if (\App\Models\Setting::get('enable_checkout_flow') == '1') {
            return redirect()->route('orders.checkout', $post->id);
        } else {
            $msg = $usedFreePromo ? 'Post submitted successfully! This was a free promotional post (0 points deducted).' : 'Post submitted successfully! 1 point has been deducted.';
            return redirect()->route('posts.index')->with('success', $msg);
        }
    }

    public function edit(Post $post)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            if (\App\Models\Setting::get('enable_user_post_editing') != '1') {
                return back()->with('error', 'Post editing is currently disabled by the administrator.');
            }
            if ($post->user_id !== $user->id) {
                return back()->with('error', 'You do not have permission to edit this post.');
            }
        }

        $categories = Category::all();
        return view('posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            if (\App\Models\Setting::get('enable_user_post_editing') != '1') {
                return back()->with('error', 'Post editing is currently disabled.');
            }
            if ($post->user_id !== $user->id) {
                return back()->with('error', 'You do not have permission to edit this post.');
            }
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'featured_image' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('featured_image')) {
            $filename = time() . '_' . uniqid() . '.' . $request->file('featured_image')->getClientOriginalExtension();
            $request->file('featured_image')->move(public_path('uploads/posts'), $filename);
            $post->featured_image = '/uploads/posts/' . $filename;
        }

        $baseSlug = $request->input('slug') ? \Illuminate\Support\Str::slug($request->input('slug')) : \Illuminate\Support\Str::slug($request->input('title'));
        $finalSlug = \App\Models\Setting::get('seo_post_slug_code') === 'on' ? $baseSlug . '-' . uniqid() : $baseSlug;
        
        if (\App\Models\Setting::get('seo_post_slug_code') !== 'on') {
            $originalFinal = $finalSlug;
            $counter = 1;
            while (Post::where('slug', $finalSlug)->where('id', '!=', $post->id)->exists()) {
                $finalSlug = $originalFinal . '-' . $counter;
                $counter++;
            }
        }

        $post->update([
            'title' => $request->input('title'),
            'slug' => $finalSlug,
            'content' => $request->input('content'),
            'category_id' => $request->input('category_id'),
            'status' => 'pending', 
            'meta_title' => $request->input('meta_title') ?? $request->input('title'),
            'meta_description' => $request->input('meta_description') ?? substr(strip_tags($request->input('content')), 0, 150),
            'meta_keywords' => $request->input('meta_keywords'),
        ]);

        return redirect()->route('posts.index')->with('success', 'Post updated successfully! It has been sent to pending status for admin review.');
    }
}
