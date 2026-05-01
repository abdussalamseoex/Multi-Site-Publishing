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
        // Predefined list with suggested category names
        $sources = [
            ['name' => 'BBC World News', 'source_url' => 'http://feeds.bbci.co.uk/news/world/rss.xml', 'suggested_category' => 'World News'],
            ['name' => 'BBC Technology', 'source_url' => 'http://feeds.bbci.co.uk/news/technology/rss.xml', 'suggested_category' => 'Technology'],
            ['name' => 'BBC Business', 'source_url' => 'http://feeds.bbci.co.uk/news/business/rss.xml', 'suggested_category' => 'Business'],
            ['name' => 'BBC Science', 'source_url' => 'http://feeds.bbci.co.uk/news/science_and_environment/rss.xml', 'suggested_category' => 'Science'],
            ['name' => 'BBC Health', 'source_url' => 'http://feeds.bbci.co.uk/news/health/rss.xml', 'suggested_category' => 'Health'],
            ['name' => 'CoinTelegraph Bitcoin', 'source_url' => 'https://cointelegraph.com/rss/tag/bitcoin', 'suggested_category' => 'Crypto'],
            ['name' => 'CoinTelegraph Ethereum', 'source_url' => 'https://cointelegraph.com/rss/tag/ethereum', 'suggested_category' => 'Crypto'],
            ['name' => 'CoinTelegraph Altcoins', 'source_url' => 'https://cointelegraph.com/rss/tag/altcoin', 'suggested_category' => 'Crypto'],
            ['name' => 'CoinTelegraph Blockchain', 'source_url' => 'https://cointelegraph.com/rss/tag/blockchain', 'suggested_category' => 'Blockchain'],
            ['name' => 'CoinTelegraph NFT', 'source_url' => 'https://cointelegraph.com/rss/tag/nft', 'suggested_category' => 'NFT'],
        ];

        // Find a fallback category if no match is found
        $fallbackCategory = Category::where('name', 'LIKE', '%News%')->orWhere('name', 'LIKE', '%General%')->first();
        if (!$fallbackCategory) {
            $fallbackCategory = Category::first();
        }

        // Find a default author (Admin or first user)
        $defaultUser = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();

        $count = 0;
        foreach ($sources as $sourceData) {
            if (!AutoNewsSource::where('source_url', $sourceData['source_url'])->exists()) {
                
                // Try to find a matching category by name
                $category = Category::where('name', 'LIKE', '%' . $sourceData['suggested_category'] . '%')->first() ?? $fallbackCategory;

                AutoNewsSource::create([
                    'name'                    => $sourceData['name'],
                    'source_url'              => $sourceData['source_url'],
                    'category_id'             => $category->id,
                    'user_id'                 => $defaultUser->id,
                    'posts_per_run'           => 2,
                    'fetch_interval_hours'    => 24,
                    'featured_image_source'   => 'stock',
                    'in_content_images_count' => 1,
                    'in_content_image_source' => 'stock',
                    'is_active'               => false,
                ]);
                $count++;
            }
        }

        return back()->with('status', "Successfully imported {$count} global news sources. They have been auto-mapped to your existing categories.");
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
