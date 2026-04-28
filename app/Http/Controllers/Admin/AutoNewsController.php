<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AutoNewsSource;
use App\Models\Category;

class AutoNewsController extends Controller
{
    public function index()
    {
        $sources = AutoNewsSource::with('category')->get();
        $categories = Category::all();
        
        return view('admin.ai-writer.news', compact('sources', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'source_url' => 'required|url',
            'category_id' => 'nullable|exists:categories,id',
            'posts_per_run' => 'required|integer|min:1|max:20',
            'fetch_interval_hours' => 'required|integer|min:1|max:168',
            'featured_image_source' => 'required|string',
            'in_content_images_count' => 'required|integer|min:0|max:5',
            'in_content_image_source' => 'required|string',
            'is_active' => 'boolean',
        ]);

        AutoNewsSource::create([
            'name' => $request->name,
            'source_url' => $request->source_url,
            'category_id' => $request->category_id,
            'posts_per_run' => $request->posts_per_run,
            'fetch_interval_hours' => $request->fetch_interval_hours,
            'featured_image_source' => $request->featured_image_source,
            'in_content_images_count' => $request->in_content_images_count,
            'in_content_image_source' => $request->in_content_image_source,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return back()->with('status', 'Auto News Source added successfully. Cron Job will process it based on the interval.');
    }

    public function destroy($id)
    {
        $source = AutoNewsSource::findOrFail($id);
        $source->delete();

        return back()->with('status', 'Auto News Source deleted.');
    }
}
