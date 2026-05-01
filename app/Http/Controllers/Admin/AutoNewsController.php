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
            'source_url'              => 'required|string', // Relaxed validation
            'category_id'             => 'nullable|exists:categories,id',
            'posts_per_run'           => 'required|integer|min:1',
            'fetch_interval_hours'    => 'required|integer|min:1',
            'daily_post_limit'        => 'nullable|integer|min:1',
            'duration_days'           => 'nullable|integer|min:1',
            'featured_image_source'   => 'required|string',
            'in_content_images_count' => 'required|integer|min:0',
            'in_content_image_source' => 'required|string',
            'user_id'                 => 'nullable|exists:users,id',
        ]);

        $data = [
            'name'                    => $request->name,
            'source_url'              => $request->source_url,
            'category_id'             => $request->category_id,
            'posts_per_run'           => $request->posts_per_run,
            'fetch_interval_hours'    => $request->fetch_interval_hours,
            'daily_post_limit'        => $request->daily_post_limit,
            'use_smart_schedule'      => $request->has('use_smart_schedule'),
            'featured_image_source'   => $request->featured_image_source,
            'in_content_images_count' => $request->in_content_images_count,
            'in_content_image_source' => $request->in_content_image_source,
            'is_active'               => $request->has('is_active'),
            'user_id'                 => $request->user_id,
        ];

        // Only update duration/expiry if provided
        if ($request->filled('duration_days')) {
            $data['duration_days'] = (int) $request->duration_days;
            $data['expires_at']    = now()->addDays($data['duration_days']);
        }

        $source->update($data);

        $categoryName = $source->category ? $source->category->name : 'No Category';
        return back()->with('status', "Source '{$source->name}' updated successfully! [Category: {$categoryName}]");
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
     * Import 10+ Premium Tech Giants (TechCrunch, Verge, etc.)
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

        return $this->processBulkImport($sources, "Premium Tech Bundle");
    }

    /**
     * Import BBC Global News Feeds
     */
    public function importBBCSources()
    {
        $sources = [
            ['name' => 'BBC World News', 'url' => 'http://feeds.bbci.co.uk/news/world/rss.xml', 'target' => 'Lifestyle & Culture'],
            ['name' => 'BBC Technology', 'url' => 'http://feeds.bbci.co.uk/news/technology/rss.xml', 'target' => 'Technology & IT'],
            ['name' => 'BBC Business', 'url' => 'http://feeds.bbci.co.uk/news/business/rss.xml', 'target' => 'Business & Finance'],
            ['name' => 'BBC Science', 'url' => 'http://feeds.bbci.co.uk/news/science_and_environment/rss.xml', 'target' => 'Health & Fitness'],
            ['name' => 'BBC Health', 'url' => 'http://feeds.bbci.co.uk/news/health/rss.xml', 'target' => 'Health & Fitness'],
            ['name' => 'BBC Entertainment', 'url' => 'http://feeds.bbci.co.uk/news/entertainment_and_arts/rss.xml', 'target' => 'Entertainment & Pop Culture'],
        ];

        return $this->processBulkImport($sources, "BBC News Bundle");
    }

    /**
     * Import Crypto/CoinTelegraph Feeds
     */
    public function importCryptoSources()
    {
        $sources = [
            ['name' => 'CoinTelegraph Bitcoin', 'url' => 'https://cointelegraph.com/rss/tag/bitcoin', 'target' => 'Alt News'],
            ['name' => 'CoinTelegraph Ethereum', 'url' => 'https://cointelegraph.com/rss/tag/ethereum', 'target' => 'Alt News'],
            ['name' => 'CoinTelegraph NFT', 'url' => 'https://cointelegraph.com/rss/tag/nft', 'target' => 'Alt News'],
            ['name' => 'CoinTelegraph DeFi', 'url' => 'https://cointelegraph.com/rss/tag/defi', 'target' => 'Alt News'],
            ['name' => 'CoinTelegraph Blockchain', 'url' => 'https://cointelegraph.com/rss/tag/blockchain', 'target' => 'Alt News'],
        ];

        return $this->processBulkImport($sources, "Crypto Bundle");
    }

    /**
     * Internal helper to process bulk imports with auto-mapping
     */
    private function processBulkImport($sources, $bundleName)
    {
        // Ensure Authors exist
        $this->importAuthors();
        $authors = \App\Models\User::where('role', 'user')->where('email', 'LIKE', '%@' . request()->getHost())->take(15)->get();
        $authorCount = $authors->count();

        // Ensure Fallback Category exists
        $altNews = Category::where('name', 'Alt News')->first() ?? Category::create(['name' => 'Alt News', 'slug' => 'alt-news']);

        $count = 0;
        foreach ($sources as $index => $sourceData) {
            if (!AutoNewsSource::where('source_url', $sourceData['url'])->exists()) {
                
                // Smart Category Mapping
                $category = Category::where('name', $sourceData['target'])->first() ?? $altNews;

                // Author Distribution
                $author = ($authorCount > 0) ? $authors[$index % $authorCount] : (\App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first());

                AutoNewsSource::create([
                    'name'                    => $sourceData['name'],
                    'source_url'              => $sourceData['url'],
                    'category_id'             => $category->id,
                    'user_id'                 => $author->id,
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

        return back()->with('status', "Successfully imported {$count} sources from the {$bundleName}. All sources have been auto-mapped to your categories.");
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
