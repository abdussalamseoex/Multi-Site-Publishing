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
        // Ensure 15 US Authors exist first
        $this->importAuthors();
        $authors = \App\Models\User::where('role', 'user')->where('email', 'LIKE', '%@' . request()->getHost())->take(15)->get();
        $authorCount = $authors->count();

        // Predefined list mapped to User's Categories
        $sources = [
            // Tech & IT
            ['name' => 'TechCrunch', 'source_url' => 'https://techcrunch.com/feed/', 'target' => 'Technology & IT'],
            ['name' => 'The Verge', 'source_url' => 'https://www.theverge.com/rss/index.xml', 'target' => 'Technology & IT'],
            
            // Business & Finance
            ['name' => 'Forbes Business', 'source_url' => 'https://www.forbes.com/business/feed/', 'target' => 'Business & Finance'],
            ['name' => 'Wall Street Journal', 'source_url' => 'https://feeds.a.dj.com/rss/WSJcomUSBusiness.xml', 'target' => 'Business & Finance'],
            
            // Health & Fitness
            ['name' => 'Healthline News', 'source_url' => 'https://www.healthline.com/feed/news', 'target' => 'Health & Fitness'],
            
            // Lifestyle & Culture
            ['name' => 'BBC Lifestyle', 'source_url' => 'http://feeds.bbci.co.uk/news/world/rss.xml', 'target' => 'Lifestyle & Culture'],
            
            // Automotive
            ['name' => 'Car and Driver', 'source_url' => 'https://www.caranddriver.com/rss/all.xml/', 'target' => 'Automotive'],
            
            // Travel & Tourism
            ['name' => 'Lonely Planet', 'source_url' => 'https://www.lonelyplanet.com/news/feed', 'target' => 'Travel & Tourism'],
            
            // Real Estate
            ['name' => 'Realtor News', 'source_url' => 'https://www.realtor.com/news/feed/', 'target' => 'Real Estate'],
            
            // Crypto/Alt News (Fallback logic)
            ['name' => 'CoinTelegraph', 'source_url' => 'https://cointelegraph.com/rss', 'target' => 'Alt News'],
        ];

        // Ensure 'Alt News' exists as fallback
        $altNewsCategory = Category::where('name', 'Alt News')->first();
        if (!$altNewsCategory) {
            $altNewsCategory = Category::create([
                'name' => 'Alt News',
                'slug' => 'alt-news'
            ]);
        }

        $count = 0;
        foreach ($sources as $index => $sourceData) {
            if (!AutoNewsSource::where('source_url', $sourceData['source_url'])->exists()) {
                
                // Try to find matching category from user's list
                $category = Category::where('name', $sourceData['target'])->first() ?? $altNewsCategory;

                // Distribute authors (cycle through the 15 authors)
                $assignedAuthor = ($authorCount > 0) ? $authors[$index % $authorCount] : (\App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first());

                AutoNewsSource::create([
                    'name'                    => $sourceData['name'],
                    'source_url'              => $sourceData['source_url'],
                    'category_id'             => $category->id,
                    'user_id'                 => $assignedAuthor->id,
                    'posts_per_run'           => 2,
                    'fetch_interval_hours'    => 24,
                    'featured_image_source'   => 'original', // Use original as requested
                    'in_content_images_count' => 1,
                    'in_content_image_source' => 'stock',
                    'is_active'               => false,
                ]);
                $count++;
            }
        }

        return back()->with('status', "Successfully imported {$count} global news sources. Mapped to your categories and distributed across 15 authors.");
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
