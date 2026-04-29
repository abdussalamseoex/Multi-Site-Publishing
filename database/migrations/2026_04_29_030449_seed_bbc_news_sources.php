<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\AutoNewsSource;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sources = [
            ['name' => 'BBC Business', 'url' => 'http://feeds.bbci.co.uk/news/business/rss.xml', 'category' => 'Business'],
            ['name' => 'BBC Technology', 'url' => 'http://feeds.bbci.co.uk/news/technology/rss.xml', 'category' => 'Technology'],
            ['name' => 'BBC Health', 'url' => 'http://feeds.bbci.co.uk/news/health/rss.xml', 'category' => 'Health'],
            ['name' => 'BBC Science', 'url' => 'http://feeds.bbci.co.uk/news/science_and_environment/rss.xml', 'category' => 'Science'],
            ['name' => 'BBC Entertainment', 'url' => 'http://feeds.bbci.co.uk/news/entertainment_and_arts/rss.xml', 'category' => 'Entertainment'],
            ['name' => 'BBC Politics', 'url' => 'http://feeds.bbci.co.uk/news/politics/rss.xml', 'category' => 'Politics'],
            ['name' => 'BBC Sports', 'url' => 'http://feeds.bbci.co.uk/sport/rss.xml', 'category' => 'Sports'],
        ];

        foreach ($sources as $source) {
            // Check if source already exists
            if (!AutoNewsSource::where('source_url', $source['url'])->exists()) {
                
                // Get or create category
                $category = Category::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($source['category'])],
                    ['name' => $source['category'], 'is_active' => true]
                );

                AutoNewsSource::create([
                    'name' => $source['name'],
                    'source_url' => $source['url'],
                    'category_id' => $category->id,
                    'posts_per_run' => 5,
                    'fetch_interval_hours' => 2, // Check every 2 hours
                    'featured_image_source' => 'pexels',
                    'in_content_images_count' => 1,
                    'in_content_image_source' => 'pexels',
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for seeding
    }
};
