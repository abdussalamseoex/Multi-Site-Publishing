<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AutoNewsSource;
use App\Models\Category;
use Illuminate\Support\Str;

class AutoNewsController extends Controller
{
    public function index()
    {
        $sources = AutoNewsSource::with(['category', 'user'])->withCount(['posts', 'posts as today_posts_count' => function($query) {
            $query->whereDate('created_at', today());
        }])->get();
        $categories = Category::all();
        $users = \App\Models\User::all(); // Load all users to show in the dropdown
        
        return view('admin.ai-writer.news', compact('sources', 'categories', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'source_url'              => 'required|url',
            'category_id'             => 'nullable|exists:categories,id',
            'posts_per_run'           => 'required|integer|min:1|max:20',
            'fetch_interval_hours'    => 'required|integer|min:1|max:168',
            'daily_post_limit'        => 'nullable|integer|min:1|max:100',
            'use_smart_schedule'      => 'boolean',
            'duration_days'           => 'nullable|integer|min:1|max:30',
            'featured_image_source'   => 'required|string',
            'in_content_images_count' => 'required|integer|min:0|max:5',
            'in_content_image_source' => 'required|string',
            'is_active'               => 'boolean',
            'user_id'                 => 'nullable|exists:users,id',
        ]);

        $durationDays = $request->duration_days ? (int) $request->duration_days : null;
        $expiresAt    = $durationDays ? now()->addDays($durationDays) : null;

        AutoNewsSource::create([
            'name'                    => $request->name,
            'source_url'              => $request->source_url,
            'category_id'             => $request->category_id,
            'posts_per_run'           => $request->posts_per_run,
            'fetch_interval_hours'    => $request->fetch_interval_hours,
            'daily_post_limit'        => $request->daily_post_limit,
            'use_smart_schedule'      => $request->has('use_smart_schedule'),
            'duration_days'           => $durationDays,
            'expires_at'              => $expiresAt,
            'featured_image_source'   => $request->featured_image_source,
            'in_content_images_count' => $request->in_content_images_count,
            'in_content_image_source' => $request->in_content_image_source,
            'is_active'               => $request->has('is_active') ? true : false,
            'user_id'                 => $request->user_id,
        ]);

        $msg = 'Auto News Source added successfully.';
        if ($durationDays) {
            $msg .= " It will run for {$durationDays} day(s) and auto-stop on " . $expiresAt->format('M d, Y') . '.';
        }

        return back()->with('status', $msg);
    }

    public function update(Request $request, $id)
    {
        $source = AutoNewsSource::findOrFail($id);

        $request->validate([
            'name'                    => 'required|string|max:255',
            'source_url'              => 'required|url',
            'category_id'             => 'nullable|exists:categories,id',
            'posts_per_run'           => 'required|integer|min:1|max:20',
            'fetch_interval_hours'    => 'required|integer|min:1|max:168',
            'daily_post_limit'        => 'nullable|integer|min:1|max:100',
            'use_smart_schedule'      => 'boolean',
            'duration_days'           => 'nullable|integer|min:1|max:30',
            'featured_image_source'   => 'required|string',
            'in_content_images_count' => 'required|integer|min:0|max:5',
            'in_content_image_source' => 'required|string',
            'is_active'               => 'boolean',
            'user_id'                 => 'nullable|exists:users,id',
        ]);

        $durationDays = $request->duration_days ? (int) $request->duration_days : null;
        
        // If duration changed, we might want to update expires_at, but for now let's just update the value
        // Usually, users expect duration to be from the moment they update it if they are extending it
        $expiresAt = $durationDays ? now()->addDays($durationDays) : null;

        $source->update([
            'name'                    => $request->name,
            'source_url'              => $request->source_url,
            'category_id'             => $request->category_id,
            'posts_per_run'           => $request->posts_per_run,
            'fetch_interval_hours'    => $request->fetch_interval_hours,
            'daily_post_limit'        => $request->daily_post_limit,
            'use_smart_schedule'      => $request->has('use_smart_schedule'),
            'duration_days'           => $durationDays,
            'expires_at'              => $expiresAt,
            'featured_image_source'   => $request->featured_image_source,
            'in_content_images_count' => $request->in_content_images_count,
            'in_content_image_source' => $request->in_content_image_source,
            'is_active'               => $request->has('is_active') ? true : false,
            'user_id'                 => $request->user_id,
        ]);

        return back()->with('status', 'Auto News Source updated successfully.');
    }

    public function destroy($id)
    {
        $source = AutoNewsSource::findOrFail($id);
        $source->delete();

        return back()->with('status', 'Auto News Source deleted.');
    }

    public function fetchNow(Request $request, $id)
    {
        $source = AutoNewsSource::findOrFail($id);
        
        try {
            // Increase execution time since fetching and AI rewriting can take minutes
            set_time_limit(600); // 10 minutes max
            
            \Illuminate\Support\Facades\Artisan::call('news:fetch-auto', ['source_id' => $source->id]);
            $output = \Illuminate\Support\Facades\Artisan::output();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fetch completed for: ' . $source->name,
                    'output'  => trim($output),
                ]);
            }

            return back()->with('status', 'Fetch triggered successfully for source: ' . $source->name);
        } catch (\Exception $e) {
            \Log::error("Manual Fetch Error: " . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ]);
            }

            return back()->withErrors(['error' => 'Failed to run fetch. Check logs.']);
        }
    }

    public function logs(Request $request)
    {
        $query = \App\Models\Post::with(['category', 'user'])->whereNotNull('auto_news_source_id');

        if ($request->filled('source_id')) {
            $query->where('auto_news_source_id', $request->source_id);
        }

        $posts = $query->latest()->paginate(30)->withQueryString();
        $sources = AutoNewsSource::all();

        return view('admin.ai-writer.news-logs', compact('posts', 'sources'));
    }

    /**
     * Bulk import 15 US-based authors (Male & Female)
     */
    public function importAuthors()
    {
        $authors = [
            ['name' => 'James Smith', 'gender' => 'male'],
            ['name' => 'Michael Johnson', 'gender' => 'male'],
            ['name' => 'Robert Williams', 'gender' => 'male'],
            ['name' => 'David Brown', 'gender' => 'male'],
            ['name' => 'William Jones', 'gender' => 'male'],
            ['name' => 'Christopher Garcia', 'gender' => 'male'],
            ['name' => 'Matthew Miller', 'gender' => 'male'],
            ['name' => 'Mary Davis', 'gender' => 'female'],
            ['name' => 'Patricia Rodriguez', 'gender' => 'female'],
            ['name' => 'Jennifer Martinez', 'gender' => 'female'],
            ['name' => 'Linda Hernandez', 'gender' => 'female'],
            ['name' => 'Elizabeth Lopez', 'gender' => 'female'],
            ['name' => 'Barbara Gonzalez', 'gender' => 'female'],
            ['name' => 'Susan Wilson', 'gender' => 'female'],
            ['name' => 'Jessica Anderson', 'gender' => 'female'],
        ];

        $count = 0;
        foreach ($authors as $authorData) {
            $email = strtolower(str_replace(' ', '.', $authorData['name'])) . '@' . request()->getHost();
            
            // Check if user already exists
            if (!\App\Models\User::where('email', $email)->exists()) {
                \App\Models\User::create([
                    'name'     => $authorData['name'],
                    'email'    => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make(Str::random(12)),
                    'role'     => 'user', // Default role for authors
                ]);
                $count++;
            }
        }

        return back()->with('status', "Successfully imported {$count} US-based authors.");
    }

    /**
     * Import BBC and CoinTelegraph Predefined Sources
     */
    public function importPredefinedSources()
    {
        $sources = [
            ['name' => 'TechCrunch', 'url' => 'https://techcrunch.com/feed/', 'target' => 'Technology & IT'],
            ['name' => 'The Verge', 'url' => 'https://www.theverge.com/rss/index.xml', 'target' => 'Technology & IT'],
            ['name' => 'Gizmodo', 'url' => 'https://gizmodo.com/rss', 'target' => 'Technology & IT'],
            ['name' => 'Wired', 'url' => 'https://www.wired.com/feed/rss', 'target' => 'Technology & IT'],
            ['name' => 'Digital Trends', 'url' => 'https://www.digitaltrends.com/feed/', 'target' => 'Technology & IT'],
            ['name' => 'Mashable', 'url' => 'https://mashable.com/feeds/rss/all', 'target' => 'Entertainment & Pop Culture'],
            ['name' => 'Engadget', 'url' => 'https://www.engadget.com/rss.xml', 'target' => 'Technology & IT'],
            ['name' => 'Ars Technica', 'url' => 'https://feeds.arstechnica.com/arstechnica/index', 'target' => 'Technology & IT'],
            ['name' => 'The Next Web', 'url' => 'https://thenextweb.com/feed', 'target' => 'Technology & IT'],
            ['name' => 'CNET', 'url' => 'https://www.cnet.com/rss/news/', 'target' => 'Technology & IT'],
        ];

        $altNewsCategory = Category::where('name', 'Alt News')->first() ?? Category::create(['name' => 'Alt News', 'slug' => 'alt-news']);
        $defaultUser = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();

        $count = 0;
        foreach ($sources as $sourceData) {
            if (!AutoNewsSource::where('source_url', $sourceData['url'])->exists()) {
                $category = Category::where('name', $sourceData['target'])->first() ?? $altNewsCategory;
                AutoNewsSource::create([
                    'name'                    => $sourceData['name'],
                    'source_url'              => $sourceData['url'],
                    'category_id'             => $category->id,
                    'user_id'                 => $defaultUser->id,
                    'posts_per_run'           => 2,
                    'fetch_interval_hours'    => 24,
                    'featured_image_source'   => 'original',
                    'in_content_images_count' => 1,
                    'in_content_image_source' => 'stock',
                    'is_active'               => false,
                ]);
                $count++;
            }
        }

        return back()->with('status', "Successfully imported {$count} premium global sources.");
    }

    /**
     * Specialized import for BBC and CoinTelegraph with selection
     */
    public function importSpecializedSources(Request $request)
    {
        $request->validate([
            'sources'     => 'required|array',
            'category_id' => 'required|exists:categories,id',
            'user_id'     => 'required|exists:users,id',
        ]);

        $count = 0;
        foreach ($request->sources as $sourceName => $url) {
            if (!AutoNewsSource::where('source_url', $url)->exists()) {
                AutoNewsSource::create([
                    'name'                    => $sourceName,
                    'source_url'              => $url,
                    'category_id'             => $request->category_id,
                    'user_id'                 => $request->user_id,
                    'posts_per_run'           => 2,
                    'fetch_interval_hours'    => 24,
                    'featured_image_source'   => 'original',
                    'in_content_images_count' => 1,
                    'in_content_image_source' => 'stock',
                    'is_active'               => false,
                ]);
                $count++;
            }
        }

        return back()->with('status', "Successfully imported {$count} specialized sources.");
    }

    /**
     * Bulk delete selected news sources
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return back()->withErrors(['error' => 'No sources selected for deletion.']);
        }

        \App\Models\AutoNewsSource::whereIn('id', $ids)->delete();

        return back()->with('status', count($ids) . ' news sources deleted successfully.');
    }
}
